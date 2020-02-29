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

    private int $numberOfSpotsAvailable;

    public static function create(
        string $identifier,
        CategoryLevelCombination $categoryLevelCombination,
        array $doorstromingEntries,
        int $totalNumberOfFullParticipatingGymnasts,
        int $numberOfSpotsAvailable
    ): self
    {
        Assertion::allIsInstanceOf($doorstromingEntries, DoorstromingEntry::class);

        $self                                         = new self();
        $self->identifier                             = $identifier;
        $self->categoryLevelCombination               = $categoryLevelCombination;
        $self->doorstromingEntries                    = $doorstromingEntries;
        $self->totalNumberOfFullParticipatingGymnasts = $totalNumberOfFullParticipatingGymnasts;
        $self->numberOfSpotsAvailable                 = $numberOfSpotsAvailable;

        $self->addRanking();

        return $self;
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

    public function addExtraSpotAvailable(int $number): void
    {
        $this->numberOfSpotsAvailable = $this->numberOfSpotsAvailable + $number;
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

    public function numberOfSpotsAvailabe(): int
    {
        return $this->numberOfSpotsAvailabe;
    }

    private function __construct()
    {
    }
}
