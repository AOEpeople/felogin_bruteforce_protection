<?php

/***************************************************************
 * Copyright notice
 *
 * (c) 2013 Kevin Schu <kevin.schu@aoemedia.de>, AOE media GmbH
 *
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * @package Tx_FeloginBruteforceProtection
 * @subpackage Hooks_UserAuth
 * @author Kevin Schu <kevin.schu@aoemedia.de>
 * @author Timo Fuchs <timo.fuchs@aoemedia.de>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_FeloginBruteforceProtection_Hooks_UserAuth_PostUserLookUp {
	/**
	 * @var Tx_FeloginBruteforceProtection_Domain_Service_Restriction
	 */
	private $restrictionService;
	
	/**
	 * @var Tx_FeloginBruteforceProtection_System_Configuration
	 */
	private $configuration;
	
	/**
	 * Constructor.
	 * @todo explain why this have be done
	 */
	public function __construct() {
		if (FALSE === ($GLOBALS ['TSFE'] instanceof tslib_fe)) {
			$GLOBALS ['TSFE'] = t3lib_div::makeInstance ( 'tslib_fe', $GLOBALS ['TYPO3_CONF_VARS'], 2, 0 );
		}
		if (FALSE === ($GLOBALS ['TSFE']->sys_page instanceof t3lib_pageSelect)) {
			$GLOBALS ['TSFE']->sys_page = t3lib_div::makeInstance ( 't3lib_pageSelect' );
		}
	}
	
	/**
	 * @param array $params
	 * @return void
	 */
	public function handlePostUserLookUp(&$params) {
		if(FALSE === $this->getConfiguration ()->isEnabled ()) {
			return;
		}

		$userAuthObject = $params ['pObj'];
		if ($this->hasFeUserLoggedIn($userAuthObject)) {
			$this->getRestrictionService ()->removeEntry ();
		} elseif($this->hasFeUserLogInFailed($userAuthObject)) {
			$this->getRestrictionService ()->incrementFailureCount ();
			$this->updateGlobals ( $userAuthObject );
		}
	}
	
	/**
	 * @param $userAuthObject
	 * @return boolean
	 */
	private function updateGlobals(&$userAuthObject) {
		$GLOBALS ['felogin_bruteforce_protection'] ['restricted'] = FALSE;
		if ($this->getRestrictionService ()->isClientRestricted ()) {
			$userAuthObject->loginFailure = 1;
			$GLOBALS ['felogin_bruteforce_protection'] ['restricted'] = TRUE;
			$GLOBALS ['felogin_bruteforce_protection'] ['restriction_message'] = $this->getRestrictionMessage ();
			return FALSE;
		}
		return TRUE;
	}
	
	/**
	 * @return string
	 */
	private function getRestrictionMessage() {
		$time = ( integer ) ($this->getConfiguration ()->getRestrictionTime () / 60);
		return Tx_Extbase_Utility_Localization::translate ( 'restriction_message', 'felogin_bruteforce_protection', array ($time, $time ) );
	}
	
	/**
	 * @return Tx_FeloginBruteforceProtection_Domain_Service_Restriction
	 */
	private function getRestrictionService() {
		if (FALSE === isset ( $this->restrictionService )) {
			$this->restrictionService = t3lib_div::makeInstance ( 'Tx_Extbase_Object_ObjectManager' )->get ( 'Tx_FeloginBruteforceProtection_Domain_Service_Restriction' );
		}
		return $this->restrictionService;
	}
	
	/**
	 * @return Tx_FeloginBruteforceProtection_System_Configuration
	 */
	private function getConfiguration() {
		if (FALSE === isset ( $this->configuration )) {
			$this->configuration = t3lib_div::makeInstance ( 'Tx_Extbase_Object_ObjectManager' )->get ( 'Tx_FeloginBruteforceProtection_System_Configuration' );
		}
		return $this->configuration;
	}
	/**
	 * check, if FE-user has logged in in this request
	 * 
	 * @param t3lib_userAuth $userAuthObject
	 */
	private function hasFeUserLoggedIn(t3lib_userAuth $userAuthObject) {
		if ($userAuthObject->loginType === 'FE' && $userAuthObject->loginFailure === FALSE && is_array($userAuthObject->user) && $userAuthObject->loginSessionStarted === TRUE) {
			return TRUE;
		}
		return FALSE;
	}
	/**
	 * check, if login-action of FE-user failed
	 * 
	 * @param t3lib_userAuth $userAuthObject
	 */
	private function hasFeUserLogInFailed(t3lib_userAuth $userAuthObject) {
		if ($userAuthObject->loginType === 'FE' && $userAuthObject->loginFailure === TRUE && $userAuthObject->user === FALSE) {
			if (isset ( $userAuthObject->svConfig ['loginNotPossible'] ) && $userAuthObject->svConfig ['loginNotPossible'] === TRUE) {
				return FALSE;
			}
			return TRUE;
		}
		return FALSE;
	}
}

if (defined ( 'TYPO3_MODE' ) && $TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/felogin_bruteforce_protection/Classes/Hooks/UserAuth/PostUserLookUp.php']) {
	include_once ($TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/felogin_bruteforce_protection/Classes/Hooks/UserAuth/PostUserLookUp.php']);
}