<?php

namespace Aoe\FeloginBruteforceProtection\Tests\Functional\System;

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

use Aoe\FeloginBruteforceProtection\System\Configuration;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ConfigurationTest
 */
class ConfigurationTest extends FunctionalTestCase
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
     * @var Configuration $configuration
     */
    private $configuration;

    public function setUp()
    {
        parent::setUp();
        $this->configuration = new Configuration();
    }

    /**
     * @test
     */
    public function doesIsEnabledReturnFalse()
    {
        $this->setGlobalConfigurationValue(Configuration::CONF_DISABLED, '1');
        $this->assertFalse($this->configuration->isEnabled());
    }

    /**
     * @test
     */
    public function doesIsEnabledReturnTrue()
    {
        $this->setGlobalConfigurationValue(Configuration::CONF_DISABLED, 0);
        $this->assertTrue($this->configuration->isEnabled());
    }

    /**
     * @test
     */
    public function checkGetMaximumNumberOfFailuresReturn()
    {
        $this->setGlobalConfigurationValue(Configuration::CONF_MAX_FAILURES, 10);
        $this->assertEquals(10, $this->configuration->getMaximumNumberOfFailures());
    }

    /**
     * @test
     */
    public function checkGetRestrictionTimeReturn()
    {
        $this->setGlobalConfigurationValue(Configuration::CONF_RESTRICTION_TIME, 300);
        $this->assertEquals(300, $this->configuration->getRestrictionTime());
    }

    /**
     * @test
     */
    public function checkGetResetTimeReturn()
    {
        $this->setGlobalConfigurationValue(Configuration::CONF_SECONDS_TILL_RESET, 50);
        $this->assertEquals(50, $this->configuration->getResetTime());
    }

    /**
     * @test
     */
    public function checkGetIdentificationIdentifierReturn()
    {
        $this->setGlobalConfigurationValue(Configuration::CONF_IDENTIFICATION_IDENTIFIER, 1);
        $this->assertEquals(1, $this->configuration->getIdentificationIdentifier());
    }

    /**
     * @test
     */
    public function doesGetXForwardedForReturnTrue()
    {
        $this->setGlobalConfigurationValue(Configuration::X_FORWARDED_FOR, 1);
        $this->assertEquals(true, $this->configuration->getXForwardedFor());
    }

    /**
     * @test
     */
    public function doesGetXForwardedForReturnFalse()
    {
        $this->setGlobalConfigurationValue(Configuration::X_FORWARDED_FOR, 0);
        $this->assertFalse($this->configuration->getXForwardedFor());
    }

    /**
     * @test
     */
    public function doesGetExcludedIpsReturnEmptyArray()
    {
        $this->setGlobalConfigurationValue(Configuration::EXCLUDED_IPS, '');
        $this->assertCount(1, $this->configuration->getExcludedIps());
    }

    /**
     * @test
     */
    public function doesGetExcludedIpsReturnArrayValues()
    {
        $this->setGlobalConfigurationValue(Configuration::EXCLUDED_IPS, '127.0.0.1,192.168.0.0.1,192.0.0.0/8');
        $this->assertCount(3, $this->configuration->getExcludedIps());
    }

    /**
     * @test
     * @expectedException Exception
     */
    public function testExceptionOfGetFunction()
    {
        $this->configuration->get('thisKeyDoesNotExist');
    }

    /**
     * @param $key
     * @param $value
     */
    private function setGlobalConfigurationValue($key, $value)
    {
        $config = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('felogin_bruteforce_protection');
        $config[$key] = $value;
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['felogin_bruteforce_protection'] = $config;
        $this->configuration = new Configuration();
    }
}
