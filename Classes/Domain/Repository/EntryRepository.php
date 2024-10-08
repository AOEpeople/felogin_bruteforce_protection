<?php

namespace Aoe\FeloginBruteforceProtection\Domain\Repository;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019 AOE GmbH <dev@aoe.com>
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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class EntryRepository extends Repository
{
    public function initializeObject(): void
    {
        /** @var Typo3QuerySettings $defaultQuerySettings */
        $defaultQuerySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        // don't add the pid constraint
        $defaultQuerySettings->setRespectStoragePage(false);
        // don't add fields from enable columns constraint
        $defaultQuerySettings->setIgnoreEnableFields(true);
        // don't add sys_language_uid constraint
        $defaultQuerySettings->setRespectSysLanguage(false);
        $this->setDefaultQuerySettings($defaultQuerySettings);
    }

    /**
     * @return array|QueryResultInterface
     */
    public function findEntriesToCleanUp($secondsTillReset, $maxFailures, $restrictionTime, $identifier = null)
    {
        $time = time();
        $age = (int) $time - $secondsTillReset;
        $restrictionTime = (int) $time - $restrictionTime;
        $query = $this->createQuery();
        $query->getQuerySettings()
            ->setRespectStoragePage(false);
        $query->getQuerySettings()
            ->setIgnoreEnableFields(true);
        $query->getQuerySettings()
            ->setRespectSysLanguage(false);
        $constraintsRestrictedEntries = [
            $query->lessThan('tstamp', $restrictionTime),
            $query->greaterThanOrEqual('failures', $maxFailures),
        ];
        $constraintsResettableEntries = [
            $query->lessThan('crdate', $age),
            $query->lessThan('failures', $maxFailures),
        ];
        if ($identifier !== null) {
            $constraintsRestrictedEntries[] = $query->equals('identifier', $identifier);
            $constraintsResettableEntries[] = $query->equals('identifier', $identifier);
        }

        $query->matching(
            $query->logicalOr(
                $query->logicalAnd(...$constraintsRestrictedEntries),
                $query->logicalAnd(...$constraintsResettableEntries)
            )
        );

        return $query->execute();
    }
}
