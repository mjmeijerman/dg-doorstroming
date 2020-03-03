<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

use Assert\Assertion;

final class DoorstromingList
{
    private string $identifier;

    private CategoryLevelCombination $categoryLevelCombination;

    /**
     * @var DoorstromingEntry[]
     */
    private array $doorstromingEntries;

    private int $totalNumberOfFullParticipatingGymnasts;

    private int $numberOfDistrictSpotsAvailable;

    private int $numberOfNationalSpotsAvailable;

    private int $numberOfDistrictReserveSpots;

    private int $rank;

    public static function create(
        string $identifier,
        CategoryLevelCombination $categoryLevelCombination,
        array $doorstromingEntries,
        int $totalNumberOfFullParticipatingGymnasts,
        int $numberOfDistrictSpotsAvailable,
        int $numberOfNationalSpotsAvailable,
        int $numberOfDistrictReserveSpots
    ): self
    {
        Assertion::allIsInstanceOf($doorstromingEntries, DoorstromingEntry::class);

        $self                                         = new self();
        $self->identifier                             = $identifier;
        $self->categoryLevelCombination               = $categoryLevelCombination;
        $self->doorstromingEntries                    = $doorstromingEntries;
        $self->totalNumberOfFullParticipatingGymnasts = $totalNumberOfFullParticipatingGymnasts;
        $self->numberOfDistrictSpotsAvailable         = $numberOfDistrictSpotsAvailable;
        $self->numberOfNationalSpotsAvailable         = $numberOfNationalSpotsAvailable;
        $self->numberOfDistrictReserveSpots           = $numberOfDistrictReserveSpots;

        $self->addRanking();

        return $self;
    }

    public function updateRank(int $rank): void
    {
        $this->rank = $rank;
    }

    public function compare(DoorstromingList $other): int
    {
        if ($this->totalNumberOfFullParticipatingGymnasts()
            === $other->totalNumberOfFullParticipatingGymnasts()) {
            return 0;
        }

        return (
            $this->totalNumberOfFullParticipatingGymnasts()
            > $other->totalNumberOfFullParticipatingGymnasts()
        ) ? -1 : 1;
    }

    private function sortEntries(): void
    {
        usort(
            $this->doorstromingEntries,
            function (DoorstromingEntry $firstEntry, DoorstromingEntry $otherEntry) {
                return $firstEntry->compare($otherEntry);
            }
        );
    }

    public function addDistrictExtraSpotAvailable(int $number): void
    {
        $this->numberOfDistrictSpotsAvailable = $this->numberOfDistrictSpotsAvailable + $number;
    }

    public function addNationalExtraSpotAvailable(int $number): void
    {
        $this->numberOfNationalSpotsAvailable = $this->numberOfNationalSpotsAvailable + $number;
    }

    private function addRanking(): void
    {
        $this->sortEntries();

        $previousEntry = null;
        $rank          = 1;
        foreach ($this->doorstromingEntries as $entry) {
            if (!$previousEntry) {
                $entry->updateTotalRanking($rank);
                $rank++;
                $previousEntry = $entry;

                continue;
            }

            if ($entry->compare($previousEntry) === 0) {
                $entry->updateTotalRanking($previousEntry->totalRank());
                $rank++;
                $previousEntry = $entry;

                continue;
            }

            $entry->updateTotalRanking($rank);
            $rank++;
            $previousEntry = $entry;
        }
    }

    public function identifier(): string
    {
        return $this->identifier;
    }

    public function categoryLevelCombination(): CategoryLevelCombination
    {
        return $this->categoryLevelCombination;
    }

    public function doorstromingEntries(): array
    {
        return $this->doorstromingEntries;
    }

    public function totalNumberOfFullParticipatingGymnasts(): int
    {
        return $this->totalNumberOfFullParticipatingGymnasts;
    }

    public function numberOfDistrictSpotsAvailable(): int
    {
        return $this->numberOfDistrictSpotsAvailable;
    }

    public function numberOfNationalSpotsAvailable(): int
    {
        return $this->numberOfNationalSpotsAvailable;
    }

    public function numberOfDistrictReserveSpots(): int
    {
        return $this->numberOfDistrictReserveSpots;
    }

    public function rank(): int
    {
        return $this->rank;
    }

    private function __construct()
    {
    }
}
