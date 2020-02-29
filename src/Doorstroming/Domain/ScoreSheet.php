<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

use Assert\Assertion;

final class ScoreSheet
{
    private string $identifier;

    private int $scoreSheetNumber;

    private Category $category;

    private Level $level;

    /**
     * @var Gymnast[]
     */
    private array $gymnasts;

    public static function create(
        string $identifier,
        int $scoreSheetNumber,
        Category $category,
        Level $level,
        array $gymnasts
    ): self
    {
        Assertion::allIsInstanceOf($gymnasts, Gymnast::class);

        $self                   = new self();
        $self->identifier       = $identifier;
        $self->scoreSheetNumber = $scoreSheetNumber;
        $self->category         = $category;
        $self->level            = $level;
        $self->gymnasts         = $gymnasts;

        $self->addRanking();

        return $self;
    }

    public function findGymnast(GymnastId $gymnastId): Gymnast
    {
        foreach ($this->gymnasts as $gymnast) {
            if ($gymnast->gymnastId()->equals($gymnastId)) {
                return $gymnast;
            }
        }

        throw new \LogicException(
            sprintf('Gymnast with number "%s" was not found in second competition score sheet', $gymnastId->toInteger())
        );
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

    private function addRanking(): void
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

    public function identifier(): string
    {
        return $this->identifier;
    }

    public function scoreSheetNumber(): int
    {
        return $this->scoreSheetNumber;
    }

    public function category(): Category
    {
        return $this->category;
    }

    public function level(): Level
    {
        return $this->level;
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
