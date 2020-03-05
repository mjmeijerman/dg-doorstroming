<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

use PHPUnit\Framework\TestCase;

class DoorstromingListTest extends TestCase
{
    /**
     * @test
     */
    public function itCreatesASortedAndRankedDoorstromingList()
    {
        $entry1 = DoorstromingEntry::create(
            'Name 1',
            'Club 1',
            1,
            18,
            47.15,
            14.08,
            48.15,
            15.08
        );

        $entry2 = DoorstromingEntry::create(
            'Name 2',
            'Club 2',
            2,
            18,
            47.15,
            14.08,
            48.15,
            15.08
        );

        $entry3 = DoorstromingEntry::create(
            'Name 3',
            'Club 3',
            19,
            2,
            47.15,
            14.08,
            48.15,
            15.08
        );

        $entry4 = DoorstromingEntry::create(
            'Name 4',
            'Club 4',
            3,
            12,
            47.15,
            14.08,
            49.15,
            15.08
        );

        $entry5 = DoorstromingEntry::create(
            'Name 5',
            'Club 5',
            12,
            3,
            47.15,
            14.08,
            48.15,
            15.08
        );

        $entry6 = DoorstromingEntry::create(
            'Name 6',
            'Club 6',
            4,
            12,
            47.15,
            14.08,
            48.15,
            16.08
        );

        $entry7 = DoorstromingEntry::create(
            'Name 7',
            'Club 7',
            12,
            4,
            47.15,
            14.08,
            48.15,
            15.08
        );

        $entry8 = DoorstromingEntry::create(
            'Name 8',
            'Club 8',
            5,
            12,
            47.15,
            14.08,
            48.15,
            15.08
        );

        $entry9 = DoorstromingEntry::create(
            'Name 9',
            'Club 9',
            12,
            5,
            47.15,
            14.08,
            48.15,
            15.08
        );

        $entry10 = DoorstromingEntry::create(
            'Name 10',
            'Club 10',
            12,
            6,
            47.15,
            14.08,
            48.15,
            15.08
        );

        $doorstromingList = DoorstromingList::create(
            'Instap N3 groep A',
            CategoryLevelCombination::create(
                Category::INSTAP(),
                Level::N3()
            ),
            [$entry5, $entry1, $entry3, $entry9, $entry7, $entry10, $entry2, $entry8, $entry4, $entry6],
            4,
            4,
            5,
            0,
            0
        );

        $expectedList = [$entry1, $entry2, $entry3, $entry4, $entry5, $entry6, $entry7, $entry9, $entry8, $entry10];
        $this->assertSame($expectedList, $doorstromingList->doorstromingEntries());
        $this->assertSame(1, $entry1->totalRank());
        $this->assertSame(2, $entry2->totalRank());
        $this->assertSame(3, $entry3->totalRank());
        $this->assertSame(4, $entry4->totalRank());
        $this->assertSame(5, $entry5->totalRank());
        $this->assertSame(6, $entry6->totalRank());
        $this->assertSame(7, $entry7->totalRank());
        $this->assertSame(8, $entry8->totalRank());
        $this->assertSame(8, $entry9->totalRank());
        $this->assertSame(10, $entry10->totalRank());

        $this->assertSame('Instap N3 groep A', $doorstromingList->identifier());
    }
}
