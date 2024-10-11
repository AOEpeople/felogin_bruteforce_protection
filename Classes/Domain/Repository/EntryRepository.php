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

use Aoe\FeloginBruteforceProtection\Domain\Model\Entry;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class EntryRepository extends Repository
{
    private const ENTRY_DB_TABLE = 'tx_feloginbruteforceprotection_domain_model_entry';

    /**
     * We don't use the extbase-logic to do the DB-query - because we must do the DB-query BEFORE the FE-user is initialised - and
     * this would lead to the TYPO3-deprecation 'Using extbase in a context without TypoScript. Will stop working with TYPO3 v13.'
     */
    public function findOneEntryByIdentifier(string $identifier): ?Entry
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(self::ENTRY_DB_TABLE);
        $records = $queryBuilder->select('*')
            ->from(self::ENTRY_DB_TABLE)
            ->where(
                $queryBuilder->expr()
                    ->eq('identifier', $queryBuilder->createNamedParameter($identifier, \PDO::PARAM_STR)),
            )
            ->executeQuery()
            ->fetchAllAssociative();

        if (count($records) === 1) {
            $entry = new Entry();
            $entry->_setProperty('uid', $records[0]['uid']);
            $entry->_setProperty('pid', $records[0]['pid']);
            $entry->_setProperty('tstamp', $records[0]['tstamp']);
            $entry->_setProperty('crdate', $records[0]['crdate']);
            $entry->_setProperty('identifier', $records[0]['identifier']);
            $entry->_setProperty('failures', $records[0]['failures']);
            return $entry;
        }

        return null;
    }

    /**
     * We don't use the extbase-logic to do the DB-query - because we must do the DB-query BEFORE the FE-user is initialised - and
     * this would lead to the TYPO3-deprecation 'Using extbase in a context without TypoScript. Will stop working with TYPO3 v13.'
     */
    public function createEntry(Entry $entry): void
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(self::ENTRY_DB_TABLE);
        $queryBuilder->insert(self::ENTRY_DB_TABLE)
            ->values([
                'pid' => 0,
                'tstamp' => $entry->getTstamp(),
                'crdate' => $entry->getCrdate(),
                'identifier' => $entry->getIdentifier(),
                'failures' => $entry->getFailures(),
            ])
            ->executeStatement();
    }

    /**
     * We don't use the extbase-logic to do the DB-query - because we must do the DB-query BEFORE the FE-user is initialised - and
     * this would lead to the TYPO3-deprecation 'Using extbase in a context without TypoScript. Will stop working with TYPO3 v13.'
     */
    public function updateEntry(Entry $entry): void
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(self::ENTRY_DB_TABLE);
        $queryBuilder->update(self::ENTRY_DB_TABLE)
            ->set('failures', $entry->getFailures())
            ->set('tstamp', $entry->getTstamp())
            ->where(
                $queryBuilder->expr()
                    ->eq('identifier', $queryBuilder->createNamedParameter($entry->getIdentifier(), \PDO::PARAM_STR)),
            )
            ->executeStatement();
    }

    /**
     * We don't use the extbase-logic to do the DB-query - because we must do the DB-query BEFORE the FE-user is initialised - and
     * this would lead to the TYPO3-deprecation 'Using extbase in a context without TypoScript. Will stop working with TYPO3 v13.'
     */
    public function removeEntry(Entry $entry): void
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(self::ENTRY_DB_TABLE);
        $queryBuilder->delete(self::ENTRY_DB_TABLE)
            ->where(
                $queryBuilder->expr()
                    ->eq('identifier', $queryBuilder->createNamedParameter($entry->getIdentifier(), \PDO::PARAM_STR)),
            )
            ->executeStatement();
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
