<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

use PHPUnit_Framework_TestCase;

class DoorstromingEntryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itComparesTwoEntriesWithDifferentMaxRankings()
    {
        $first = DoorstromingEntry::create(
            'name 1',
            'club 1',
            1,
            2,
            45.03,
            14.08,
            47.93,
            15.01
        );

        $second = DoorstromingEntry::create(
            'name 2',
            'club 2',
            4,
            2,
            45.03,
            14.08,
            47.93,
            15.01
        );

        $this->assertSame(-1, $first->compare($second));
    }

    /**
     * @test
     */
    public function itComparesTwoEntriesWithDifferentAverageRankings()
    {
        $first = DoorstromingEntry::create(
            'name 1',
            'club 1',
            1,
            2,
            45.03,
            14.08,
            47.93,
            15.01
        );

        $second = DoorstromingEntry::create(
            'name 2',
            'club 2',
            4,
            1,
            45.03,
            14.08,
            47.93,
            15.01
        );

        $this->assertSame(-1, $first->compare($second));
    }

    /**
     * @test
     */
    public function itComparesTwoEntriesWithDifferentTotalScores()
    {
        $first = DoorstromingEntry::create(
            'name 1',
            'club 1',
            1,
            2,
            45.03,
            14.08,
            47.93,
            15.01
        );

        $second = DoorstromingEntry::create(
            'name 2',
            'club 2',
            2,
            1,
            44.03,
            14.08,
            47.93,
            15.01
        );

        $this->assertSame(-1, $first->compare($second));
    }

    /**
     * @test
     */
    public function itComparesTwoEntriesWithDifferentEScores()
    {
        $first = DoorstromingEntry::create(
            'name 1',
            'club 1',
            1,
            2,
            45.03,
            14.08,
            47.93,
            15.01
        );

        $second = DoorstromingEntry::create(
            'name 2',
            'club 2',
            2,
            1,
            45.03,
            14.08,
            47.93,
            14.01
        );

        $this->assertSame(-1, $first->compare($second));
    }

    /**
     * @test
     */
    public function itComparesTwoEntriesWithThatAreEqual()
    {
        $first = DoorstromingEntry::create(
            'name 1',
            'club 1',
            1,
            2,
            45.03,
            14.08,
            47.93,
            15.01
        );

        $second = DoorstromingEntry::create(
            'name 2',
            'club 2',
            2,
            1,
            45.03,
            14.08,
            47.93,
            15.01
        );

        $this->assertSame(0, $first->compare($second));
    }

    /**
     * @test
     */
    public function itReturnsTheBestRank()
    {
        $first = DoorstromingEntry::create(
            'name 1',
            'club 1',
            1,
            20,
            45.03,
            14.08,
            47.93,
            15.01
        );

        $second = DoorstromingEntry::create(
            'name 2',
            'club 2',
            1,
            1,
            45.03,
            14.08,
            47.93,
            15.01
        );

        $this->assertSame(1, $first->bestRank());
        $this->assertSame(1, $second->bestRank());
    }

    /**
     * @test
     */
    public function itReturnsTheAverageRank()
    {
        $first = DoorstromingEntry::create(
            'name 1',
            'club 1',
            1,
            20,
            45.03,
            14.08,
            47.93,
            15.01
        );

        $second = DoorstromingEntry::create(
            'name 2',
            'club 2',
            1,
            1,
            45.03,
            14.08,
            47.93,
            15.01
        );

        $this->assertSame(10.5, $first->averageRank());
        $this->assertSame(1.0, $second->averageRank());
    }

    /**
     * @test
     */
    public function itReturnsTheTotalScore()
    {
        $first = DoorstromingEntry::create(
            'name 1',
            'club 1',
            1,
            20,
            45.03,
            14.08,
            47.93,
            15.01
        );

        $this->assertSame(92.96, $first->totalScore());
    }

    /**
     * @test
     */
    public function itReturnsTheTotalEScore()
    {
        $first = DoorstromingEntry::create(
            'name 1',
            'club 1',
            1,
            20,
            45.03,
            14.08,
            47.93,
            15.01
        );

        $this->assertSame(29.09, $first->totalEScore());
    }
}
