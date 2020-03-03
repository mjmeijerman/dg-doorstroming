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

    private int $numberOfDistrictExtraSpots;

    private int $numberOfNationalExtraSpots;

    public static function create(
        CategoryLevelCombination $categoryLevelCombination,
        array $doorstromingLists,
        int $numberOfDistrictExtraSpots,
        int $numberOfNationalExtraSpots
    ): self
    {
        Assertion::allIsInstanceOf($doorstromingLists, DoorstromingList::class);

        $self                             = new self();
        $self->categoryLevelCombination   = $categoryLevelCombination;
        $self->doorstromingLists          = $doorstromingLists;
        $self->numberOfDistrictExtraSpots = $numberOfDistrictExtraSpots;
        $self->numberOfNationalExtraSpots = $numberOfNationalExtraSpots;
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
        if ($this->numberOfDistrictExtraSpots === 0) {
            return;
        }

        $rank = 1;
        while (true) {
            $results = $this->findByRank($rank);
            if (count($results) === 0) {
                break;
            }

            if (count($results) > $this->numberOfDistrictExtraSpots) {
                break;
            }

            foreach ($results as $doorstromingList) {
                $this->subtractFromNumberOfDistrictExtraSpotsAvailable(1);
                $doorstromingList->addDistrictExtraSpotAvailable(1);
                $rank++;
            }

            if ($this->numberOfDistrictExtraSpots === 0) {
                break;
            }
        }

        $rank = 1;
        while (true) {
            $results = $this->findByRank($rank);
            if (count($results) === 0) {
                break;
            }

            if (count($results) > $this->numberOfNationalExtraSpots) {
                break;
            }

            foreach ($results as $doorstromingList) {
                $this->subtractFromNumberOfNationalExtraSpotsAvailable(1);
                $doorstromingList->addNationalExtraSpotAvailable(1);
                $rank++;
            }

            if ($this->numberOfNationalExtraSpots === 0) {
                break;
            }
        }
    }

    private function addRankingToDoorstromingLists(): void
    {
        $this->sortDoorstromingLists();

        $previousList = null;
        $rank         = 1;
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

    public function sortByIdentifier(): void
    {
        usort(
            $this->doorstromingLists,
            function (DoorstromingList $firstList, DoorstromingList $otherList) {
                return strcmp($firstList->identifier(), $otherList->identifier());
            }
        );
    }

    private function subtractFromNumberOfDistrictExtraSpotsAvailable(int $number): void
    {
        if ($number > $this->numberOfDistrictExtraSpots) {
            throw new LogicException(
                sprintf(
                    'Trying to subtract "%d" spots, but only "%d" extra spots are available"',
                    $number,
                    $this->numberOfDistrictExtraSpots
                )
            );
        }

        $this->numberOfDistrictExtraSpots = $this->numberOfDistrictExtraSpots - $number;
    }

    private function subtractFromNumberOfNationalExtraSpotsAvailable(int $number): void
    {
        if ($number > $this->numberOfDistrictExtraSpots) {
            throw new LogicException(
                sprintf(
                    'Trying to subtract "%d" spots, but only "%d" extra spots are available"',
                    $number,
                    $this->numberOfDistrictExtraSpots
                )
            );
        }

        $this->numberOfDistrictExtraSpots = $this->numberOfDistrictExtraSpots - $number;
    }

    public function categoryLevelCombination(): CategoryLevelCombination
    {
        return $this->categoryLevelCombination;
    }

    public function doorstromingLists(): array
    {
        return $this->doorstromingLists;
    }

    public function numberOfDistrictExtraSpots(): int
    {
        return $this->numberOfDistrictExtraSpots;
    }

    public function numberOfNationalExtraSpots(): int
    {
        return $this->numberOfNationalExtraSpots;
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
