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
            3
        );

        $entry2 = DoorstromingList::create(
            'Identifier',
            CategoryLevelCombination::create(
                Category::PUPIL2(),
                Level::N2()
            ),
            [],
            18,
            3
        );

        $entry3 = DoorstromingList::create(
            'Identifier',
            CategoryLevelCombination::create(
                Category::PUPIL2(),
                Level::N2()
            ),
            [],
            13,
            3
        );

        $entry4 = DoorstromingList::create(
            'Identifier',
            CategoryLevelCombination::create(
                Category::PUPIL2(),
                Level::N2()
            ),
            [],
            15,
            3
        );

        $entry5 = DoorstromingList::create(
            'Identifier',
            CategoryLevelCombination::create(
                Category::PUPIL2(),
                Level::N2()
            ),
            [],
            12,
            3
        );

        $entry6 = DoorstromingList::create(
            'Identifier',
            CategoryLevelCombination::create(
                Category::PUPIL2(),
                Level::N2()
            ),
            [],
            20,
            3
        );

        $categoryLevelSpecificDoostromingLists = CategoryLevelSpecificDoorstromingLists::create(
            CategoryLevelCombination::create(
                Category::PUPIL2(),
                Level::N2()
            ),
            [$entry1, $entry2, $entry3, $entry4, $entry5, $entry6],
            5
        );

        $expectedList = [$entry6, $entry2, $entry1, $entry4, $entry3, $entry5];
        $this->assertSame($expectedList, $categoryLevelSpecificDoostromingLists->doorstromingLists());
        $this->assertSame(1, $entry6->rank());
        $this->assertSame(2, $entry2->rank());
        $this->assertSame(3, $entry1->rank());
        $this->assertSame(3, $entry4->rank());
        $this->assertSame(5, $entry3->rank());
        $this->assertSame(6, $entry5->rank());

        $this->assertSame(4, $entry6->numberOfSpotsAvailable());
        $this->assertSame(4, $entry2->numberOfSpotsAvailable());
        $this->assertSame(4, $entry1->numberOfSpotsAvailable());
        $this->assertSame(4, $entry4->numberOfSpotsAvailable());
        $this->assertSame(4, $entry3->numberOfSpotsAvailable());
        $this->assertSame(3, $entry5->numberOfSpotsAvailable());

        $this->assertSame(0, $categoryLevelSpecificDoostromingLists->numberOfExtraSpotsAvailable());
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
            3
        );

        $entry2 = DoorstromingList::create(
            'Identifier',
            CategoryLevelCombination::create(
                Category::PUPIL2(),
                Level::N2()
            ),
            [],
            18,
            3
        );

        $entry3 = DoorstromingList::create(
            'Identifier',
            CategoryLevelCombination::create(
                Category::PUPIL2(),
                Level::N2()
            ),
            [],
            13,
            3
        );

        $entry4 = DoorstromingList::create(
            'Identifier',
            CategoryLevelCombination::create(
                Category::PUPIL2(),
                Level::N2()
            ),
            [],
            15,
            3
        );

        $entry5 = DoorstromingList::create(
            'Identifier',
            CategoryLevelCombination::create(
                Category::PUPIL2(),
                Level::N2()
            ),
            [],
            12,
            3
        );

        $entry6 = DoorstromingList::create(
            'Identifier',
            CategoryLevelCombination::create(
                Category::PUPIL2(),
                Level::N2()
            ),
            [],
            20,
            3
        );

        $categoryLevelSpecificDoostromingLists = CategoryLevelSpecificDoorstromingLists::create(
            CategoryLevelCombination::create(
                Category::PUPIL2(),
                Level::N2()
            ),
            [$entry1, $entry2, $entry3, $entry4, $entry5, $entry6],
            3
        );

        $this->assertSame(4, $entry6->numberOfSpotsAvailable());
        $this->assertSame(4, $entry2->numberOfSpotsAvailable());
        $this->assertSame(3, $entry1->numberOfSpotsAvailable());
        $this->assertSame(3, $entry4->numberOfSpotsAvailable());
        $this->assertSame(3, $entry3->numberOfSpotsAvailable());
        $this->assertSame(3, $entry5->numberOfSpotsAvailable());

        $this->assertSame(1, $categoryLevelSpecificDoostromingLists->numberOfExtraSpotsAvailable());

    }
}
