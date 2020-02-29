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

    public static function create(CategoryLevelCombination $categoryLevelCombination, array $doorstromingLists): self
    {
        Assertion::allIsInstanceOf($doorstromingLists, DoorstromingList::class);

        $self                           = new self();
        $self->categoryLevelCombination = $categoryLevelCombination;
        $self->doorstromingLists        = $doorstromingLists;
        $self->protect();

        return $self;
    }

    public function getCategoryLevelCombinations(): CategoryLevelCombinations
    {
        $categoryLevelCombinations = CategoryLevelCombinations::create([]);
        foreach ($this->doorstromingLists as $doorstromingList) {
            $categoryLevelCombinations->push(
                $doorstromingList->categoryLevelCombination()
            );
        }

        return $categoryLevelCombinations;
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
