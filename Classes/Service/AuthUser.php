<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Kevin Schu <kevin.schu@aoemedia.de>, AOE media GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

require_once t3lib_extMgm::extPath('sv', 'class.tx_sv_auth.php');

/**
 * @package felogin_bruteforce_protection
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_FeloginBruteforceProtection_Service_AuthUser extends tx_sv_auth
{
	/**
	 * @var Tx_FeloginBruteforceProtection_System_Configuration
	 */
	private $configuration;

	/**
	 * @var Tx_FeloginBruteforceProtection_Domain_Model_Entry
	 */
	private $currentEntry ;

	/**
	 * @var Tx_FeloginBruteforceProtection_Domain_Repository_Entry
	 */
	private $entryRepository;

	/**
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	private $objectManager;

	/**
	 * @var Tx_Extbase_Persistence_Manager
	 */
	private $persistenceManager;

	/**
	 * @var t3lib_userauth
	 */
	private $t3libUserAuth;

	/**
	 * Ensure TSFE is loaded
	 */
	public function __construct()
	{
		if (FALSE === ($GLOBALS['TSFE'] instanceof tslib_fe)) {
			$GLOBALS['TSFE'] = t3lib_div::makeInstance('tslib_fe', $GLOBALS['TYPO3_CONF_VARS'], 2, 0);
		}
		if (FALSE === ($GLOBALS['TSFE']->sys_page instanceof t3lib_pageSelect)) {
			$GLOBALS['TSFE']->sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
		}
	}

	/**
	 * Ensure chain breaking if client is already banned!
	 *
	 * @param   mixed       $userData Data of user.
	 * @return  integer     Chain result (<0: break chain; 100: use next chain service; 200: success)
	 */
	public function authUser($userData)
	{
		if (TRUE === $this->isProtectionEnabled() && TRUE === $this->isClientTemporaryRestricted()) {
			return -1;
		}
		return 100;
	}

	/**
	 * Ensure chain breaking if client is already banned!
	 * Simulate an invalid user and stop the chain by setting the "fetchAllUsers" configuration to "FALSE";
	 *
	 * @return bool|array
	 */
	public function getUser()
	{
		if (TRUE === $this->isProtectionEnabled() && TRUE === $this->isClientTemporaryRestricted()) {
			$GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup'][$this->t3libUserAuth->loginType . '_fetchAllUsers'] = FALSE;
			return array('uid' => 0);
		}
		return parent::getUser();
	}

	/**
	 * @param $subType
	 * @param $loginData
	 * @param $authInfo
	 * @param $t3libUserAuth
	 */
	public function initAuth(&$subType, &$loginData, &$authInfo, &$t3libUserAuth) {
		$this->t3libUserAuth = $t3libUserAuth;
	}

	/**
	 * @return bool
	 */
	public function isClientTemporaryRestricted()
	{
		$entry = $this->getEntryRepository()->findOneByIdentifier($this->getIdentifier());
		if (
			$entry instanceof Tx_FeloginBruteforceProtection_Domain_Model_Entry &&
			$entry->getFailures() >= $this->getConfiguration()->get(Tx_FeloginBruteforceProtection_System_Configuration::CONF_MAX_FAILURES)
		) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * @return bool
	 */
	public function isProtectionEnabled()
	{
		if ('1' === $this->getConfiguration()->get(Tx_FeloginBruteforceProtection_System_Configuration::CONF_DISABLED)) {
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * @return void
	 */
	public function resetEntryForCurrentClient()
	{
		$this->getEntryRepository()->remove($this->getEntryForCurrentClient());
		$this->getPersistenceManager()->persistAll();
	}

	/**
	 * @return void
	 */
	public function rememberFailedLogin()
	{
		if (FALSE === $this->isClientTemporaryRestricted()) {
			$this->getEntryForCurrentClient()->increaseFailures();
			$this->getEntryForCurrentClient()->setTstamp(time());
			$this->getEntryRepository()->add($this->getEntryForCurrentClient()); // Need to use "add", "update" does not work...
			$this->getPersistenceManager()->persistAll();
		}
	}

	/**
	 * @param $userAuthObject
	 * @return bool
	 */
	public function validate(&$userAuthObject)
	{
		$GLOBALS['felogin_bruteforce_protection']['restricted'] = FALSE;
		if ($this->isClientTemporaryRestricted()) {
			$userAuthObject->loginFailure = 1;
			$GLOBALS['felogin_bruteforce_protection']['restricted'] = TRUE;
			$GLOBALS['felogin_bruteforce_protection']['restriction_message'] = $this->getRestrictionMessage();
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * @return Tx_FeloginBruteforceProtection_System_Configuration
	 */
	private function getConfiguration()
	{
		if(FALSE === ($this->configuration instanceof Tx_FeloginBruteforceProtection_System_Configuration)) {
			$this->configuration = $this->getObjectManager()->create('Tx_FeloginBruteforceProtection_System_Configuration');
		}
		return $this->configuration;
	}

	/**
	 * @return Tx_FeloginBruteforceProtection_Domain_Model_Entry
	 */
	private function getEntryForCurrentClient()
	{
		if (FALSE === ($this->currentEntry instanceof Tx_FeloginBruteforceProtection_Domain_Model_Entry)) {
			$entry = $this->getEntryRepository()->findOneByIdentifier($this->getIdentifier());
			if (FALSE === ($entry instanceof Tx_FeloginBruteforceProtection_Domain_Model_Entry)) {
				$time = time();
				/** @var $entry Tx_FeloginBruteforceProtection_Domain_Model_Entry */
				$entry = $this->getObjectManager()->get('Tx_FeloginBruteforceProtection_Domain_Model_Entry');
				$entry->setFailures(0);
				$entry->setCrdate($time);
				$entry->setTstamp($time);
				$entry->setIdentifier($this->getIdentifier());
				$this->getEntryRepository()->add($entry);
				$this->getPersistenceManager()->persistAll();
			}
			$this->currentEntry = $entry;
		}

		return $this->currentEntry;
	}

	/**
	 * @return Tx_FeloginBruteforceProtection_Domain_Repository_Entry
	 */
	private function getEntryRepository()
	{
		if (FALSE === ($this->entryRepository instanceof Tx_FeloginBruteforceProtection_Domain_Repository_Entry)) {
			$this->entryRepository = $this->getObjectManager()->get('Tx_FeloginBruteforceProtection_Domain_Repository_Entry');
		}
		return $this->entryRepository;
	}

	/**
	 * @return string
	 */
	private function getIdentifier()
	{
		return md5($_SERVER['REMOTE_ADDR'] . $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']);
	}

	/**
	 * @return Tx_Extbase_Object_ObjectManager
	 */
	private function getObjectManager()
	{
		if (FALSE === ($this->objectManager instanceof Tx_Extbase_Object_ObjectManager)) {
			$this->objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
		}
		return $this->objectManager;
	}

	/**
	 * @return Tx_Extbase_Persistence_Manager
	 */
	protected function getPersistenceManager()
	{
		if (FALSE === ($this->persistenceManager instanceof Tx_Extbase_Persistence_Manager)) {
			$this->persistenceManager = $this->getObjectManager()->get('Tx_Extbase_Persistence_Manager');
		}
		return $this->persistenceManager;
	}

	/**
	 * @return string
	 */
	private function getRestrictionMessage()
	{
		$time = (int)($this->getConfiguration()->get(Tx_FeloginBruteforceProtection_System_Configuration::CONF_RESTRICTION_TIME) / 60);
		return Tx_Extbase_Utility_Localization::translate('restriction_message', 'felogin_bruteforce_protection', array(
			$time,$time
		));
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/felogin_bruteforce_protection/Classes/Service/AuthUser.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/felogin_bruteforce_protection/Classes/Service/AuthUser.php']);
}