<?php
namespace Aoe\FeloginBruteforceProtection\Tests\Unit\Domain\Model;

/***************************************************************
 * Copyright notice
 *
 * (c) 2013 Kevin Schu <kevin.schu@aoemedia.de>, AOE media GmbH
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

use Aoe\FeloginBruteforceProtection\Domain\Model\Entry;

/**
 * @package Aoe\FeloginBruteforceProtection\Tests\Domain\Model
 */
class EntryTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    /**
     * @var Entry
     */
    protected $fixture;

    public function setUp()
    {
        $this->fixture = new Entry();
    }

    public function tearDown()
    {
        unset ($this->fixture);
    }

    /**
     * @test
     */
    public function setIdentifierForStringSetsIdentifier()
    {
        $this->fixture->setIdentifier('Conceived at T3CON10');
        $this->assertSame('Conceived at T3CON10', $this->fixture->getIdentifier());
    }

    /**
     * @test
     */
    public function setFailuresForIntegerSetsFailures()
    {
        $this->fixture->setFailures(12);
        $this->assertSame(12, $this->fixture->getFailures());
    }
}
