<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

use LogicException;
use PHPUnit_Framework_TestCase;

class DoorstromingListCreatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itDoesNotCreateAListWithIncorrectInputIdentifiers()
    {
        $gymnast1  = Gymnast::create(
            GymnastId::fromInteger(1),
            'name 1',
            'club 1',
            43.12,
            13.43,
            true
        );
        $gymnast2  = Gymnast::create(
            GymnastId::fromInteger(2),
            'name 2',
            'club 2',
            40.12,
            11.43,
            true
        );
        $gymnast3  = Gymnast::create(
            GymnastId::fromInteger(3),
            'name 3',
            'club 3',
            46.12,
            14.43,
            false
        );
        $gymnast4  = Gymnast::create(
            GymnastId::fromInteger(4),
            'name 4',
            'club 4',
            42.12,
            15.43,
            true
        );
        $gymnasts1 = [
            $gymnast1,
            $gymnast2,
            $gymnast3,
            $gymnast4,
        ];

        $gymnasts2 = [
            $gymnast1,
            $gymnast3,
            $gymnast4,
        ];

        $identifier1              = 'something';
        $identifier2              = 'something else';
        $scoreSheetNumber1        = 1;
        $scoreSheetNumber2        = 1;
        $categoryLevelCombination = CategoryLevelCombination::create(Category::JEUGD1(), Level::D1());

        $firstCompetitionScoreSheet  = ScoreSheet::create(
            $identifier1,
            $scoreSheetNumber1,
            $categoryLevelCombination,
            $gymnasts1
        );
        $secondCompetitionScoreSheet = ScoreSheet::create(
            $identifier2,
            $scoreSheetNumber2,
            $categoryLevelCombination,
            $gymnasts2
        );
        $numberOfSpotsAvailable      = 4;

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'Can not compare score sheet with identifier "' . $identifier1 . '" to score sheet with identifier "' . $identifier2 . '"'
        );
        DoorstromingListCreator::create(
            $firstCompetitionScoreSheet,
            $secondCompetitionScoreSheet,
            $numberOfSpotsAvailable,
            1,
            1
        );
    }

    /**
     * @test
     */
    public function itDoesNotCreateAListWithIncorrectScoreSheetNumber()
    {
        $gymnast1  = Gymnast::create(
            GymnastId::fromInteger(1),
            'name 1',
            'club 1',
            43.12,
            13.43,
            true
        );
        $gymnast2  = Gymnast::create(
            GymnastId::fromInteger(2),
            'name 2',
            'club 2',
            40.12,
            11.43,
            true
        );
        $gymnast3  = Gymnast::create(
            GymnastId::fromInteger(3),
            'name 3',
            'club 3',
            46.12,
            14.43,
            false
        );
        $gymnast4  = Gymnast::create(
            GymnastId::fromInteger(4),
            'name 4',
            'club 4',
            42.12,
            15.43,
            true
        );
        $gymnasts1 = [
            $gymnast1,
            $gymnast2,
            $gymnast3,
            $gymnast4,
        ];

        $gymnasts2 = [
            $gymnast1,
            $gymnast3,
            $gymnast4,
        ];

        $identifier               = 'something';
        $scoreSheetNumber1        = 1;
        $scoreSheetNumber2        = 1;
        $categoryLevelCombination = CategoryLevelCombination::create(Category::JEUGD1(), Level::D1());

        $firstCompetitionScoreSheet  = ScoreSheet::create(
            $identifier,
            $scoreSheetNumber1,
            $categoryLevelCombination,
            $gymnasts1
        );
        $secondCompetitionScoreSheet = ScoreSheet::create(
            $identifier,
            $scoreSheetNumber2,
            $categoryLevelCombination,
            $gymnasts2
        );
        $numberOfSpotsAvailable      = 4;

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Got the same score sheet twice');
        DoorstromingListCreator::create(
            $firstCompetitionScoreSheet,
            $secondCompetitionScoreSheet,
            $numberOfSpotsAvailable,
            1,
            1
        );
    }

    /**
     * @test
     */
    public function itCreatesAListWithMissingGymnastInSecondSheetByAddingThemToThatList()
    {
        $gymnast1  = Gymnast::create(
            GymnastId::fromInteger(1),
            'name 1',
            'club 1',
            43.12,
            13.43,
            true
        );
        $gymnast2  = Gymnast::create(
            GymnastId::fromInteger(2),
            'name 2',
            'club 2',
            40.12,
            11.43,
            true
        );
        $gymnast3  = Gymnast::create(
            GymnastId::fromInteger(3),
            'name 3',
            'club 3',
            46.12,
            14.43,
            false
        );
        $gymnast4  = Gymnast::create(
            GymnastId::fromInteger(4),
            'name 4',
            'club 4',
            42.12,
            15.43,
            true
        );
        $gymnasts1 = [
            $gymnast1,
            $gymnast2,
            $gymnast3,
            $gymnast4,
        ];

        $gymnasts2 = [
            $gymnast1,
            $gymnast3,
            $gymnast4,
        ];

        $identifier               = 'something';
        $scoreSheetNumber1        = 1;
        $scoreSheetNumber2        = 2;
        $categoryLevelCombination = CategoryLevelCombination::create(Category::JEUGD1(), Level::D1());

        $firstCompetitionScoreSheet  = ScoreSheet::create(
            $identifier,
            $scoreSheetNumber1,
            $categoryLevelCombination,
            $gymnasts1
        );
        $secondCompetitionScoreSheet = ScoreSheet::create(
            $identifier,
            $scoreSheetNumber2,
            $categoryLevelCombination,
            $gymnasts2
        );
        $numberOfSpotsAvailable      = 4;

        DoorstromingListCreator::create(
            $firstCompetitionScoreSheet,
            $secondCompetitionScoreSheet,
            $numberOfSpotsAvailable,
            1,
            1
        );
    }

    /**
     * @test
     */
    public function itCreatesAListWithMissingGymnastInFirstSheet()
    {
        $gymnast1  = Gymnast::create(
            GymnastId::fromInteger(1),
            'name 1',
            'club 1',
            43.12,
            13.43,
            true
        );
        $gymnast2  = Gymnast::create(
            GymnastId::fromInteger(2),
            'name 2',
            'club 2',
            40.12,
            11.43,
            true
        );
        $gymnast3  = Gymnast::create(
            GymnastId::fromInteger(3),
            'name 3',
            'club 3',
            46.12,
            14.43,
            false
        );
        $gymnast4  = Gymnast::create(
            GymnastId::fromInteger(4),
            'name 4',
            'club 4',
            42.12,
            15.43,
            true
        );
        $gymnasts1 = [
            $gymnast1,
            $gymnast3,
            $gymnast4,
        ];

        $gymnasts2 = [
            $gymnast1,
            $gymnast2,
            $gymnast3,
            $gymnast4,
        ];

        $identifier               = 'something';
        $scoreSheetNumber1        = 1;
        $scoreSheetNumber2        = 2;
        $categoryLevelCombination = CategoryLevelCombination::create(Category::JEUGD1(), Level::D1());

        $firstCompetitionScoreSheet  = ScoreSheet::create(
            $identifier,
            $scoreSheetNumber1,
            $categoryLevelCombination,
            $gymnasts1
        );
        $secondCompetitionScoreSheet = ScoreSheet::create(
            $identifier,
            $scoreSheetNumber2,
            $categoryLevelCombination,
            $gymnasts2
        );
        $numberOfSpotsAvailable      = 4;

        DoorstromingListCreator::create(
            $firstCompetitionScoreSheet,
            $secondCompetitionScoreSheet,
            $numberOfSpotsAvailable,
            1,
            1
        );
    }

    /**
     * @test
     */
    public function itCreatesADoorstromingList()
    {
        $gymnast1  = Gymnast::create(
            GymnastId::fromInteger(1),
            'name 1',
            'club 1',
            43.12,
            13.43,
            true
        );
        $gymnast2  = Gymnast::create(
            GymnastId::fromInteger(2),
            'name 2',
            'club 2',
            40.12,
            11.43,
            true
        );
        $gymnast3  = Gymnast::create(
            GymnastId::fromInteger(3),
            'name 3',
            'club 3',
            46.12,
            14.43,
            false
        );
        $gymnast4  = Gymnast::create(
            GymnastId::fromInteger(4),
            'name 4',
            'club 4',
            42.12,
            15.43,
            true
        );
        $gymnasts1 = [
            $gymnast1,
            $gymnast2,
            $gymnast3,
            $gymnast4,
        ];

        $gymnasts2 = [
            $gymnast1,
            $gymnast2,
            $gymnast3,
            $gymnast4,
        ];

        $identifier               = 'something';
        $scoreSheetNumber1        = 1;
        $scoreSheetNumber2        = 2;
        $categoryLevelCombination = CategoryLevelCombination::create(Category::JEUGD1(), Level::D1());

        $firstCompetitionScoreSheet  = ScoreSheet::create(
            $identifier,
            $scoreSheetNumber1,
            $categoryLevelCombination,
            $gymnasts1
        );
        $secondCompetitionScoreSheet = ScoreSheet::create(
            $identifier,
            $scoreSheetNumber2,
            $categoryLevelCombination,
            $gymnasts2
        );
        $numberOfSpotsAvailable      = 4;

        $result = DoorstromingListCreator::create(
            $firstCompetitionScoreSheet,
            $secondCompetitionScoreSheet,
            $numberOfSpotsAvailable,
            1,
            1
        );

        $this->assertInstanceOf(DoorstromingList::class, $result);
    }
}
