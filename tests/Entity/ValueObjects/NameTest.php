<?php

namespace App\Tests\Entity\ValueObjects;

use App\Entity\ValueObjects\Name;
use App\Tests\AbstractWebTestCase;

class NameTest extends AbstractWebTestCase
{
    /**
     * @testWith    ["Rosalind", "Franklin"]
     *              ["Retta", null]
     *              [null, "Lovelace"]
     */
    public function testIsValidReturnsTrueWhenFirstAndOrLastSet($first, $last)
    {
        $name = new Name($first, $last);
        $this->assertTrue($name->isValid());
    }

    public function testIsValidReturnsFalseWhenFirstAndLastMissing()
    {
        $name = new Name();
        $this->assertFalse($name->isValid());
    }
}
