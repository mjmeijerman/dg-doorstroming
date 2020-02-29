<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

use Assert\Assertion;

final class ScoreSheets
{
    /**
     * @var ScoreSheet[]
     */
    private array $scoreSheets;

    public static function create(array $scoreSheets): self
    {
        Assertion::allIsInstanceOf($scoreSheets, ScoreSheet::class);

        $self              = new self();
        $self->scoreSheets = $scoreSheets;

        return $self;
    }

    public function getCategoryLevelCombinations(): CategoryLevelCombinations
    {
        $categoryLevelCombinations = CategoryLevelCombinations::create([]);
        foreach ($this->scoreSheets as $scoreSheet) {
            $categoryLevelCombinations->push(
                $scoreSheet->categoryLevelCombination()
            );
        }

        return $categoryLevelCombinations;
    }

    private function __construct()
    {
    }
}
