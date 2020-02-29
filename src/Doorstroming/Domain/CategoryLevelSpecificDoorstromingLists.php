<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

use Assert\Assertion;

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

    private function divideExtraSpotsAvailable(): void
    {
        $this->sortByTotalNumberOfFullParticipatingGymnasts();
        if ($this->numberOfExtraSpotsAvailable === 0) {
            return;
        }
        foreach ($this->doorstromingLists as $doorstromingList) {
            if ($this->numberOfExtraSpotsAvailable === 0) {
                return;
            }

            $this->subtractFromNumberOfExtraSpotsAvailable(1);
            $doorstromingList->addExtraSpotAvailable(1);
        }
    }

    private function sortByTotalNumberOfFullParticipatingGymnasts(): void
    {
        usort(
            $this->doorstromingLists,
            function (DoorstromingList $firstList, DoorstromingList $otherList) {
                if ($firstList->totalNumberOfFullParticipatingGymnasts()
                    === $otherList->totalNumberOfFullParticipatingGymnasts()) {
                    return 0;
                }

                return (
                    $firstList->totalNumberOfFullParticipatingGymnasts()
                    > $otherList->totalNumberOfFullParticipatingGymnasts()
                ) ? -1 : 1;
            }
        );
    }

    private function subtractFromNumberOfExtraSpotsAvailable(int $number): void
    {
        if ($number > $this->numberOfExtraSpotsAvailable) {
            throw new \LogicException(
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

    private function protect(): void
    {
        foreach ($this->doorstromingLists as $doorstromingList) {
            if (!$doorstromingList->categoryLevelCombination()->equals($this->categoryLevelCombination)) {
                throw new \LogicException(
                    'Invalid category level combination found while creating category level specific lists'
                );
            }
        }
    }

    private function __construct()
    {
    }
}
