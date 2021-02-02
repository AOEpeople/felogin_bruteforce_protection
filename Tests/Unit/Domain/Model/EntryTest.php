<?php

namespace Aoe\FeloginBruteforceProtection\Tests\Unit\Domain\Model;

/***************************************************************
 * Copyright notice
 *
 * (c) 2019 AOE GmbH <dev@aoe.com>
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
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * @package Aoe\\FeloginBruteforceProtection\\Tests\\Domain\\Model
 */
class EntryTest extends UnitTestCase
{
    /**
     * @var Entry
     */
    protected $entry;

    public function setUp()
    {
        $this->entry = new Entry();
    }

    /**
     * @test
     */
    public function setIdentifierForStringSetsIdentifier()
    {
        $this->entry->setIdentifier('Conceived at T3CON10');
        $this->assertSame('Conceived at T3CON10', $this->entry->getIdentifier());
    }

    /**
     * @test
     */
    public function setFailuresForIntegerSetsFailures()
    {
        $this->entry->setFailures(12);
        $this->assertSame(12, $this->entry->getFailures());
    }

    /**
     * @test
     **/
    public function setCrdateForTimestampStringSetsCrdate()
    {
        $time = 1;
        $this->entry->setCrdate($time);
        $this->assertSame($time, $this->entry->getCrdate());
    }

    /**
     * @test
     **/
    public function setTstampForTimestampStringSetsTstamp()
    {
        $time = 1;
        $this->entry->setTstamp($time);
        $this->assertSame($time, $this->entry->getTstamp());
    }

    /**
     * @test
     **/
    public function increaseFailuresShouldIncrementFailures()
    {
        $this->entry->setFailures(12);
        $this->entry->increaseFailures();
        $this->assertEquals(13, $this->entry->getFailures());
    }
}
