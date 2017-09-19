<?php

namespace Aoe\FeloginBruteforceProtection\Tests\Unit\Domain\Service;

/***************************************************************
 * Copyright notice
 *
 * (c) 2014 Kevin Schu <dev@aoe.com>, AOE GmbH
 *
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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

use Aoe\FeloginBruteforceProtection\Domain\Model\Entry;
use Aoe\FeloginBruteforceProtection\Domain\Repository\EntryRepository;
use Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionIdentifierFactory;
use Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionIdentifierFrontendName;
use Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionService;
use Aoe\FeloginBruteforceProtection\Service\Logger\Logger;
use Aoe\FeloginBruteforceProtection\System\Configuration;
use PHPUnit_Framework_MockObject_MockObject;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * @package Aoe\FeloginBruteforceProtection\Domain\Service
 */
class RestrictionServiceFrontendNameAbstract extends UnitTestCase
{
    /**
     * @var Configuration|PHPUnit_Framework_MockObject_MockObject
     */
    private $configuration;

    /**
     * @var FrontendUserAuthentication|PHPUnit_Framework_MockObject_MockObject
     */
    protected $frontendUserAuthentication;

    /**
     * @var RestrictionIdentifierFactory
     */
    private $restrictionIdentifierFactory;

    /**
     * @var RestrictionIdentifierFrontendName
     */
    private $restrictionIdentifier;
    /**
     * @var RestrictionService
     */
    private $restriction;

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    public function setUp()
    {
        $this->configuration = $this->getMock(
            'Aoe\FeloginBruteforceProtection\System\Configuration',
            [],
            [],
            '',
            false
        );
        $this->configuration->expects(static::any())->method('isLoggingEnabled')->will(static::returnValue(false));
        $this->frontendUserAuthentication = $this->getMock(FrontendUserAuthentication::class);
        $this->configuration
            ->expects(static::any())
            ->method('getIdentificationIdentifier')
            ->will(static::returnValue(2));
        $this->restrictionIdentifierFactory = new RestrictionIdentifierFactory();
        $this->restrictionIdentifier = $this->restrictionIdentifierFactory->getRestrictionIdentifier(
            $this->configuration,
            $this->frontendUserAuthentication
        );
        $this->restriction = new RestrictionService($this->restrictionIdentifier);
        $this->inject(
            $this->restriction,
            'persistenceManager',
            $this->getMock(PersistenceManager::class)
        );
        $logger = $this->getMock(Logger::class, ['log']);
        $this->inject($this->restriction, 'logger', $logger);
    }

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    public function tearDown()
    {
        parent::tearDown();
        unset($this->frontendUserAuthentication);
        unset($this->configuration);
        unset($this->restrictionIdentifier);
    }

    /**
     * @test
     */
    public function isClientRestricted()
    {
        $this->configuration
            ->expects(static::any())
            ->method('getMaximumNumberOfFailures')
            ->will(static::returnValue(10));
        $this->configuration->expects(static::any())->method('getResetTime')->will(static::returnValue(300));
        $this->configuration->expects(static::any())->method('getRestrictionTime')->will(static::returnValue(3000));
        $entryRepository = $this->getMock(
            EntryRepository::class,
            ['findOneByIdentifier', 'remove'],
            [],
            '',
            false
        );
        $entry = $this->getMock(Entry::class);
        $entry->expects(static::any())->method('getFailures')->will(static::returnValue(0));
        $entry->expects(static::any())->method('getCrdate')->will(static::returnValue(time() - 400));
        $entryRepository->expects(static::any())->method('findOneByIdentifier')->will(static::returnValue($entry));
        $this->inject($this->restriction, 'entryRepository', $entryRepository);
        $this->inject($this->restriction, 'configuration', $this->configuration);

        static::assertFalse($this->restriction->isClientRestricted());
    }

    /**
     * @test
     */
    public function isClientRestrictedWithFailures()
    {
        $this->configuration
            ->expects(static::any())
            ->method('getMaximumNumberOfFailures')
            ->will(static::returnValue(10));
        $this->configuration->expects(static::any())->method('getResetTime')->will(static::returnValue(300));
        $this->configuration->expects(static::any())->method('getRestrictionTime')->will(static::returnValue(3000));
        $entry = $this->getMock('Aoe\FeloginBruteforceProtection\Domain\Model\Entry', [], [], '', false);
        $entry->expects(static::any())->method('getFailures')->will(static::returnValue(10));
        $entry->expects(static::any())->method('getCrdate')->will(static::returnValue(time() - 400));
        $entry->expects(static::any())->method('getTstamp')->will(static::returnValue(time() - 400));
        $entryRepository = $this->getMock(
            EntryRepository::class,
            ['findOneByIdentifier', 'remove'],
            [],
            '',
            false
        );
        $entryRepository->expects(static::any())->method('findOneByIdentifier')->will(static::returnValue($entry));
        $this->inject($this->restriction, 'entryRepository', $entryRepository);
        $this->inject($this->restriction, 'configuration', $this->configuration);
        static::assertTrue($this->restriction->isClientRestricted());
    }

    /**
     * @test
     */
    public function isClientRestrictedWithFailuresAndTimeout()
    {
        $this->configuration
            ->expects(static::any())
            ->method('getMaximumNumberOfFailures')
            ->will(static::returnValue(10));
        $this->configuration->expects(static::any())->method('getResetTime')->will(static::returnValue(300));
        $this->configuration->expects(static::any())->method('getRestrictionTime')->will(static::returnValue(3000));
        $entry = $this->getMock('Aoe\FeloginBruteforceProtection\Domain\Model\Entry', [], [], '', false);
        $entry->expects(static::any())->method('getFailures')->will(static::returnValue(10));
        $entry->expects(static::any())->method('getCrdate')->will(static::returnValue(time() - 400));
        $entry->expects(static::any())->method('getTstamp')->will(static::returnValue(time() - 4000));
        $entryRepository = $this->getMock(
            EntryRepository::class,
            ['findOneByIdentifier', 'remove'],
            [],
            '',
            false
        );
        $entryRepository->expects(static::any())->method('findOneByIdentifier')->will(static::returnValue($entry));
        $this->inject($this->restriction, 'entryRepository', $entryRepository);
        $this->inject($this->restriction, 'configuration', $this->configuration);
        static::assertFalse($this->restriction->isClientRestricted());
    }

    /**
     * @test
     */
    public function isClientRestrictedWithLessFailures()
    {
        $this->configuration
            ->expects(static::any())
            ->method('getMaximumNumberOfFailures')
            ->will(static::returnValue(10));
        $this->configuration->expects(static::any())->method('getResetTime')->will(static::returnValue(300));
        $this->configuration->expects(static::any())->method('getRestrictionTime')->will(static::returnValue(3000));
        $entry = $this->getMock('Aoe\FeloginBruteforceProtection\Domain\Model\Entry', [], [], '', false);
        $entry->expects(static::any())->method('getFailures')->will(static::returnValue(5));
        $entry->expects(static::any())->method('getCrdate')->will(static::returnValue(time() - 400));
        $entry->expects(static::any())->method('getTstamp')->will(static::returnValue(time() - 400));
        $entryRepository = $this->getMock(
            EntryRepository::class,
            ['findOneByIdentifier', 'remove'],
            [],
            '',
            false
        );
        $entryRepository->expects(static::any())->method('findOneByIdentifier')->will(static::returnValue($entry));
        $this->inject($this->restriction, 'entryRepository', $entryRepository);
        $this->inject($this->restriction, 'configuration', $this->configuration);
        static::assertFalse($this->restriction->isClientRestricted());
    }

    /**
     * @test
     */
    public function doesCheckPreconditionsReturnTrue()
    {
        static::assertTrue($this->restrictionIdentifier->checkPreconditions());
    }
}
