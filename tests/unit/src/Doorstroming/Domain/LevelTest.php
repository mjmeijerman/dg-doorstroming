<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

use PHPUnit_Framework_TestCase;

class LevelTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itCompares()
    {
        $level = Level::N3();
        $this->assertSame(-1, $level->compare(Level::R3()));
        $this->assertSame(0, $level->compare(Level::N3()));
        $this->assertSame(1, $level->compare(Level::N1()));
    }

    /**
     * @test
     */
    public function itKnowsIfALevelIsNational()
    {
        $nationalLevel = Level::N3();
        $otherLevel = Level::D1();

        $this->assertTrue($nationalLevel->isNationalLevel());
        $this->assertFalse($otherLevel->isNationalLevel());
    }
}
