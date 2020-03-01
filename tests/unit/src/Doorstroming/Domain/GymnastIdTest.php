<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

use PHPUnit_Framework_TestCase;

class GymnastIdTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itRepresentsAGymnastId()
    {
        $id = GymnastId::fromInteger(12);
        $this->assertSame(12, $id->toInteger());
    }

    /**
     * @test
     */
    public function itKnowsIfEqualToAnother()
    {
        $id = GymnastId::fromInteger(12);
        $this->assertTrue($id->equals(GymnastId::fromInteger(12)));
        $this->assertFalse($id->equals(GymnastId::fromInteger(14)));
    }
}
