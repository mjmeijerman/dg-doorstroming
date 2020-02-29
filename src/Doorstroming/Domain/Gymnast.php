<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

final class Gymnast
{
    private GymnastId $gymnastId;

    private string $name;

    private string $club;

    private float $totalScore;

    private float $eScore;

    private int $rank;

    private bool $participatedEntireCompetition;

    public static function create(
        GymnastId $gymnastId,
        string $name,
        string $club,
        float $totalScore,
        float $eScore,
        bool $participatedEntireCompetition
    ): self
    {
        $self                                = new self();
        $self->gymnastId                     = $gymnastId;
        $self->name                          = $name;
        $self->club                          = $club;
        $self->totalScore                    = $totalScore;
        $self->eScore                        = $eScore;
        $self->participatedEntireCompetition = $participatedEntireCompetition;

        return $self;
    }

    public function compare(Gymnast $other): int
    {
        $epsilon = 0.00001;
        if (abs($this->totalScore - $other->totalScore()) < $epsilon) {
            return 0;
        }

        return ($this->totalScore > $other->totalScore()) ? -1 : 1;
    }

    public function updateRanking(int $rank)
    {
        $this->rank = $rank;
    }

    public function gymnastId(): GymnastId
    {
        return $this->gymnastId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function club(): string
    {
        return $this->club;
    }

    public function totalScore(): float
    {
        return $this->totalScore;
    }

    public function eScore(): float
    {
        return $this->eScore;
    }

    public function rank(): int
    {
        return $this->rank;
    }

    public function participatedEntireCompetition(): bool
    {
        return $this->participatedEntireCompetition;
    }

    private function __construct()
    {
    }
}
