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

/**
 * @package felogin_bruteforce_protection
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_FeloginBruteforceProtection_Hooks_UserAuth_PostUserLookUp
{
	/**
	 * @var Tx_FeloginBruteforceProtection_Service_AuthUser
	 */
	private $service;

	/**
	 * @param $params
	 * @return void
	 */
	public function handlePostUserLookUp(&$params)
	{
		$userAuthObject = $params['pObj'];
		if ($userAuthObject->loginType === 'FE') {
			if ($userAuthObject->loginFailure == 0) {
				$this->getService()->resetEntryForCurrentClient();
			}
			if (TRUE === $this->getService()->isProtectionEnabled()) {
				if ($userAuthObject->loginFailure == 1) {
					$this->getService()->rememberFailedLogin();
				}
				$this->getService()->validate($userAuthObject);
			}
		}
	}

	/**
	 * @return Tx_FeloginBruteforceProtection_Service_AuthUser
	 */
	private function getService()
	{
		if (FALSE === ($this->service instanceof Tx_FeloginBruteforceProtection_Service_AuthUser)) {
			/** @var $objectManager Tx_Extbase_Object_ObjectManager */
			$objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
			/** @var $service Tx_FeloginBruteforceProtection_Service_AuthUser */
			$service = $objectManager->get('Tx_FeloginBruteforceProtection_Service_AuthUser');
			$this->service = $service;
		}
		return $this->service;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/felogin_bruteforce_protection/Classes/Hooks/UserAuth/PostUserLookUp.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/felogin_bruteforce_protection/Classes/Hooks/UserAuth/PostUserLookUp.php']);
}