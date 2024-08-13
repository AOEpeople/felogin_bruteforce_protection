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
use Exception;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class ConfigurationTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['typo3conf/ext/felogin_bruteforce_protection'];

    private Configuration $configuration;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configuration = new Configuration();
    }

    public function testDoesIsEnabledReturnFalse(): void
    {
        $this->setGlobalConfigurationValue(Configuration::CONF_DISABLED, '1');
        $this->assertFalse($this->configuration->isEnabled());
    }

    public function testDoesIsEnabledReturnTrue(): void
    {
        $this->setGlobalConfigurationValue(Configuration::CONF_DISABLED, 0);
        $this->assertTrue($this->configuration->isEnabled());
    }

    public function testCheckGetMaximumNumberOfFailuresReturn(): void
    {
        $this->setGlobalConfigurationValue(Configuration::CONF_MAX_FAILURES, 10);
        $this->assertSame(10, $this->configuration->getMaximumNumberOfFailures());
    }

    public function testCheckGetRestrictionTimeReturn(): void
    {
        $this->setGlobalConfigurationValue(Configuration::CONF_RESTRICTION_TIME, 300);
        $this->assertSame(300, $this->configuration->getRestrictionTime());
    }

    public function testCheckGetResetTimeReturn(): void
    {
        $this->setGlobalConfigurationValue(Configuration::CONF_SECONDS_TILL_RESET, 50);
        $this->assertSame(50, $this->configuration->getResetTime());
    }

    public function testCheckGetIdentificationIdentifierReturn(): void
    {
        $this->setGlobalConfigurationValue(Configuration::CONF_IDENTIFICATION_IDENTIFIER, 1);
        $this->assertSame(1, $this->configuration->getIdentificationIdentifier());
    }

    public function testDoesGetXForwardedForReturnTrue(): void
    {
        $this->setGlobalConfigurationValue(Configuration::X_FORWARDED_FOR, 1);
        $this->assertTrue($this->configuration->getXForwardedFor());
    }

    public function testDoesGetXForwardedForReturnFalse(): void
    {
        $this->setGlobalConfigurationValue(Configuration::X_FORWARDED_FOR, 0);
        $this->assertFalse($this->configuration->getXForwardedFor());
    }

    public function testDoesGetExcludedIpsReturnEmptyArray(): void
    {
        $this->setGlobalConfigurationValue(Configuration::EXCLUDED_IPS, '');
        $this->assertCount(1, $this->configuration->getExcludedIps());
    }

    public function testDoesGetExcludedIpsReturnArrayValues(): void
    {
        $this->setGlobalConfigurationValue(Configuration::EXCLUDED_IPS, '127.0.0.1,192.168.0.0.1,192.0.0.0/8');
        $this->assertCount(3, $this->configuration->getExcludedIps());
    }

    public function testExceptionOfGetFunction(): void
    {
        $this->expectException(Exception::class);
        $this->configuration->get('thisKeyDoesNotExist');
    }

    private function setGlobalConfigurationValue(string $key, string | int $value): void
    {
        $config = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('felogin_bruteforce_protection');
        $config[$key] = $value;
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['felogin_bruteforce_protection'] = $config;
        $this->configuration = new Configuration();
    }
}
