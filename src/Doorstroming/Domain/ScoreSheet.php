<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

use Assert\Assertion;

final class ScoreSheet
{
    private string $identifier;

    private int $scoreSheetNumber;

    private CategoryLevelCombination $categoryLevelCombination;

    /**
     * @var Gymnast[]
     */
    private array $gymnasts;

    public static function create(
        string $identifier,
        int $scoreSheetNumber,
        CategoryLevelCombination $categoryLevelCombination,
        array $gymnasts
    ): self
    {
        Assertion::allIsInstanceOf($gymnasts, Gymnast::class);

        $self                           = new self();
        $self->identifier               = $identifier;
        $self->scoreSheetNumber         = $scoreSheetNumber;
        $self->categoryLevelCombination = $categoryLevelCombination;
        $self->gymnasts                 = $gymnasts;

        $self->addRanking();

        return $self;
    }

    public function findGymnast(GymnastId $gymnastId): ?Gymnast
    {
        foreach ($this->gymnasts as $gymnast) {
            if ($gymnast->gymnastId()->equals($gymnastId)) {
                return $gymnast;
            }
        }

        return null;
    }

    private function sortGymnasts(): void
    {
        usort(
            $this->gymnasts,
            function (Gymnast $firstGymnast, Gymnast $otherGymnast) {
                return $firstGymnast->compare($otherGymnast);
            }
        );
    }

    public function addRanking(): void
    {
        $this->sortGymnasts();

        $previousGymnast = null;
        $rank            = 1;
        foreach ($this->gymnasts as $gymnast) {
            if (!$previousGymnast) {
                $gymnast->updateRanking($rank);
                $rank++;
                $previousGymnast = $gymnast;

                continue;
            }

            if ($gymnast->compare($previousGymnast) === 0) {
                $gymnast->updateRanking($previousGymnast->rank());
                $rank++;
                $previousGymnast = $gymnast;

                continue;
            }

            $gymnast->updateRanking($rank);
            $rank++;
            $previousGymnast = $gymnast;
        }
    }

    public function pushGymnast(Gymnast $gymnast): void
    {
        $existingGymnast = $this->findGymnast($gymnast->gymnastId());
        if ($existingGymnast) {
            return;
        }

        $this->gymnasts[] = $gymnast;
    }

    public function totalNumberOfFullParticipatedGymnasts(): int
    {
        $total = 0;
        foreach ($this->gymnasts as $gymnast) {
            if ($gymnast->participatedEntireCompetition()) {
                $total++;
            }
        }

        return $total;
    }

    public function identifier(): string
    {
        return $this->identifier;
    }

    public function scoreSheetNumber(): int
    {
        return $this->scoreSheetNumber;
    }

    public function categoryLevelCombination(): CategoryLevelCombination
    {
        return $this->categoryLevelCombination;
    }

    /**
     * @return Gymnast[]
     */
    public function gymnasts(): array
    {
        return $this->gymnasts;
    }

    private function __construct()
    {
    }
}
