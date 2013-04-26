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
 * @package Tx_FeloginBruteforceProtection
 * @subpackage Service
 * @author Kevin Schu <kevin.schu@aoemedia.de>
 * @author Timo Fuchs <timo.fuchs@aoemedia.de>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_FeloginBruteforceProtection_Service_AuthUser extends tx_sv_auth {

	/**
	 * @var Tx_FeloginBruteforceProtection_System_Configuration
	 */
	private $configuration;

	/**
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	private $objectManager;

	/**
	 * @var Tx_FeloginBruteforceProtection_Domain_Service_Restriction
	 */
	private $restrictionService;

	/**
	 * @var t3lib_userauth
	 */
	private $t3libUserAuth;

	/**
	 * Constructor.
	 */
	public function __construct() {
		if (FALSE === ($GLOBALS['TSFE'] instanceof tslib_fe)) {
			$GLOBALS['TSFE'] = t3lib_div::makeInstance('tslib_fe', $GLOBALS['TYPO3_CONF_VARS'], 2, 0);
		}
		if (FALSE === ($GLOBALS['TSFE']->sys_page instanceof t3lib_pageSelect)) {
			$GLOBALS['TSFE']->sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
		}
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
	 * Ensure chain breaking if client is already banned!
	 * Simulate an invalid user and stop the chain by setting the "fetchAllUsers" configuration to "FALSE";
	 *
	 * @return bool|array
	 */
	public function getUser() {
		if ($this->isProtectionEnabled() && $this->getRestrictionService()->isClientRestricted()) {
			//$this->getRestrictionService()->setForceRestriction(TRUE);
			$GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup'][$this->t3libUserAuth->loginType . '_fetchAllUsers'] = FALSE;
			return array('uid' => 0);
		}
		return parent::getUser();
	}

	/**
	 * Ensure chain breaking if client is already banned!
	 *
	 * @param   mixed $userData Data of user.
	 * @return  integer     Chain result (<0: break chain; 100: use next chain service; 200: success)
	 */
	public function authUser($userData) {
		if ($this->isProtectionEnabled() && $this->getRestrictionService()->isClientRestricted()) {
			return -1;
		}
		return 100;
	}

	/**
	 * @return bool
	 */
	public function isProtectionEnabled() {
		return $this->getConfiguration()->isEnabled();
	}

	/**
	 * @return Tx_FeloginBruteforceProtection_System_Configuration
	 */
	private function getConfiguration() {
		if (FALSE === ($this->configuration instanceof Tx_FeloginBruteforceProtection_System_Configuration)) {
			$this->configuration = $this->getObjectManager()->create('Tx_FeloginBruteforceProtection_System_Configuration');
		}
		return $this->configuration;
	}

	/**
	 * @return Tx_Extbase_Object_ObjectManager
	 */
	private function getObjectManager() {
		if (FALSE === ($this->objectManager instanceof Tx_Extbase_Object_ObjectManager)) {
			$this->objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
		}
		return $this->objectManager;
	}

	/**
	 * @return Tx_FeloginBruteforceProtection_Domain_Service_Restriction
	 */
	private function getRestrictionService() {
		if (FALSE === isset($this->restrictionService)) {
			$this->restrictionService = $this->getObjectManager()->get('Tx_FeloginBruteforceProtection_Domain_Service_Restriction');
		}
		return $this->restrictionService;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/felogin_bruteforce_protection/Classes/Service/AuthUser.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/felogin_bruteforce_protection/Classes/Service/AuthUser.php']);
}