<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

final class StandardDoorstromingListCreator implements DoorstromingListCreator
{
    public function create(ScoreSheet $firstCompetitionScoreSheet, ScoreSheet $secondCompetitionScoreSheet): DoorstromingList
    {
        if ($firstCompetitionScoreSheet->identifier() !== $secondCompetitionScoreSheet->identifier()) {
            throw new \LogicException(
                sprintf(
                    'Can not compare score sheet with identifier "%s" to score sheet with identifier "%s"',
                    $firstCompetitionScoreSheet->identifier(),
                    $secondCompetitionScoreSheet->identifier()
                )
            );
        }

        if ($firstCompetitionScoreSheet->scoreSheetNumber() === $secondCompetitionScoreSheet->scoreSheetNumber()) {
            throw new \LogicException('Got the same score sheet twice');
        }

        $doorstromingEntries = [];
        foreach ($firstCompetitionScoreSheet->gymnasts() as $firstCompetitionGymnast) {
            $secondCompetitionGymnast = $secondCompetitionScoreSheet->findGymnast(
                $firstCompetitionGymnast->gymnastId()
            );

            $doorstromingEntries[] = DoorstromingEntry::create(
                $firstCompetitionGymnast->name(),
                $firstCompetitionGymnast->club(),
                $firstCompetitionGymnast->rank(),
                $secondCompetitionGymnast->rank(),
                $firstCompetitionGymnast->totalScore(),
                $firstCompetitionGymnast->eScore(),
                $secondCompetitionGymnast->totalScore(),
                $secondCompetitionGymnast->eScore()
            );
        }

        return DoorstromingList::create(
            $firstCompetitionScoreSheet->identifier(),
            $firstCompetitionScoreSheet->category(),
            $firstCompetitionScoreSheet->level(),
            $doorstromingEntries
        );
    }
}
