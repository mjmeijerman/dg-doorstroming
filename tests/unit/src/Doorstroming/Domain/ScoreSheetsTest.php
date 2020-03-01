<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

use PHPUnit_Framework_TestCase;

class ScoreSheetsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itRepresentsScoreSheets()
    {
        $gymnast1 = Gymnast::create(
            GymnastId::fromInteger(1),
            'Name 1',
            'Club 1',
            43.104,
            12.30,
            true
        );

        $categoryLevelCombination1 = CategoryLevelCombination::create(
            Category::JEUGD1(),
            Level::N3()
        );
        $scoreSheet1               = ScoreSheet::create(
            'Medaillegroep A',
            1,
            $categoryLevelCombination1,
            [$gymnast1]
        );

        $categoryLevelCombination2 = CategoryLevelCombination::create(
            Category::JEUGD1(),
            Level::N2()
        );
        $scoreSheet2               = ScoreSheet::create(
            'Medaillegroep B',
            1,
            $categoryLevelCombination2,
            [$gymnast1]
        );

        $scoreSheetArray = [
            $scoreSheet1,
            $scoreSheet2,
        ];

        $scoreSheets = ScoreSheets::create($scoreSheetArray);
        $this->assertEquals(
            CategoryLevelCombinations::create([$categoryLevelCombination2, $categoryLevelCombination1]),
            $scoreSheets->getCategoryLevelCombinations()
        );

        $this->assertSame($scoreSheetArray, $scoreSheets->toArray());
    }
}
