<?php
namespace Aoe\FeloginBruteforceProtection\Domain\Repository;

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
 */
class Entry extends \TYPO3\CMS\Extbase\Persistence\Repository
{

    /**
     * initialize
     */
    public function __construct()
    {
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        parent::__construct($objectManager);
    }

    /**
     * @return void
     */
    public function initializeObject()
    {
        /** @var $defaultQuerySettings \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings */
        $defaultQuerySettings = $this->objectManager->get('\TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings');
        // don't add the pid constraint
        $defaultQuerySettings->setRespectStoragePage(false);
        // don't add fields from enablecolumns constraint
        $defaultQuerySettings->setIgnoreEnableFields(true)->setIncludeDeleted(true);
        // don't add sys_language_uid constraint
        $defaultQuerySettings->setRespectSysLanguage(false);
        $this->setDefaultQuerySettings($defaultQuerySettings);
    }

    /**
     * @param int $uid
     * @return object
     */
    public function findByUid($uid)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectSysLanguage(false);
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->getQuerySettings()->setIgnoreEnableFields(true)->setIncludeDeleted(true);
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
    public function cleanUp($secondsTillReset, $maxFailures, $restrictionTime, $identifier = null)
    {
        $time = time();
        $age = (int)$time - $secondsTillReset;
        $restrictionTime = (int)$time - $restrictionTime;
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectSysLanguage(false);
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->getQuerySettings()->setIgnoreEnableFields(true)->setIncludeDeleted(true);
        $constraintsRestrictedEntries = array(
            $query->lessThan('tstamp', $restrictionTime),
            $query->greaterThanOrEqual('failures', $maxFailures),
        );
        $constraintsResettableEntries = array(
            $query->lessThan('crdate', $age),
            $query->lessThan('failures', $maxFailures),
        );
        if (null !== $identifier) {
            $constraintsRestrictedEntries[] = $query->equals('identifier', $identifier);
            $constraintsResettableEntries[] = $query->equals('identifier', $identifier);
        }
        $query->matching(
            $query->logicalOr(
                $query->logicalAnd($constraintsRestrictedEntries),
                $query->logicalAnd($constraintsResettableEntries)
            )
        );
        foreach ($query->execute() as $object) {
            $this->remove($object);
            $this->add($object);
        }
    }
}
