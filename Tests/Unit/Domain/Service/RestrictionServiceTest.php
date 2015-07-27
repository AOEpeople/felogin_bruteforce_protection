<?php
namespace Aoe\FeloginBruteforceProtection\Tests\Unit\Domain\Service;

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
use Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionService;
use Aoe\FeloginBruteforceProtection\System\Configuration;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * @package Aoe\\FeloginBruteforceProtection\\Tests\\Unit\\Domain\\Service
 * @author Patrick Roos <patrick.roos@aoe.com>
 */
class RestrictionServiceTest extends UnitTestCase
{
    /**
     * @var RestrictionService
     */
    private $restrictionService;

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    public function setUp()
    {
        $configuration = new Configuration();
        $entryRepository = new EntryRepository();
        $persistenceManager = new PersistenceManager();


        //$this->restrictionService = new RestrictionService();
    }

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    public function tearDown()
    {
        //unset($this->restrictionService);
    }
}
