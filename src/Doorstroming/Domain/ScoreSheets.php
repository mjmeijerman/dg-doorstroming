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

    /**
     * @param CategoryLevelCombination $categoryLevelCombination
     *
     * @return ScoreSheet[]
     */
    public function findByCategoryLevelCombination(CategoryLevelCombination $categoryLevelCombination): array
    {
        $scoreSheets = [];
        foreach ($this->scoreSheets as $scoreSheet) {
            if ($scoreSheet->categoryLevelCombination()->equals($categoryLevelCombination)) {
                $scoreSheets[] = $scoreSheet;
            }
        }

        return $scoreSheets;
    }

    public function findByIdentifier(string $identifier): ?ScoreSheet
    {
        foreach ($this->scoreSheets as $scoreSheet) {
            if ($scoreSheet->identifier() === $identifier) {
                return $scoreSheet;
            }
        }

        return null;
    }

    public function getAllIdentifiers(): array
    {
        $identifiers = [];
        foreach ($this->scoreSheets as $scoreSheet) {
            $identifiers[] = $scoreSheet->identifier();
        }
        sort($identifiers);

        return $identifiers;
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

    /**
     * @return ScoreSheet[]
     */
    public function toArray(): array
    {
        return $this->scoreSheets;
    }

    private function __construct()
    {
    }
}
