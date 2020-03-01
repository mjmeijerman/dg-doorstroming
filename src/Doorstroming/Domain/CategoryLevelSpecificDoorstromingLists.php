<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

use Assert\Assertion;
use LogicException;

final class CategoryLevelSpecificDoorstromingLists
{
    private CategoryLevelCombination $categoryLevelCombination;

    /**
     * @var DoorstromingList[]
     */
    private array $doorstromingLists;

    private int $numberOfExtraSpotsAvailable;

    public static function create(CategoryLevelCombination $categoryLevelCombination, array $doorstromingLists, int $numberOfExtraSpotsAvailable): self
    {
        Assertion::allIsInstanceOf($doorstromingLists, DoorstromingList::class);

        $self                              = new self();
        $self->categoryLevelCombination    = $categoryLevelCombination;
        $self->doorstromingLists           = $doorstromingLists;
        $self->numberOfExtraSpotsAvailable = $numberOfExtraSpotsAvailable;
        $self->protect();
        $self->divideExtraSpotsAvailable();

        return $self;
    }

    /**
     * @param int $rank
     *
     * @return DoorstromingList[]
     */
    private function findByRank(int $rank): array
    {
        $filteredList = [];
        foreach ($this->doorstromingLists as $doorstromingList) {
            if ($doorstromingList->rank() === $rank) {
                $filteredList[] = $doorstromingList;
            }
        }

        return $filteredList;
    }

    private function divideExtraSpotsAvailable(): void
    {
        $this->addRankingToDoorstromingLists();
        if ($this->numberOfExtraSpotsAvailable === 0) {
            return;
        }

        $rank = 1;
        while (true) {
            $results = $this->findByRank($rank);
            if (count($results) === 0) {
                break;
            }

            if (count($results) > $this->numberOfExtraSpotsAvailable) {
                break;
            }

            foreach ($results as $doorstromingList) {
                $this->subtractFromNumberOfExtraSpotsAvailable(1);
                $doorstromingList->addExtraSpotAvailable(1);
                $rank++;
            }

            if ($this->numberOfExtraSpotsAvailable === 0) {
                break;
            }
        }
    }

    private function addRankingToDoorstromingLists(): void
    {
        $this->sortDoorstromingLists();

        $previousList = null;
        $rank          = 1;
        foreach ($this->doorstromingLists as $doorstromingList) {
            if (!$previousList) {
                $doorstromingList->updateRank($rank);
                $rank++;
                $previousList = $doorstromingList;

                continue;
            }

            if ($doorstromingList->compare($previousList) === 0) {
                $doorstromingList->updateRank($previousList->rank());
                $rank++;
                $previousList = $doorstromingList;

                continue;
            }

            $doorstromingList->updateRank($rank);
            $rank++;
            $previousList = $doorstromingList;
        }
    }

    private function sortDoorstromingLists(): void
    {
        usort(
            $this->doorstromingLists,
            function (DoorstromingList $firstList, DoorstromingList $otherList) {
                return $firstList->compare($otherList);
            }
        );
    }

    private function subtractFromNumberOfExtraSpotsAvailable(int $number): void
    {
        if ($number > $this->numberOfExtraSpotsAvailable) {
            throw new LogicException(
                sprintf(
                    'Trying to subtract "%d" spots, but only "%d" extra spots are available"',
                    $number,
                    $this->numberOfExtraSpotsAvailable
                )
            );
        }

        $this->numberOfExtraSpotsAvailable = $this->numberOfExtraSpotsAvailable - $number;
    }

    public function categoryLevelCombination(): CategoryLevelCombination
    {
        return $this->categoryLevelCombination;
    }

    public function doorstromingLists(): array
    {
        return $this->doorstromingLists;
    }

    public function numberOfExtraSpotsAvailable(): int
    {
        return $this->numberOfExtraSpotsAvailable;
    }

    private function protect(): void
    {
        foreach ($this->doorstromingLists as $doorstromingList) {
            if (!$doorstromingList->categoryLevelCombination()->equals($this->categoryLevelCombination)) {
                throw new LogicException(
                    'Invalid category level combination found while creating category level specific lists'
                );
            }
        }
    }

    private function __construct()
    {
    }
}
