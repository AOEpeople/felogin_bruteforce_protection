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

use Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionIdentifierClientIp;
use Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionIdentifierFabric;
use Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionIdentifierFrontendName;
use Aoe\FeloginBruteforceProtection\System\Configuration;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class RestrictionIdentifierFabricTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['typo3conf/ext/felogin_bruteforce_protection'];

    /**
     * @var Configuration
     */
    private MockObject $configuration;

    /**
     * @var FrontendUserAuthentication
     */
    private MockObject $frontendUserAuthentication;

    /**
     * @var RestrictionIdentifierFabric
     */
    private $restrictionIdentifierFabric;

    /**
     * (non-PHPdoc)
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->configuration = $this->createMock(Configuration::class);
        $this->frontendUserAuthentication = $this->createMock(
            FrontendUserAuthentication::class
        );
    }

    public function testIsInstanceOfRestrictionIdentifierClientIp(): void
    {
        $this->configuration
            ->method('getIdentificationIdentifier')
            ->willReturn(1);
        $this->restrictionIdentifierFabric = new RestrictionIdentifierFabric();

        $this->assertInstanceOf(
            RestrictionIdentifierClientIp::class,
            $this->restrictionIdentifierFabric->getRestrictionIdentifier($this->configuration)
        );
    }

    public function testIsInstanceOfRestrictionIdentifierClientIpWithNonExistingIdentifier(): void
    {
        $this->configuration
            ->method('getIdentificationIdentifier')
            ->willReturn(10);
        $this->restrictionIdentifierFabric = new RestrictionIdentifierFabric();

        $this->assertInstanceOf(
            RestrictionIdentifierClientIp::class,
            $this->restrictionIdentifierFabric->getRestrictionIdentifier($this->configuration)
        );
    }

    public function testIsInstanceOfRestrictionIdentifierClientIpWithSecondParam(): void
    {
        $this->configuration
            ->method('getIdentificationIdentifier')
            ->willReturn(1);
        $this->restrictionIdentifierFabric = new RestrictionIdentifierFabric();

        $this->assertInstanceOf(
            RestrictionIdentifierClientIp::class,
            $this->restrictionIdentifierFabric->getRestrictionIdentifier(
                $this->configuration,
                $this->frontendUserAuthentication
            )
        );
    }

    public function testIsInstanceOfRestrictionIdentifierClientIpWithoutConfigurationValue(): void
    {
        $this->restrictionIdentifierFabric = new RestrictionIdentifierFabric();

        $this->assertInstanceOf(
            RestrictionIdentifierClientIp::class,
            $this->restrictionIdentifierFabric->getRestrictionIdentifier($this->configuration)
        );
    }

    public function testIsInstanceOfRestrictionIdentifierClientIpWithoutConfigurationValueWithSecondParam(): void
    {
        $this->restrictionIdentifierFabric = new RestrictionIdentifierFabric();

        $this->assertInstanceOf(
            RestrictionIdentifierClientIp::class,
            $this->restrictionIdentifierFabric->getRestrictionIdentifier(
                $this->configuration,
                $this->frontendUserAuthentication
            )
        );
    }

    public function testIsInstanceOfRestrictionIdentifierFrontendUsername(): void
    {
        $this->configuration
            ->method('getIdentificationIdentifier')
            ->willReturn(2);
        $this->restrictionIdentifierFabric = new RestrictionIdentifierFabric();

        $this->assertInstanceOf(
            RestrictionIdentifierFrontendName::class,
            $this->restrictionIdentifierFabric->getRestrictionIdentifier(
                $this->configuration,
                $this->frontendUserAuthentication
            )
        );
    }

    public function testIsInstanceOfRestrictionIdentifierClientIpWithMissingFrontendUsername(): void
    {
        $this->configuration
            ->method('getIdentificationIdentifier')
            ->willReturn(2);
        $this->restrictionIdentifierFabric = new RestrictionIdentifierFabric();

        $this->assertInstanceOf(
            RestrictionIdentifierClientIp::class,
            $this->restrictionIdentifierFabric->getRestrictionIdentifier($this->configuration)
        );
    }
}
