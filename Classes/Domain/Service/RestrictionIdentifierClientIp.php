<?php

namespace Aoe\FeloginBruteforceProtection\Domain\Service;

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
use Aoe\FeloginBruteforceProtection\Utility\CIDRUtility;

class RestrictionIdentifierClientIp extends RestrictionIdentifierAbstract
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * the value of the restriction identifier
     *
     * @return string
     */
    public function getIdentifierValue()
    {
        if (!isset($this->identifierValue)) {
            if ($this->configuration->getXForwardedFor() && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $this->identifierValue = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $this->identifierValue = $_SERVER['REMOTE_ADDR'];
            }
        }

        return $this->identifierValue;
    }

    /**
     * when IP is excluded no restriction check is necessary
     */
    public function checkPreconditions(): bool
    {
        return !$this->isIpExcluded();
    }

    public function setConfiguration(Configuration $configuration): void
    {
        $this->configuration = $configuration;
    }

    /**
     * checks if the IP is excluded for restriction
     */
    protected function isIpExcluded(): bool
    {
        if (in_array($this->getIdentifierValue(), $this->configuration->getExcludedIps())) {
            return true;
        }

        foreach ($this->configuration->getExcludedIps() as $excludedIp) {
            // CIDR notation is used within excluded IPs
            if (CIDRUtility::isCIDR($excludedIp) && CIDRUtility::matchCIDR($this->getIdentifierValue(), $excludedIp)) {
                return true;
            }
        }

        return false;
    }
}
