<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

use Assert\Assertion;

final class CategoryLevelCombination
{
    private Category $category;
    private Level $level;

    public static function create(Category $category, Level $level): self
    {
        Assertion::choice($level->toString(), Level::getAvailableLevelsForCategoryAsString($category));

        $combination           = new self();
        $combination->category = $category;
        $combination->level    = $level;

        return $combination;
    }

    public function compare(CategoryLevelCombination $other): int
    {
        if ($this->category->compare($other->category) !== 0) {
            return $this->category->compare($other->category);
        }

        return ($this->level->compare($other->level));
    }

    public function equals(CategoryLevelCombination $other): bool
    {
        return ($this->category->equals($other->category) && $this->level->equals($other->level));
    }

    public function category(): Category
    {
        return $this->category;
    }

    public function level(): Level
    {
        return $this->level;
    }

    private function __construct()
    {
    }
}
