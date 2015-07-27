<?php
namespace Aoe\FeloginBruteforceProtection\Tests\Unit\Domain\Repository;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 AOE GmbH <dev@aoe.com>
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

use Aoe\FeloginBruteforceProtection\Domain\Repository\EntryRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @package Aoe\\FeloginBruteforceProtection\\Tests\\Unit\\Domain\\Repository
 * @author Patrick Roos <patrick.roos@aoe.com>
 */
class EntryRepositoryTest extends \Tx_Phpunit_Database_TestCase
{
    /**
     * @var EntryRepository $entryRepository
     */
    private $repository;

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    public function setUp()
    {
        $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        $this->repository = $objectManager->get('Aoe\FeloginBruteforceProtection\Domain\Repository\EntryRepository');
        $this->repository->initializeObject();
        $this->createDatabase();
        $this->useTestDatabase();
        $this->importExtensions(array('felogin_bruteforce_protection'));
    }

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    public function tearDown()
    {
        unset($this->entryRepository);
    }

    /**
     * @test
     */
    public function shouldFindEntriesToCleanUp()
    {
        $this->importDataSet(dirname(__FILE__) . '/fixtures/tx_feloginbruteforceprotection_domain_model_entry_row.xml');
        $entries = $this->repository->findEntriesToCleanUp(300, 10, 1800);
        $this->assertCount(2, $entries);
    }

    /**
     * @test
     */
    public function shouldFindEntriesWithIdentifierToCleanUp()
    {
        $this->importDataSet(dirname(__FILE__) . '/fixtures/tx_feloginbruteforceprotection_domain_model_entry_row.xml');
        $entries = $this->repository->findEntriesToCleanUp(300, 10, 1800, '348fc3b487c9b7853b8be6a1e514b16d');
        $this->assertCount(1, $entries);
    }
}
