<?php
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
     */
    public function shouldMatchIpInRage()
    {
        $this->assertTrue(CIDRUtility::matchCIDR('10.5.21.30', '10.5.16.0/20'));
    }

    /**
     * @test
     */
    public function shouldNotMatchIpInRange()
    {
        $this->assertFalse(CIDRUtility::matchCIDR('192.168.50.2', '192.168.30.0/23'));
    }
}
