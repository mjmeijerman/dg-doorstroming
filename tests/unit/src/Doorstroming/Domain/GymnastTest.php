<?php

declare(strict_types=1);

namespace Mark\unit\src\Doorstroming\Domain;

use Mark\Doorstroming\Domain\Gymnast;
use Mark\Doorstroming\Domain\GymnastId;
use PHPUnit\Framework\TestCase;

class GymnastTest extends TestCase
{
    /**
     * @test
     */
    public function itComparesTwoGymnastsWhenTheyAreEqual()
    {
        $gymnast1 = Gymnast::create(
            GymnastId::fromInteger(1),
            'Name 1',
            'Club 1',
            43.104,
            12.30
        );

        $gymnast2 = Gymnast::create(
            GymnastId::fromInteger(1),
            'Name 2',
            'Club 2',
            43.104,
            12.30
        );

        $this->assertSame(0, $gymnast1->compare($gymnast2));
    }

    /**
     * @test
     */
    public function itComparesTwoGymnastsWhenTheyAreNotEqual()
    {
        $gymnast1 = Gymnast::create(
            GymnastId::fromInteger(1),
            'Name 1',
            'Club 1',
            43.4,
            12.30
        );

        $gymnast2 = Gymnast::create(
            GymnastId::fromInteger(1),
            'Name 2',
            'Club 2',
            43.104,
            12.30
        );

        $this->assertSame(-1, $gymnast1->compare($gymnast2));
        $this->assertSame(1, $gymnast2->compare($gymnast1));
    }
}
