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
	 * @var string
	 */
	const CONF_MAX_FAILURES = 'max_failures';

	/**
	 * @var string
	 */
	const CONF_DISABLED = 'disabled';

	/**
	 * @var string
	 */
	const RESTRICTION_TIME = 'restriction_time';

	/**
	 * @var string
	 */
	const CONF_SECONDS_TILL_RESET = 'seconds_till_reset';

	/**
	 * @var Tx_FeloginBruteforceProtection_Domain_Model_Entry
	 */
	private $currentEntry = NULL;

	/**
	 * @var Tx_FeloginBruteforceProtection_Domain_Repository_Entry|null
	 */
	private $entryRepository = NULL;

	/**
	 * @var null|Tx_Extbase_Object_ObjectManager
	 */
	private $objectManager = NULL;

	/**
	 * @var null|Tx_Extbase_Persistence_Manager
	 */
	private $persistenceManager = NULL;

	/**
	 * @var t3lib_userauth
	 */
	private $t3libUserAuth = NULL;

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
	 * @return void
	 */
	public function cleanUpEntries()
	{
		$this->getEntryRepository()->removeEntriesOlderThan($this->getConfiguration(self::CONF_SECONDS_TILL_RESET));
		$this->getPersistenceManager()->persistAll();
	}

	/**
	 * @return bool|mixed
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
			$entry->getFailures() >= $this->getConfiguration(self::CONF_MAX_FAILURES) &&
			(time() - $entry->getTstamp()) <= $this->getConfiguration(self::RESTRICTION_TIME)
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
		if ('1' === $this->getConfiguration(self::CONF_DISABLED)) {
			return FALSE;
		}
		return TRUE;
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
		$this->cleanUpEntries();
		return TRUE;
	}

	/**
	 * @param $key
	 * @return mixed
	 * @throws InvalidArgumentException
	 */
	private function getConfiguration($key)
	{
		$conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['felogin_bruteforce_protection']);
		if (array_key_exists($key, $conf)) {
			return $conf[$key];
		}
		throw new InvalidArgumentException('Configuration key "' . $key . '" does not exist.');
	}

	/**
	 * @return Tx_FeloginBruteforceProtection_Domain_Model_Entry
	 */
	private function getEntryForCurrentClient()
	{
		if (NULL === $this->currentEntry) {
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
		if (NULL === $this->entryRepository) {
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
		if (NULL === $this->objectManager) {
			$this->objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
		}
		return $this->objectManager;
	}

	/**
	 * @return Tx_Extbase_Persistence_Manager
	 */
	protected function getPersistenceManager()
	{
		if (NULL === $this->persistenceManager) {
			$this->persistenceManager = $this->getObjectManager()->get('Tx_Extbase_Persistence_Manager');
		}
		return $this->persistenceManager;
	}

	/**
	 * @return string
	 */
	private function getRestrictionMessage()
	{
		return Tx_Extbase_Utility_Localization::translate('restriction_message', 'felogin_bruteforce_protection', array(
			(int)($this->getConfiguration(self::RESTRICTION_TIME) / 60)
		));
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/felogin_bruteforce_protection/Classes/Service/AuthUser.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/felogin_bruteforce_protection/Classes/Service/AuthUser.php']);
}