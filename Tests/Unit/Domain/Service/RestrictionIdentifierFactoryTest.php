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

use Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionIdentifierFactory;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use Aoe\FeloginBruteforceProtection\System\Configuration;
use \TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * @package Aoe\FeloginBruteforceProtection\Domain\Service
 */
class RestrictionIdentifierFactoryTest extends UnitTestCase
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var FrontendUserAuthentication
     */
    private $frontendUserAuthentication;

    /**
     * @var RestrictionIdentifierFactory
     */
    private $restrictionIdentifierFactory;

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    public function setUp()
    {
        $this->configuration = $this->getMock(
            'Aoe\FeloginBruteforceProtection\System\Configuration',
            array(),
            array(),
            '',
            false
        );
        $this->frontendUserAuthentication = $this->getMock('TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication');
    }

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    public function tearDown()
    {
        unset($this->frontendUserAuthentication);
        unset($this->configuration);
        unset($this->restrictionIdentifierFactory);
    }

    /**
     * @test
     */
    public function isInstanceOfRestrictionIdentifierClientIp()
    {
        $this->configuration->expects($this->any())->method('getIdentificationIdentifier')->will($this->returnValue(1));
        $this->restrictionIdentifierFactory = new RestrictionIdentifierFactory();

        $this->assertInstanceOf(
            'Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionIdentifierClientIp',
            $this->restrictionIdentifierFactory->getRestrictionIdentifier($this->configuration)
        );
    }

    /**
     * @test
     */
    public function isInstanceOfRestrictionIdentifierClientIpWithNonExistingIdentifier()
    {
        $this->configuration->expects($this->any())->method('getIdentificationIdentifier')->will($this->returnValue(10));
        $this->restrictionIdentifierFactory = new RestrictionIdentifierFactory();

        $this->assertInstanceOf(
            'Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionIdentifierClientIp',
            $this->restrictionIdentifierFactory->getRestrictionIdentifier($this->configuration)
        );
    }

    /**
     * @test
     */
    public function isInstanceOfRestrictionIdentifierClientIpWithSecondParam()
    {
        $this->configuration->expects($this->any())->method('getIdentificationIdentifier')->will($this->returnValue(1));
        $this->restrictionIdentifierFactory = new RestrictionIdentifierFactory();

        $this->assertInstanceOf(
            'Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionIdentifierClientIp',
            $this->restrictionIdentifierFactory->getRestrictionIdentifier($this->configuration, $this->frontendUserAuthentication)
        );
    }

    /**
     * @test
     */
    public function isInstanceOfRestrictionIdentifierClientIpWithoutConfigurationValue()
    {
        $this->restrictionIdentifierFactory = new RestrictionIdentifierFactory();

        $this->assertInstanceOf(
            'Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionIdentifierClientIp',
            $this->restrictionIdentifierFactory->getRestrictionIdentifier($this->configuration)
        );
    }

    /**
     * @test
     */
    public function isInstanceOfRestrictionIdentifierClientIpWithoutConfigurationValueWithSecondParam()
    {
        $this->restrictionIdentifierFactory = new RestrictionIdentifierFactory();

        $this->assertInstanceOf(
            'Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionIdentifierClientIp',
            $this->restrictionIdentifierFactory->getRestrictionIdentifier($this->configuration, $this->frontendUserAuthentication)
        );
    }

    /**
     * @test
     */
    public function isInstanceOfRestrictionIdentifierFrontendUsername()
    {
        $this->configuration->expects($this->any())->method('getIdentificationIdentifier')->will($this->returnValue(2));
        $this->restrictionIdentifierFactory = new RestrictionIdentifierFactory();

        $this->assertInstanceOf(
            'Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionIdentifierFrontendName',
            $this->restrictionIdentifierFactory->getRestrictionIdentifier($this->configuration, $this->frontendUserAuthentication)
        );

    }

    /**
     * @test
     */
    public function isInstanceOfRestrictionIdentifierClientIpWithMissingFrontendUsername()
    {
        $this->configuration->expects($this->any())->method('getIdentificationIdentifier')->will($this->returnValue(2));
        $this->restrictionIdentifierFactory = new RestrictionIdentifierFactory();

        $this->assertInstanceOf(
            'Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionIdentifierClientIp',
            $this->restrictionIdentifierFactory->getRestrictionIdentifier($this->configuration)
        );
    }
}
