<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

use Assert\Assertion;

final class CategoryLevelCombinations
{
    /**
     * @var CategoryLevelCombination[]
     */
    private array $categoryLevelCombinations;

    public static function create(array $categoryLevelCombinations): self
    {
        Assertion::allIsInstanceOf($categoryLevelCombinations, CategoryLevelCombination::class);

        $self                            = new self();
        $self->categoryLevelCombinations = $categoryLevelCombinations;
        $self->sortList();

        return $self;
    }

    public function sortList(): void
    {
        usort(
            $this->categoryLevelCombinations,
            function (CategoryLevelCombination $firstCombination, CategoryLevelCombination $secondCombination) {
                return $firstCombination->compare($secondCombination);
            }
        );
    }

    public function categoryLevelCombinations(): array
    {
        return $this->categoryLevelCombinations;
    }

    public function push(CategoryLevelCombination $categoryLevelCombination): void
    {
        if ($this->contains($categoryLevelCombination)) {
            $this->categoryLevelCombinations[] = $categoryLevelCombination;
            $this->sortList();
        }
    }

    private function contains(CategoryLevelCombination $otherCategoryLevelCombination): bool
    {
        foreach ($this->categoryLevelCombinations as $categoryLevelCombination) {
            if ($categoryLevelCombination->category()->equals($otherCategoryLevelCombination->category())
                && $categoryLevelCombination->level()->equals($otherCategoryLevelCombination->level())) {
                return true;
            }
        }

        return false;
    }

    private function __construct()
    {
    }
}
