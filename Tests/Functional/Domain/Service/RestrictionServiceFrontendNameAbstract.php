<?php

namespace Aoe\FeloginBruteforceProtection\Tests\Functional\Domain\Service;

/***************************************************************
 * Copyright notice
 *
 * (c) 2019 AOE GmbH <dev@aoe.com>
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
use Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionIdentifierFabric;
use Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionIdentifierFrontendName;
use Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionService;
use Aoe\FeloginBruteforceProtection\Service\Logger\Logger;
use Aoe\FeloginBruteforceProtection\System\Configuration;
use Aoe\FeloginBruteforceProtection\Tests\Fixtures\Classes\EntryRepositoryMock;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class RestrictionServiceFrontendNameAbstract extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['typo3conf/ext/felogin_bruteforce_protection'];

    /**
     * @var FrontendUserAuthentication
     */
    protected MockObject $frontendUserAuthentication;

    /**
     * @var Configuration
     */
    private $configuration;

    private RestrictionIdentifierFabric $restrictionIdentifierFabric;

    private RestrictionIdentifierFrontendName $restrictionIdentifier;

    private RestrictionService $restriction;

    /**
     * (non-PHPdoc)
     */
    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['TYPO3_CONF_VARS']['FE'] = [
            'cookieName' => 'testingCookie',
            'lockIP' => '',
            'checkFeUserPid' => '',
            'lifetime' => '',
            'sessionTimeout' => 300,
        ];
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = md5('this-encryptionkey-is-only-for-testing');
        $GLOBALS['TYPO3_CONF_VARS']['FE']['lockIP'] = 0;
        $GLOBALS['TYPO3_CONF_VARS']['FE']['lockIPv6'] = 0;

        $this->configuration = $this->getAccessibleMock(
            Configuration::class,
            [],
            [],
            '',
            true
        );

        $this->configuration->method('isLoggingEnabled')
            ->willReturn(false);
        $this->frontendUserAuthentication = $this->getMockBuilder(FrontendUserAuthentication::class)
            ->getMock();
        $this->configuration->method('getIdentificationIdentifier')
            ->willReturn(2);
        $this->restrictionIdentifierFabric = new RestrictionIdentifierFabric();
        $this->restrictionIdentifier = $this->restrictionIdentifierFabric->getRestrictionIdentifier(
            $this->configuration,
            $this->frontendUserAuthentication
        );
        GeneralUtility::setSingletonInstance(
            PersistenceManager::class,
            $this->getMockBuilder(PersistenceManager::class)->disableOriginalConstructor()->getMock()
        );

        $logger = $this->getMockBuilder(Logger::class)
            ->onlyMethods(['log'])
            ->getMock();

        GeneralUtility::addInstance(Logger::class, $logger);
    }

    public function testIsClientRestricted(): void
    {
        $this->configuration->method('getMaximumNumberOfFailures')
            ->willReturn(10);
        $this->configuration->method('getResetTime')
            ->willReturn(300);
        $this->configuration->method('getRestrictionTime')
            ->willReturn(3000);

        $entryRepository = $this->getAccessibleMock(
            EntryRepositoryMock::class,
            ['findOneByIdentifier', 'remove'],
            [],
            '',
            false
        );

        $entry = $this->getMockBuilder(Entry::class)->getMock();
        $entry->method('getFailures')
            ->willReturn(0);
        $entry->method('getCrdate')
            ->willReturn(time() - 400);
        $entryRepository->method('findOneByIdentifier')
            ->willReturn($entry);

        GeneralUtility::setSingletonInstance(EntryRepository::class, $entryRepository);
        GeneralUtility::addInstance(Configuration::class, $this->configuration);

        $this->restriction = new RestrictionService($this->restrictionIdentifier);
        $this->assertFalse($this->restriction->isClientRestricted());
    }

    public function testIsClientRestrictedWithFailures(): void
    {
        $this->configuration->method('getMaximumNumberOfFailures')
            ->willReturn(10);
        $this->configuration->method('getResetTime')
            ->willReturn(300);
        $this->configuration->method('getRestrictionTime')
            ->willReturn(3000);
        $entry = $this->getMockBuilder(Entry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entry->method('getFailures')
            ->willReturn(10);
        $entry->method('getCrdate')
            ->willReturn(time() - 400);
        $entry->method('getTstamp')
            ->willReturn(time() - 400);
        $entryRepository = $this->getAccessibleMock(
            EntryRepositoryMock::class,
            ['findOneByIdentifier', 'remove'],
            [],
            '',
            false
        );
        $entryRepository->method('findOneByIdentifier')
            ->willReturn($entry);

        GeneralUtility::setSingletonInstance(EntryRepository::class, $entryRepository);
        GeneralUtility::addInstance(Configuration::class, $this->configuration);

        $this->restriction = new RestrictionService($this->restrictionIdentifier);
        $this->assertTrue($this->restriction->isClientRestricted());
    }

    public function testIsClientRestrictedWithFailuresAndTimeout(): void
    {
        $this->configuration->method('getMaximumNumberOfFailures')
            ->willReturn(10);
        $this->configuration->method('getResetTime')
            ->willReturn(300);
        $this->configuration->method('getRestrictionTime')
            ->willReturn(3000);
        $entry = $this->getMockBuilder(Entry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entry->method('getFailures')
            ->willReturn(10);
        $entry->method('getCrdate')
            ->willReturn(time() - 400);
        $entry->method('getTstamp')
            ->willReturn(time() - 4000);
        $entryRepository = $this->getAccessibleMock(
            EntryRepositoryMock::class,
            ['findOneByIdentifier', 'remove'],
            [],
            '',
            false
        );
        $entryRepository->method('findOneByIdentifier')
            ->willReturn($entry);

        GeneralUtility::setSingletonInstance(EntryRepository::class, $entryRepository);
        GeneralUtility::addInstance(Configuration::class, $this->configuration);

        $this->restriction = new RestrictionService($this->restrictionIdentifier);
        $this->assertFalse($this->restriction->isClientRestricted());
    }

    public function testIsClientRestrictedWithLessFailures(): void
    {
        $this->configuration->method('getMaximumNumberOfFailures')
            ->willReturn(10);
        $this->configuration->method('getResetTime')
            ->willReturn(300);
        $this->configuration->method('getRestrictionTime')
            ->willReturn(3000);
        $entry = $this->getMockBuilder(Entry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entry->method('getFailures')
            ->willReturn(5);
        $entry->method('getCrdate')
            ->willReturn(time() - 400);
        $entry->method('getTstamp')
            ->willReturn(time() - 400);
        $entryRepository = $this->getAccessibleMock(
            EntryRepositoryMock::class,
            ['findOneByIdentifier', 'remove'],
            [],
            '',
            false
        );
        $entryRepository->method('findOneByIdentifier')
            ->willReturn($entry);

        GeneralUtility::setSingletonInstance(EntryRepository::class, $entryRepository);
        GeneralUtility::addInstance(Configuration::class, $this->configuration);

        $this->restriction = new RestrictionService($this->restrictionIdentifier);
        $this->assertFalse($this->restriction->isClientRestricted());
    }

    public function testDoesCheckPreconditionsReturnTrue(): void
    {
        $this->assertTrue($this->restrictionIdentifier->checkPreconditions());
    }
}
