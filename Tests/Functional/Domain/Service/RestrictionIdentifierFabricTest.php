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

use Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionIdentifierFabric;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use Aoe\FeloginBruteforceProtection\System\Configuration;

/**
 * @package Aoe\FeloginBruteforceProtection\Domain\Service
 */
class RestrictionIdentifierFabricTest extends FunctionalTestCase
{
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
    private $configuration;

    /**
     * @var FrontendUserAuthentication
     */
    private $frontendUserAuthentication;

    /**
     * @var RestrictionIdentifierFabric
     */
    private $restrictionIdentifierFabric;

    /**
     * (non-PHPdoc)
     */
    public function setUp()
    {
        $this->configuration = $this->createMock('Aoe\FeloginBruteforceProtection\System\Configuration');
        $this->frontendUserAuthentication = $this->createMock('TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication');
    }

    /**
     * @test
     */
    public function isInstanceOfRestrictionIdentifierClientIp()
    {
        $this->configuration->expects($this->any())->method('getIdentificationIdentifier')->will($this->returnValue(1));
        $this->restrictionIdentifierFabric = new RestrictionIdentifierFabric();

        $this->assertInstanceOf(
            'Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionIdentifierClientIp',
            $this->restrictionIdentifierFabric->getRestrictionIdentifier($this->configuration)
        );
    }

    /**
     * @test
     */
    public function isInstanceOfRestrictionIdentifierClientIpWithNonExistingIdentifier()
    {
        $this->configuration->expects($this->any())->method('getIdentificationIdentifier')->will($this->returnValue(10));
        $this->restrictionIdentifierFabric = new RestrictionIdentifierFabric();

        $this->assertInstanceOf(
            'Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionIdentifierClientIp',
            $this->restrictionIdentifierFabric->getRestrictionIdentifier($this->configuration)
        );
    }

    /**
     * @test
     */
    public function isInstanceOfRestrictionIdentifierClientIpWithSecondParam()
    {
        $this->configuration->expects($this->any())->method('getIdentificationIdentifier')->will($this->returnValue(1));
        $this->restrictionIdentifierFabric = new RestrictionIdentifierFabric();

        $this->assertInstanceOf(
            'Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionIdentifierClientIp',
            $this->restrictionIdentifierFabric->getRestrictionIdentifier($this->configuration, $this->frontendUserAuthentication)
        );
    }

    /**
     * @test
     */
    public function isInstanceOfRestrictionIdentifierClientIpWithoutConfigurationValue()
    {
        $this->restrictionIdentifierFabric = new RestrictionIdentifierFabric();

        $this->assertInstanceOf(
            'Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionIdentifierClientIp',
            $this->restrictionIdentifierFabric->getRestrictionIdentifier($this->configuration)
        );
    }

    /**
     * @test
     */
    public function isInstanceOfRestrictionIdentifierClientIpWithoutConfigurationValueWithSecondParam()
    {
        $this->restrictionIdentifierFabric = new RestrictionIdentifierFabric();

        $this->assertInstanceOf(
            'Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionIdentifierClientIp',
            $this->restrictionIdentifierFabric->getRestrictionIdentifier($this->configuration, $this->frontendUserAuthentication)
        );
    }

    /**
     * @test
     */
    public function isInstanceOfRestrictionIdentifierFrontendUsername()
    {
        $this->configuration->expects($this->any())->method('getIdentificationIdentifier')->will($this->returnValue(2));
        $this->restrictionIdentifierFabric = new RestrictionIdentifierFabric();

        $this->assertInstanceOf(
            'Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionIdentifierFrontendName',
            $this->restrictionIdentifierFabric->getRestrictionIdentifier($this->configuration, $this->frontendUserAuthentication)
        );

    }

    /**
     * @test
     */
    public function isInstanceOfRestrictionIdentifierClientIpWithMissingFrontendUsername()
    {
        $this->configuration->expects($this->any())->method('getIdentificationIdentifier')->will($this->returnValue(2));
        $this->restrictionIdentifierFabric = new RestrictionIdentifierFabric();

        $this->assertInstanceOf(
            'Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionIdentifierClientIp',
            $this->restrictionIdentifierFabric->getRestrictionIdentifier($this->configuration)
        );
    }
}
