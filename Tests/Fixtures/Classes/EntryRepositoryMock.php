<?php

namespace Aoe\FeloginBruteforceProtection\Tests\Fixtures\Classes;

use Aoe\FeloginBruteforceProtection\Domain\Model\Entry;
use Aoe\FeloginBruteforceProtection\Domain\Repository\EntryRepository;

/**
 * This Class is used to mock magic repository methods
 */
class EntryRepositoryMock extends EntryRepository
{
    public function findOneByIdentifier(): Entry
    {
    }
}
