<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

use PHPUnit_Framework_TestCase;

class UuidTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itRepresentsAUuid()
    {
        $uuid = TestId::generate();
        $this->assertInstanceOf(Uuid::class, $uuid);
    }

    /**
     * @test
     */
    public function itEqualsAnother()
    {
        $uuid1 = TestId::generate();
        $uuid2 = TestId::fromString($uuid1->toString());

        $this->assertTrue($uuid1->equals($uuid2));
        $this->assertFalse($uuid1->equals(TestId::generate()));
    }

    /**
     * @test
     */
    public function itIsNotCreatedFromAnInvalidFormat()
    {
        $this->expectException(\LogicException::class);
        TestId::fromString('Invalid');
    }
}
