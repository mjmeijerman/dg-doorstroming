<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

use PHPUnit\Framework\TestCase;

class CategoryLevelCombinationsTest extends TestCase
{
    /**
     * @test
     */
    public function itCreatesSortedCategoryLevelCombinations()
    {
        $entry1 = CategoryLevelCombination::create(
            Category::PUPIL2(),
            Level::N3()
        );

        $entry2 = CategoryLevelCombination::create(
            Category::PUPIL2(),
            Level::N2()
        );

        $entry3 = CategoryLevelCombination::create(
            Category::INSTAP(),
            Level::N3()
        );

        $entry4 = CategoryLevelCombination::create(
            Category::JEUGD2(),
            Level::DIV4()
        );

        $entry5 = CategoryLevelCombination::create(
            Category::JUNIOR(),
            Level::DIV3()
        );

        $entry6 = CategoryLevelCombination::create(
            Category::JUNIOR(),
            Level::ERE()
        );

        $entry7 = CategoryLevelCombination::create(
            Category::MINI(),
            Level::N3()
        );

        $entry8 = CategoryLevelCombination::create(
            Category::VOORINSTAP(),
            Level::N1()
        );

        $entry9 = CategoryLevelCombination::create(
            Category::VOORINSTAP(),
            Level::N2()
        );

        $entry10 = CategoryLevelCombination::create(
            Category::JEUGD1(),
            Level::N3()
        );

        $categoryLevelCombinations = CategoryLevelCombinations::create(
            [$entry5, $entry1, $entry3, $entry9, $entry7, $entry10, $entry2, $entry8, $entry4, $entry6]
        );

        $expectedList = [$entry7, $entry8, $entry9, $entry3, $entry2, $entry1, $entry10, $entry4, $entry6, $entry5];
        $this->assertSame($expectedList, $categoryLevelCombinations->categoryLevelCombinations());

        $categoryLevelCombinations->push($entry4);
        $this->assertSame($expectedList, $categoryLevelCombinations->categoryLevelCombinations());

        $newEntry = CategoryLevelCombination::create(
            Category::PUPIL1(),
            Level::N3()
        );
        $newExpectedList = [$entry7, $entry8, $entry9, $entry3, $newEntry, $entry2, $entry1, $entry10, $entry4, $entry6, $entry5];
        $categoryLevelCombinations->push($newEntry);
        $this->assertSame($newExpectedList, $categoryLevelCombinations->categoryLevelCombinations());
    }
}
