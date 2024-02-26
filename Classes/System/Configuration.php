<?php

namespace Aoe\FeloginBruteforceProtection\System;

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

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception;

class Configuration
{
    /**
     * @var string
     */
    public const CONF_MAX_FAILURES = 'max_failures';

    /**
     * @var string
     */
    public const CONF_DISABLED = 'disabled';

    /**
     * @var string
     */
    public const CONF_RESTRICTION_TIME = 'restriction_time';

    /**
     * @var string
     */
    public const CONF_SECONDS_TILL_RESET = 'seconds_till_reset';

    /**
     * @var string
     */
    public const LOGGING_ENABLED = 'logging_enabled';

    /**
     * @var string
     */
    public const LOGGING_LEVEL = 'logging_level';

    /**
     * @var string
     */
    public const EXCLUDED_IPS = 'exclude_ips';

    /**
     * @var string
     */
    public const X_FORWARDED_FOR = 'x_forwarded_for';

    /**
     * @var string
     */
    public const CONF_IDENTIFICATION_IDENTIFIER = 'identification_identifier';

    /**
     * @var array
     */
    private $configuration = [];

    /**
     * Initialize configuration array
     */
    public function __construct()
    {
        $conf = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('felogin_bruteforce_protection');
        if (is_array($conf)) {
            $this->configuration = $conf;
        }
    }

    /**
     * Tells if the protection is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->get(self::CONF_DISABLED) !== '1';
    }

    /**
     * Returns the maximum number of allowed failures for an ip.
     */
    public function getMaximumNumberOfFailures(): int
    {
        return (int) $this->get(self::CONF_MAX_FAILURES);
    }

    /**
     * Returns the number of seconds of the restriction time.
     */
    public function getRestrictionTime(): int
    {
        return (int) $this->get(self::CONF_RESTRICTION_TIME);
    }

    /**
     * Returns the number of seconds after an entry is resetted.
     */
    public function getResetTime(): int
    {
        return (int) $this->get(self::CONF_SECONDS_TILL_RESET);
    }

    public function isLoggingEnabled(): bool
    {
        return (bool) $this->get(self::LOGGING_ENABLED) == 1;
    }

    public function getLogLevel(): int
    {
        return (int) $this->get(self::LOGGING_LEVEL);
    }

    public function getExcludedIps(): array
    {
        return explode(',', (string) $this->get(self::EXCLUDED_IPS));
    }

    public function getXForwardedFor(): bool
    {
        return (bool) $this->get(self::X_FORWARDED_FOR);
    }

    public function getIdentificationIdentifier(): int
    {
        return (int) $this->get(self::CONF_IDENTIFICATION_IDENTIFIER);
    }

    /**
     * @return mixed
     */
    public function get(string $key)
    {
        if (array_key_exists($key, $this->configuration)) {
            return $this->configuration[$key];
        }

        throw new Exception('Configuration key "' . $key . '" does not exist.');
    }
}
