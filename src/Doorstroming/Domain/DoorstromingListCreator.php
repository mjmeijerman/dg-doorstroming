<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

interface DoorstromingListCreator
{
    public function create(ScoreSheet $firstCompetition, ScoreSheet $secondCompetition): DoorstromingList;
}
