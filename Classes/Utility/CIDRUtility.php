<?php

namespace Aoe\FeloginBruteforceProtection\Utility;

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

class CIDRUtility
{
    /**
     * @param string $ip
     * @param string $range
     */
    public static function matchCIDR($ip, $range): bool
    {
        [$subnet, $bits] = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask; # nb: in case the supplied subnet wasn't correctly aligned

        return ($ip & $mask) == $subnet;
    }

    /**
     * Checks if an ip address corresponds to the CIDR format.
     * Valid CIDR: 192.168.100.14/24
     * Invalid CIDR: 192.168.100.14
     */
    public static function isCIDR(string $ip): bool
    {
        $ipBlock = '\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5]';
        $subnetMask = '\d|[1-2]\d|3[0-2]';
        $pattern = "/^(($ipBlock)\.){3}($ipBlock)(\/($subnetMask))$/";

        return preg_match($pattern, $ip) > 0;
    }
}
