<?php
namespace Aoe\FeloginBruteforceProtection\Tests\Functional\Domain\Service;

/***************************************************************
 * Copyright notice
 *
 * (c) 2018 AOE GmbH <dev@aoe.com>
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

use Aoe\FeloginBruteforceProtection\Domain\Repository\EntryRepository;
use Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionIdentifierFabric;
use Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionIdentifierClientIp;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionService;
use Aoe\FeloginBruteforceProtection\System\Configuration;

/**
 * @package Aoe\FeloginBruteforceProtection\Domain\Service
 */
class RestrictionServiceClientIpAbstract extends FunctionalTestCase
{
    protected $configurationToUseInTestInstance = [
        'SYS' => [
            'encryptionKey' => '2929d9d6b1cad1be1b68bfc23807763b',
        ],
        'TYPO3_CONF_VARS' => []
    ];

    /**
     * @var array
     */
    protected $coreExtensionsToLoad = ['cms', 'core', 'lang', 'extensionmanager'];

    /**
     * @var array
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/felogin_bruteforce_protection'];

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var FrontendUserAuthentication
     */
    protected $frontendUserAuthentication;

    /**
     * @var RestrictionIdentifierFabric
     */
    protected $restrictionIdentifierFabric;

    /**
     * @var RestrictionIdentifierClientIp
     */
    protected $restrictionIdentifier;

    /**
     * @var RestrictionService
     */
    protected $restriction;

    public function setUp()
    {
        parent::setUp();

        $GLOBALS['TYPO3_CONF_VARS']['FE'] = [
            'cookieName' => 'testingCookie',
            'lockIP' => '',
            'checkFeUserPid' => '',
            'lifetime' => '',
            'sessionTimeout' => 300
        ];

        $this->configuration = $this->getMockBuilder('Aoe\FeloginBruteforceProtection\System\Configuration')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configuration->expects($this->any())->method('isLoggingEnabled')->will($this->returnValue(false));
        $this->frontendUserAuthentication = $this->getMockBuilder('TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication')->getMock();
        $this->configuration->expects($this->any())->method('getIdentificationIdentifier')->will($this->returnValue(1));
        $this->restrictionIdentifierFabric = new RestrictionIdentifierFabric();
        $this->restrictionIdentifier = $this->restrictionIdentifierFabric->getRestrictionIdentifier($this->configuration);
        $this->restriction = new RestrictionService($this->restrictionIdentifier);

        $logger = $this->getMockBuilder('\Aoe\FeloginBruteforceProtection\Service\Logger\Logger')
            ->setMethodsExcept(['log'])
            ->getMock();

        $this->inject(
            $this->restriction,
            'persistenceManager',
            $this->getMockBuilder('\TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager')->getMock()
        );
        $this->inject($this->restriction, 'logger', $logger);
    }

    /**
     * @test
     */
    public function isNotClientRestrictedWhenMaximumNumbersOfFailureNotReached()
    {
        $this->configuration->expects($this->any())->method('getMaximumNumberOfFailures')->will($this->returnValue(10));
        $this->configuration->expects($this->any())->method('getResetTime')->will($this->returnValue(300));
        $this->configuration->expects($this->any())->method('getRestrictionTime')->will($this->returnValue(3000));
        $entryRepository = $this->getAccessibleMock(EntryRepository::class, ['findByIdentifier', 'remove'], [], '', false);

        $entry = $this->getMockBuilder('Aoe\FeloginBruteforceProtection\Domain\Model\Entry')->getMock();
        $entry->expects($this->any())->method('getFailures')->will($this->returnValue(0));
        $entry->expects($this->any())->method('getCrdate')->will($this->returnValue(time() - 200));

        $entryRepository->expects($this->any())->method('findByIdentifier')->will($this->returnValue($entry));
        $this->inject($this->restriction, 'entryRepository', $entryRepository);
        $this->inject($this->restriction, 'configuration', $this->configuration);

        $this->assertFalse($this->restriction->isClientRestricted());
    }

    /**
     * @test
     */
    public function isClientRestrictedWithFailures()
    {
        $this->configuration->expects($this->any())->method('getMaximumNumberOfFailures')->will($this->returnValue(10));
        $this->configuration->expects($this->any())->method('getResetTime')->will($this->returnValue(300));
        $this->configuration->expects($this->any())->method('getRestrictionTime')->will($this->returnValue(3000));
        $entry = $this->getMockBuilder('Aoe\FeloginBruteforceProtection\Domain\Model\Entry')->disableOriginalConstructor()->getMock();
        $entry->expects($this->any())->method('getFailures')->will($this->returnValue(10));
        $entry->expects($this->any())->method('getCrdate')->will($this->returnValue(time() - 400));
        $entry->expects($this->any())->method('getTstamp')->will($this->returnValue(time() - 400));
        $entryRepository = $this->getAccessibleMock(EntryRepository::class, ['findByIdentifier', 'remove'], [], '', false);
        $entryRepository->expects($this->any())->method('findByIdentifier')->will($this->returnValue($entry));
        $this->inject($this->restriction, 'entryRepository', $entryRepository);
        $this->inject($this->restriction, 'configuration', $this->configuration);
        $this->assertTrue($this->restriction->isClientRestricted());
    }

    /**
     * @test
     */
    public function isClientRestrictedWithFailuresAndTimeout()
    {
        $this->configuration->expects($this->any())->method('getMaximumNumberOfFailures')->will($this->returnValue(10));
        $this->configuration->expects($this->any())->method('getResetTime')->will($this->returnValue(300));
        $this->configuration->expects($this->any())->method('getRestrictionTime')->will($this->returnValue(3000));

        $entry = $this->getMockBuilder('Aoe\FeloginBruteforceProtection\Domain\Model\Entry')->getMock();
        $entry->expects($this->any())->method('getFailures')->will($this->returnValue(10));
        $entry->expects($this->any())->method('getCrdate')->will($this->returnValue(time() - 200));
        $entry->expects($this->any())->method('getTstamp')->will($this->returnValue(time() - 4000));
        $entryRepository = $this->getAccessibleMock(EntryRepository::class, ['findByIdentifier', 'remove'], [], '', false);
        $entryRepository->expects($this->any())->method('findByIdentifier')->will($this->returnValue($entry));
        $this->inject($this->restriction, 'entryRepository', $entryRepository);
        $this->inject($this->restriction, 'configuration', $this->configuration);
        $this->assertFalse($this->restriction->isClientRestricted());
    }

    /**
     * @test
     */
    public function isClientRestrictedWithLessFailures()
    {
        $this->configuration->expects($this->any())->method('getMaximumNumberOfFailures')->will($this->returnValue(10));
        $this->configuration->expects($this->any())->method('getResetTime')->will($this->returnValue(300));
        $this->configuration->expects($this->any())->method('getRestrictionTime')->will($this->returnValue(3000));

        $entry = $this->getMockBuilder('Aoe\FeloginBruteforceProtection\Domain\Model\Entry')->getMock();
        $entry->expects($this->any())->method('getFailures')->will($this->returnValue(5));
        $entry->expects($this->any())->method('getCrdate')->will($this->returnValue(time() - 400));
        $entry->expects($this->any())->method('getTstamp')->will($this->returnValue(time() - 400));
        $entryRepository = $this->getAccessibleMock(EntryRepository::class, ['findByIdentifier', 'remove'], [], '', false);
        $entryRepository->expects($this->any())->method('findByIdentifier')->will($this->returnValue($entry));
        $this->inject($this->restriction, 'entryRepository', $entryRepository);
        $this->inject($this->restriction, 'configuration', $this->configuration);
        $this->assertFalse($this->restriction->isClientRestricted());
    }

    /**
     * @return array
     */
    public function dataProviderIsClientRestrictedWithExcludedIp()
    {
        return array(
            array('192.168.1.2', array('192.168.1.2'), true),
            array('192.168.1.2', array('192.0.0.0/8'), true),
            array('192.168.1.2', array('192.168.0.1'), false),
            array('192.168.1.2', array('192.168.2.0/24'), false),
        );
    }
}
