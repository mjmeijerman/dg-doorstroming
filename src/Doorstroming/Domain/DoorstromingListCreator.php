<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

final class DoorstromingListCreator
{
    public static function create(ScoreSheet $firstCompetitionScoreSheet, ScoreSheet $secondCompetitionScoreSheet, int $numberOfSpotsAvailable): DoorstromingList
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
        foreach ($secondCompetitionScoreSheet->gymnasts() as $secondCompetitionGymnast) {
            $firstCompetitionGymnast = $firstCompetitionScoreSheet->findGymnast(
                $secondCompetitionGymnast->gymnastId()
            );
            if (!$firstCompetitionGymnast) {
                throw new \LogicException(
                    sprintf(
                        'Gymnast with number "%s" was not found in first competition score sheet',
                        $secondCompetitionGymnast->gymnastId()->toInteger()
                    )
                );
            }
        }
        foreach ($firstCompetitionScoreSheet->gymnasts() as $firstCompetitionGymnast) {
            $secondCompetitionGymnast = $secondCompetitionScoreSheet->findGymnast(
                $firstCompetitionGymnast->gymnastId()
            );
            if (!$secondCompetitionGymnast) {
                throw new \LogicException(
                    sprintf(
                        'Gymnast with number "%s" was not found in second competition score sheet',
                        $firstCompetitionGymnast->gymnastId()->toInteger()
                    )
                );
            }

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
            $firstCompetitionScoreSheet->categoryLevelCombination(),
            $doorstromingEntries,
            $firstCompetitionScoreSheet->totalNumberOfFullParticipatedGymnasts()
            + $secondCompetitionScoreSheet->totalNumberOfFullParticipatedGymnasts(),
            $numberOfSpotsAvailable
        );
    }
}
