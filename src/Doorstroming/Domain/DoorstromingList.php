<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

use Assert\Assertion;

final class DoorstromingList
{
    private string $identifier;

    private Category $category;

    private Level $level;

    /**
     * @var DoorstromingEntry[]
     */
    private array $doorstromingEntries;

    public static function create(
        string $identifier,
        Category $category,
        Level $level,
        array $doorstromingEntries
    ): self
    {
        Assertion::allIsInstanceOf($doorstromingEntries, DoorstromingEntry::class);

        $self                      = new self();
        $self->identifier          = $identifier;
        $self->category            = $category;
        $self->level               = $level;
        $self->doorstromingEntries = $doorstromingEntries;

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

    public function category(): Category
    {
        return $this->category;
    }

    public function level(): Level
    {
        return $this->level;
    }

    public function doorstromingEntries(): array
    {
        return $this->doorstromingEntries;
    }

    private function __construct()
    {
    }
}
