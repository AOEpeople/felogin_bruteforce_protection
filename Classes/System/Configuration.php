<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Kevin Schu <kevin.schu@aoemedia.de>, AOE media GmbH
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

/**
 * @package felogin_bruteforce_protection
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_FeloginBruteforceProtection_System_Configuration
{
	/**
	 * @var string
	 */
	const CONF_MAX_FAILURES = 'max_failures';

	/**
	 * @var string
	 */
	const CONF_DISABLED = 'disabled';

	/**
	 * @var string
	 */
	const CONF_RESTRICTION_TIME = 'restriction_time';

	/**
	 * @var string
	 */
	const CONF_SECONDS_TILL_RESET = 'seconds_till_reset';

	/**
	 * @var array
	 */
	private $configuration = array();

	/**
	 * Initialize configuration array
	 */
	public function __construct()
	{
		$conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['felogin_bruteforce_protection']);
		if (is_array($conf)) {
			$this->configuration = $conf;
		}
	}

	/**
	 * @param string $key
	 * @return mixed
	 * @throws InvalidArgumentException
	 */
	public function get($key)
	{
		if (array_key_exists($key, $this->configuration)) {
			return $this->configuration[$key];
		}
		throw new InvalidArgumentException('Configuration key "' . $key . '" does not exist.');
	}
}
