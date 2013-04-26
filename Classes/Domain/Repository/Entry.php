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
 * @package Tx_FeloginBruteforceProtection
 * @subpackage Domain_Repository
 * @author Kevin Schu <kevin.schu@aoemedia.de>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_FeloginBruteforceProtection_Domain_Repository_Entry extends Tx_Extbase_Persistence_Repository {

	/**
	 * @return void
	 */
	public function initializeObject() {
		/** @var $defaultQuerySettings Tx_Extbase_Persistence_Typo3QuerySettings */
		$defaultQuerySettings = $this->objectManager->get('Tx_Extbase_Persistence_Typo3QuerySettings');
		// don't add the pid constraint
		$defaultQuerySettings->setRespectStoragePage(FALSE);
		// don't add fields from enablecolumns constraint
		$defaultQuerySettings->setRespectEnableFields(FALSE);
		// don't add sys_language_uid constraint
		$defaultQuerySettings->setRespectSysLanguage(FALSE);
		$this->setDefaultQuerySettings($defaultQuerySettings);
	}

	/**
	 * @param int $uid
	 * @return object
	 */
	public function findByUid($uid) {
		$query = $this->createQuery();
		$query->getQuerySettings()->setRespectSysLanguage(FALSE);
		$query->getQuerySettings()->setRespectStoragePage(FALSE);
		$query->getQuerySettings()->setRespectEnableFields(FALSE);
		$query->matching($query->equals('uid', $uid));
		return $query->execute()->getFirst();
	}

	/**
	 * @param $secondsTillReset
	 * @param $maxFailures
	 * @param $restrictionTime
	 * @param $identifier
	 * @return void
	 */
	public function cleanUp($secondsTillReset, $maxFailures, $restrictionTime, $identifier = NULL) {
		$time = time();
		$age = (int)$time - $secondsTillReset;
		$restrictionTime = (int)$time - $restrictionTime;
		$query = $this->createQuery();
		$query->getQuerySettings()->setRespectSysLanguage(FALSE);
		$query->getQuerySettings()->setRespectStoragePage(FALSE);
		$query->getQuerySettings()->setRespectEnableFields(FALSE);
		$constraintsRestrictedEntries = array(
			$query->lessThan('tstamp', $restrictionTime),
			$query->greaterThanOrEqual('failures', $maxFailures),
		);
		$constraintsResettableEntries = array(
			$query->lessThan('crdate', $age),
			$query->lessThan('failures', $maxFailures),
		);
		if(NULL !== $identifier) {
			$constraintsRestrictedEntries[] = $query->equals('identifier', $identifier);
			$constraintsResettableEntries[] = $query->equals('identifier', $identifier);
		}
		$query->matching($query->logicalOr(
			$query->logicalAnd($constraintsRestrictedEntries),
			$query->logicalAnd($constraintsResettableEntries)
		));
		foreach ($query->execute() as $object) {
			$this->removedObjects->attach($object);
			$this->addedObjects->detach($object);
		}
	}
}