<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

use PHPUnit\Framework\TestCase;

class ScoreSheetTest extends TestCase
{
    /**
     * @test
     */
    public function itCreatesASortedAndRankedScoreSheet()
    {
        $gymnast1 = Gymnast::create(
            GymnastId::fromInteger(1),
            'Name 1',
            'Club 1',
            43.104,
            12.30,
            true
        );

        $gymnast2 = Gymnast::create(
            GymnastId::fromInteger(1),
            'Name 1',
            'Club 1',
            43.103,
            12.30,
            false
        );

        $gymnast3 = Gymnast::create(
            GymnastId::fromInteger(1),
            'Name 1',
            'Club 1',
            43.105,
            12.30,
            true
        );

        $gymnast4 = Gymnast::create(
            GymnastId::fromInteger(1),
            'Name 1',
            'Club 1',
            43.104,
            12.30,
            true
        );

        $gymnast5 = Gymnast::create(
            GymnastId::fromInteger(1),
            'Name 1',
            'Club 1',
            43.9,
            12.30,
            true
        );

        $categoryLevelCombination = CategoryLevelCombination::create(
            Category::JEUGD1(),
            Level::N3()
        );
        $scoreSheet = ScoreSheet::create(
            'Medaillegroep A',
            1,
            $categoryLevelCombination,
            [$gymnast1, $gymnast2, $gymnast3, $gymnast4, $gymnast5]
        );

        $expectedGymnastList = [$gymnast5, $gymnast3, $gymnast1, $gymnast4, $gymnast2];
        $this->assertSame($expectedGymnastList, $scoreSheet->gymnasts());
        $this->assertSame(1, $gymnast5->rank());
        $this->assertSame(2, $gymnast3->rank());
        $this->assertSame(3, $gymnast1->rank());
        $this->assertSame(3, $gymnast4->rank());
        $this->assertSame(5, $gymnast2->rank());
        $this->assertSame(4, $scoreSheet->totalNumberOfFullParticipatedGymnasts());
        $this->assertSame('Medaillegroep A', $scoreSheet->identifier());
        $this->assertSame(1, $scoreSheet->scoreSheetNumber());
        $this->assertSame($categoryLevelCombination, $scoreSheet->categoryLevelCombination());
    }

    /**
     * @test
     */
    public function itFindsAGymnast()
    {
        $gymnast1 = Gymnast::create(
            GymnastId::fromInteger(1),
            'Name 1',
            'Club 1',
            43.104,
            12.30,
            true
        );

        $gymnast2 = Gymnast::create(
            GymnastId::fromInteger(2),
            'Name 1',
            'Club 1',
            43.103,
            12.30,
            false
        );

        $scoreSheet = ScoreSheet::create(
            'Medaillegroep A',
            1,
            CategoryLevelCombination::create(
                Category::JEUGD1(),
                Level::N3()
            ),
            [$gymnast1]
        );

        $this->assertSame($gymnast1, $scoreSheet->findGymnast($gymnast1->gymnastId()));
        $this->assertNull($scoreSheet->findGymnast($gymnast2->gymnastId()));
    }
}
