<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Timo Fuchs <timo.fuchs@aoemedia.de>, AOE media GmbH
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
 * @subpackage Task
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_FeloginBruteforceProtection_Task_CleanUpEntries extends tx_scheduler_Task
{
	/**
	 * @return boolean
	 */
	public function execute()
	{
		$this->getEntryRepository()->cleanUp(
			$this->getConfiguration()->get(Tx_FeloginBruteforceProtection_System_Configuration::CONF_SECONDS_TILL_RESET),
			$this->getConfiguration()->get(Tx_FeloginBruteforceProtection_System_Configuration::CONF_MAX_FAILURES),
			$this->getConfiguration()->get(Tx_FeloginBruteforceProtection_System_Configuration::CONF_RESTRICTION_TIME)
		);
		$this->getPersistenceManager()->persistAll();
		return TRUE;
	}

	/**
	 * @return Tx_FeloginBruteforceProtection_Domain_Repository_Entry
	 */
	private function getEntryRepository()
	{
		return $this->getObjectManager()->get('Tx_FeloginBruteforceProtection_Domain_Repository_Entry');
	}

	/**
	 * @return Tx_Extbase_Persistence_Manager
	 */
	private function getPersistenceManager()
	{
		return $this->getObjectManager()->get('Tx_Extbase_Persistence_Manager');
	}

	/**
	 * @return Tx_FeloginBruteforceProtection_System_Configuration
	 */
	private function getConfiguration()
	{
		return $this->getObjectManager()->get('Tx_FeloginBruteforceProtection_System_Configuration');
	}

	/**
	 * @return Tx_Extbase_Object_ObjectManager
	 */
	private function getObjectManager()
	{
		return t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
	}
}