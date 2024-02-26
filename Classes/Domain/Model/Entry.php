<?php

namespace Aoe\FeloginBruteforceProtection\Domain\Model;

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

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Entry extends AbstractEntity
{
    /**
     * @var string
     */
    protected $tstamp;

    /**
     * @var string
     */
    protected $crdate;

    /**
     * @var string
     * @TYPO3\CMS\Extbase\Annotation\Validate(validator="NotEmpty")
     */
    protected $identifier;

    /**
     * @var integer
     * @TYPO3\CMS\Extbase\Annotation\Validate(validator="NotEmpty")
     */
    protected $failures;

    /**
     * @param string $crdate
     */
    public function setCrdate($crdate): void
    {
        $this->crdate = $crdate;
    }

    /**
     * @return string
     */
    public function getCrdate()
    {
        return $this->crdate;
    }

    /**
     * @param string $tstamp
     */
    public function setTstamp($tstamp): void
    {
        $this->tstamp = $tstamp;
    }

    /**
     * @return string
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier): void
    {
        $this->identifier = $identifier;
    }

    /**
     * @return integer
     */
    public function getFailures()
    {
        return $this->failures;
    }

    /**
     * @param integer $failures
     */
    public function setFailures($failures): void
    {
        $this->failures = $failures;
    }

    public function increaseFailures(): void
    {
        $this->setFailures($this->failures + 1);
    }
}
