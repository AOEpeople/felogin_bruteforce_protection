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

/**
 * @package Aoe\FeloginBruteforceProtection\Domain\Service
 */
class RestrictionServiceClientIpRemoteAddressTest extends RestrictionServiceClientIpAbstract
{
    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    public function setUp()
    {
        parent::setUp();
        $this->configuration->expects(static::any())->method('getXForwardedFor')->will(static::returnValue(false));
    }

    /**
     * @test
     * @dataProvider dataProviderIsClientRestrictedWithExcludedIp
     * @param string $clientIp
     * @param array $excludedIPs
     * @param boolean $shouldClientRestricted
     */
    public function isClientRestrictedWithExcludedIpWithoutCIRD($clientIp, array $excludedIPs, $shouldClientRestricted)
    {
        $this->configuration->expects(static::any())->method('getExcludedIps')->will(static::returnValue($excludedIPs));
        $this->inject($this->restriction, 'configuration', $this->configuration);

        $_SERVER['REMOTE_ADDR'] = $clientIp;

        static::assertNotEquals($shouldClientRestricted, $this->restrictionIdentifier->checkPreconditions());
    }
}
