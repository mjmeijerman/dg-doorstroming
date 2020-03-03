<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

use PHPUnit\Framework\TestCase;

class CategoryLevelSpecificDoorstromingListsTest extends TestCase
{
    /**
     * @test
     */
    public function itCreatesSortedCategoryLevelSpecificDoorstromingListsAndAddsRankingAndDividesExtraSpots()
    {
        $entry1 = DoorstromingList::create(
            'Identifier',
            CategoryLevelCombination::create(
                Category::PUPIL2(),
                Level::N2()
            ),
            [],
            15,
            3,
            0,
            0
        );

        $entry2 = DoorstromingList::create(
            'Identifier',
            CategoryLevelCombination::create(
                Category::PUPIL2(),
                Level::N2()
            ),
            [],
            18,
            3,
            0,
            0
        );

        $entry3 = DoorstromingList::create(
            'Identifier',
            CategoryLevelCombination::create(
                Category::PUPIL2(),
                Level::N2()
            ),
            [],
            13,
            3,
            0,
            0
        );

        $entry4 = DoorstromingList::create(
            'Identifier',
            CategoryLevelCombination::create(
                Category::PUPIL2(),
                Level::N2()
            ),
            [],
            15,
            3,
            0,
            0
        );

        $entry5 = DoorstromingList::create(
            'Identifier',
            CategoryLevelCombination::create(
                Category::PUPIL2(),
                Level::N2()
            ),
            [],
            12,
            3,
            0,
            0
        );

        $entry6 = DoorstromingList::create(
            'Identifier',
            CategoryLevelCombination::create(
                Category::PUPIL2(),
                Level::N2()
            ),
            [],
            20,
            3,
            0,
            0
        );

        $categoryLevelSpecificDoostromingLists = CategoryLevelSpecificDoorstromingLists::create(
            CategoryLevelCombination::create(
                Category::PUPIL2(),
                Level::N2()
            ),
            [$entry1, $entry2, $entry3, $entry4, $entry5, $entry6],
            5,
            0
        );

        $expectedList = [$entry6, $entry2, $entry1, $entry4, $entry3, $entry5];
        $this->assertSame($expectedList, $categoryLevelSpecificDoostromingLists->doorstromingLists());
        $this->assertSame(1, $entry6->rank());
        $this->assertSame(2, $entry2->rank());
        $this->assertSame(3, $entry1->rank());
        $this->assertSame(3, $entry4->rank());
        $this->assertSame(5, $entry3->rank());
        $this->assertSame(6, $entry5->rank());

        $this->assertSame(4, $entry6->numberOfAvailableSpots(CompetitionType::DISTRICT()));
        $this->assertSame(4, $entry2->numberOfAvailableSpots(CompetitionType::DISTRICT()));
        $this->assertSame(4, $entry1->numberOfAvailableSpots(CompetitionType::DISTRICT()));
        $this->assertSame(4, $entry4->numberOfAvailableSpots(CompetitionType::DISTRICT()));
        $this->assertSame(4, $entry3->numberOfAvailableSpots(CompetitionType::DISTRICT()));
        $this->assertSame(3, $entry5->numberOfAvailableSpots(CompetitionType::DISTRICT()));

        $this->assertSame(0, $categoryLevelSpecificDoostromingLists->numberOfExtraSpots(CompetitionType::DISTRICT()));
    }

    /**
     * @test
     */
    public function itCreatesSortedCategoryLevelSpecificDoorstromingListsAndAddsRankingAndDoesNotKnowWhatToDoWithExtraSpots()
    {
        $entry1 = DoorstromingList::create(
            'Identifier',
            CategoryLevelCombination::create(
                Category::PUPIL2(),
                Level::N2()
            ),
            [],
            15,
            3,
            0,
            0
        );

        $entry2 = DoorstromingList::create(
            'Identifier',
            CategoryLevelCombination::create(
                Category::PUPIL2(),
                Level::N2()
            ),
            [],
            18,
            3,
            0,
            0
        );

        $entry3 = DoorstromingList::create(
            'Identifier',
            CategoryLevelCombination::create(
                Category::PUPIL2(),
                Level::N2()
            ),
            [],
            13,
            3,
            0,
            0
        );

        $entry4 = DoorstromingList::create(
            'Identifier',
            CategoryLevelCombination::create(
                Category::PUPIL2(),
                Level::N2()
            ),
            [],
            15,
            3,
            0,
            0
        );

        $entry5 = DoorstromingList::create(
            'Identifier',
            CategoryLevelCombination::create(
                Category::PUPIL2(),
                Level::N2()
            ),
            [],
            12,
            3,
            0,
            0
        );

        $entry6 = DoorstromingList::create(
            'Identifier',
            CategoryLevelCombination::create(
                Category::PUPIL2(),
                Level::N2()
            ),
            [],
            20,
            3,
            0,
            0
        );

        $categoryLevelSpecificDoostromingLists = CategoryLevelSpecificDoorstromingLists::create(
            CategoryLevelCombination::create(
                Category::PUPIL2(),
                Level::N2()
            ),
            [$entry1, $entry2, $entry3, $entry4, $entry5, $entry6],
            3,
            0
        );

        $this->assertSame(4, $entry6->numberOfAvailableSpots(CompetitionType::DISTRICT()));
        $this->assertSame(4, $entry2->numberOfAvailableSpots(CompetitionType::DISTRICT()));
        $this->assertSame(3, $entry1->numberOfAvailableSpots(CompetitionType::DISTRICT()));
        $this->assertSame(3, $entry4->numberOfAvailableSpots(CompetitionType::DISTRICT()));
        $this->assertSame(3, $entry3->numberOfAvailableSpots(CompetitionType::DISTRICT()));
        $this->assertSame(3, $entry5->numberOfAvailableSpots(CompetitionType::DISTRICT()));
        $this->assertTrue(
            $categoryLevelSpecificDoostromingLists->categoryLevelCombination()->equals(
                CategoryLevelCombination::create(
                    Category::PUPIL2(),
                    Level::N2()
                )
            )
        );

        $this->assertSame(1, $categoryLevelSpecificDoostromingLists->numberOfExtraSpots(CompetitionType::DISTRICT()));
    }

    /**
     * @test
     */
    public function itCreatesSortedCategoryLevelSpecificDoorstromingListsAndAddsRankingWithoutExtraSpots()
    {
        $entry1 = DoorstromingList::create(
            'Identifier',
            CategoryLevelCombination::create(
                Category::PUPIL2(),
                Level::N2()
            ),
            [],
            15,
            3,
            0,
            0
        );

        CategoryLevelSpecificDoorstromingLists::create(
            CategoryLevelCombination::create(
                Category::PUPIL2(),
                Level::N2()
            ),
            [$entry1],
            0,
            0
        );

        $this->assertSame(3, $entry1->numberOfAvailableSpots(CompetitionType::DISTRICT()));
    }

    /**
     * @test
     */
    public function itCreatesSortedCategoryLevelSpecificDoorstromingListsAndReturnsWhenExtraSpotsCouldNotBeAdded()
    {
        $categoryLevelSpecificDoostromingLists = CategoryLevelSpecificDoorstromingLists::create(
            CategoryLevelCombination::create(
                Category::PUPIL2(),
                Level::N2()
            ),
            [],
            1,
            0
        );

        $this->assertSame(1, $categoryLevelSpecificDoostromingLists->numberOfExtraSpots(CompetitionType::DISTRICT()));
    }

    /**
     * @test
     */
    public function itIsNotCreateWithDifferentCategoryLevelCombinations()
    {
        $entry1 = DoorstromingList::create(
            'Identifier',
            CategoryLevelCombination::create(
                Category::PUPIL2(),
                Level::N2()
            ),
            [],
            15,
            3,
            0,
            0
        );

        $entry2 = DoorstromingList::create(
            'Identifier',
            CategoryLevelCombination::create(
                Category::PUPIL1(),
                Level::N2()
            ),
            [],
            18,
            3,
            0,
            0
        );

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(
            'Invalid category level combination found while creating category level specific lists'
        );

        CategoryLevelSpecificDoorstromingLists::create(
            CategoryLevelCombination::create(
                Category::PUPIL2(),
                Level::N2()
            ),
            [$entry1, $entry2],
            3,
            0
        );
    }
}
