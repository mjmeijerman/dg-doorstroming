<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

final class DoorstromingEntry
{
    private string $gymnastName;

    private string $gymnastClub;

    private int $rankingFirstCompetition;

    private int $rankingSecondCompetition;

    private float $totalScoreFirstCompetition;

    private float $eScoreFirstCompetition;

    private float $totalScoreSecondCompetition;

    private float $eScoreSecondCompetition;

    private int $totalRank;

    public static function create(
        string $gymnastName,
        string $gymnastClub,
        int $rankingFirstCompetition,
        int $rankingSecondCompetition,
        float $totalScoreFirstCompetition,
        float $eScoreFirstCompetition,
        float $totalScoreSecondCompetition,
        float $eScoreSecondCompetition
    ): self
    {
        $self                              = new self();
        $self->gymnastName                 = $gymnastName;
        $self->gymnastClub                 = $gymnastClub;
        $self->rankingFirstCompetition     = $rankingFirstCompetition;
        $self->rankingSecondCompetition    = $rankingSecondCompetition;
        $self->totalScoreFirstCompetition  = $totalScoreFirstCompetition;
        $self->eScoreFirstCompetition      = $eScoreFirstCompetition;
        $self->totalScoreSecondCompetition = $totalScoreSecondCompetition;
        $self->eScoreSecondCompetition     = $eScoreSecondCompetition;

        return $self;
    }

    public function compare(DoorstromingEntry $other): int
    {
        $epsilon = 0.00001;
        if ($this->bestRank() !== $other->bestRank()) {
            return ($this->bestRank() < $other->bestRank()) ? -1 : 1;
        }

        if ($this->averageRank() !== $other->averageRank()) {
            return ($this->averageRank() < $other->averageRank()) ? -1 : 1;
        }

        if (abs($this->totalScore() - $other->totalScore()) > $epsilon) {
            return ($this->totalScore() > $other->totalScore()) ? -1 : 1;
        }

        if (abs($this->totalEScore() - $other->totalEScore()) > $epsilon) {
            return ($this->totalEScore() > $other->totalEScore()) ? -1 : 1;
        }

        return 0;
    }

    public function bestRank(): int
    {
        return min($this->rankingFirstCompetition, $this->rankingSecondCompetition);
    }

    public function averageRank(): float
    {
        return floatval(($this->rankingFirstCompetition + $this->rankingSecondCompetition) / 2);
    }

    public function totalScore(): float
    {
        return $this->totalScoreFirstCompetition + $this->totalScoreSecondCompetition;
    }

    public function totalEScore(): float
    {
        return $this->eScoreFirstCompetition + $this->eScoreSecondCompetition;
    }

    public function updateTotalRanking(int $rank): void
    {
        $this->totalRank = $rank;
    }

    public function gymnastName(): string
    {
        return $this->gymnastName;
    }

    public function gymnastClub(): string
    {
        return $this->gymnastClub;
    }

    public function rankingFirstCompetition(): int
    {
        return $this->rankingFirstCompetition;
    }

    public function rankingSecondCompetition(): int
    {
        return $this->rankingSecondCompetition;
    }

    public function totalScoreFirstCompetition(): float
    {
        return $this->totalScoreFirstCompetition;
    }

    public function eScoreFirstCompetition(): float
    {
        return $this->eScoreFirstCompetition;
    }

    public function totalScoreSecondCompetition(): float
    {
        return $this->totalScoreSecondCompetition;
    }

    public function eScoreSecondCompetition(): float
    {
        return $this->eScoreSecondCompetition;
    }

    public function totalRank(): int
    {
        return $this->totalRank;
    }

    private function __construct()
    {
    }
}
