<?php
namespace Aoe\FeloginBruteforceProtection\Tests\Unit\Utility;

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

use Aoe\FeloginBruteforceProtection\Utility\CIDRUtility;

class CIDRUtilityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider dataProviderMatchIpInRange
     * @param string $ip
     * @param string $range
     */
    public function shouldMatchIpInRage($ip, $range)
    {
        $this->assertTrue(CIDRUtility::matchCIDR($ip, $range));
    }

    /**
     * @test
     * @dataProvider dataProviderNotMatchIpInRange
     * @param string $ip
     * @param string $range
     */
    public function shouldNotMatchIpInRange($ip, $range)
    {
        $this->assertFalse(CIDRUtility::matchCIDR($ip, $range));
    }

    /**
     * @test
     * @dataProvider dataProviderValidateIpAsCIRD
     * @param string $ip
     */
    public function shouldValidateIpAsCIRD($ip)
    {
        $this->assertTrue(CIDRUtility::isCIDR($ip));
    }

    /**
     * @test
     * @dataProvider dataProviderNotValidateIpAsCIRD
     * @param string $ip
     */
    public function shouldNotValidateIpAsCIRD($ip)
    {
        $this->assertFalse(CIDRUtility::isCIDR($ip));
    }

    /**
     * @return array
     */
    public function dataProviderMatchIpInRange()
    {
        return array(
            array('192.168.30.2', '192.0.0.0/8'),
            array('192.168.30.2', '192.168.0.0/16'),
            array('192.168.30.2', '192.168.30.0/24'),
            array('192.168.30.2', '192.168.30.2/32')
        );
    }

    /**
     * @return array
     */
    public function dataProviderNotMatchIpInRange()
    {
        return array(
            array('197.190.30.2', '192.0.0.0/8'),
            array('192.192.30.2', '192.168.0.0/16'),
            array('192.168.32.2', '192.168.30.0/24'),
            array('192.168.30.4', '192.168.30.2/32')
        );
    }

    /**
     * @return array
     */
    public function dataProviderValidateIpAsCIRD()
    {
        return array(
            array('192.0.0.0/8'),
            array('192.168.0.0/16'),
            array('192.168.30.0/24'),
            array('192.168.30.2/32')
        );
    }

    /**
     * @return array
     */
    public function dataProviderNotValidateIpAsCIRD()
    {
        return array(
            array('192.0.0.0'),
            array('teststring'),
        );
    }
}
