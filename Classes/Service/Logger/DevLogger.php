<?php

namespace Aoe\FeloginBruteforceProtection\Service\Logger;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class DevLogger
 *
 * @package Aoe\FeloginBruteforceProtection\Service\Logger
 */
class DevLogger implements LoggerInterface
{
    /**
     * @param string      $message        the message to log
     * @param int         $severity       type and severity of log entry
     * @param array|null  $additionalData optional Array of additional data for the log entry which will be logged too
     * @param string|null $packageKey     optional string with a free key for the application so the log entries are easier to find
     */
    public function log(
        $message,
        $severity = self::SEVERITY_NOTICE,
        $additionalData = null,
        $packageKey = null
    ): void {
        if (!isset($packageKey)) {
            $packageKey = '';
        }
        if (!isset($additionalData)) {
            $additionalData = false;
        }
        GeneralUtility::devLog($message, $packageKey, $severity, $additionalData);
    }
}
