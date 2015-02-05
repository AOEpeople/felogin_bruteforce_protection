<?php
namespace Aoe\FeloginBruteforceProtection\Service\Logger;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 AOE GmbH <dev@aoe.com>
 *
 *  All rights reserved
 *
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
use TYPO3\CMS\Core as Core;
use TYPO3\CMS\Extbase\Utility as Utility;

/**
 * Class Logger
 * @package Aoe\FeloginBruteforceProtection\Service\Logger
 */
class Logger
{
    /** @var LoggerInterface */
    private $loggerImplementation;

    /** @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface */
    protected $objectManager;

    /** @var Configuration */
    protected $configuration;

    /**
     * @param LoggerInterface $loggerImplementation
     */
    public function injectLoggerImplementation(LoggerInterface $loggerImplementation)
    {
        $this->loggerImplementation = $loggerImplementation;
    }

    /**
     * @param $message, The Message to log
     * @param int $severity type and severity of log entry
     * @param array|null $additionalData optional Array of additional data for the log entry which will be logged too
     * @param string|null $packageKey optional string with a free key for the application so the log entries are easier
     *                                to find
     * @return void
     */
    public function log(
        $message,
        $severity = LoggerInterface::SEVERITY_INFO,
        $additionalData = null,
        $packageKey = null
    ) {
        if ($this->isLoggingEnabled() && $severity >= $this->getLogLevel()) {
            $this->getLoggerImplementation()->log($message, $severity, $additionalData, $packageKey);
        }
    }

    /**
     * @return bool
     */
    public function isLoggingEnabled()
    {
        return $this->getConfiguration()->isLoggingEnabled();
    }

    /**
     * @return int
     */
    public function getLogLevel()
    {
        return $this->getConfiguration()->getLogLevel();
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        if (!isset ($this->configuration)) {
            $this->configuration =
                $this->getObjectManager()->get('Aoe\FeloginBruteforceProtection\System\Configuration');
        }
        return $this->configuration;
    }

    /**
     * @return \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    protected function getObjectManager()
    {
        if (false === isset ($this->objectManager)) {
            $this->objectManager = Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        }
        return $this->objectManager;
    }

    /**
     * @return LoggerInterface
     */
    private function getLoggerImplementation()
    {
        if (!is_object($this->loggerImplementation)) {
            $loggerImplementation = $this->getObjectManager()->get(
                'Aoe\FeloginBruteforceProtection\Service\Logger\DevLogger'
            );
            $this->injectLoggerImplementation($loggerImplementation);
        }
        return $this->loggerImplementation;
    }
}
