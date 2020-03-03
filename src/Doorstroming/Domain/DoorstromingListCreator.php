<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

final class DoorstromingListCreator
{
    public static function create(
        ScoreSheet $firstCompetitionScoreSheet,
        ScoreSheet $secondCompetitionScoreSheet,
        int $numberOfDistrictSpotsAvailable,
        int $numberOfNationalSpotsAvailable,
        int $numberOfReserveSpots
    ): DoorstromingList
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
                $firstCompetitionGymnast = self::createGymnastWhenItDoesNotExist($secondCompetitionGymnast);
                $firstCompetitionScoreSheet->pushGymnast($firstCompetitionGymnast);
                $firstCompetitionScoreSheet->addRanking();
            }
        }
        foreach ($firstCompetitionScoreSheet->gymnasts() as $firstCompetitionGymnast) {
            $secondCompetitionGymnast = $secondCompetitionScoreSheet->findGymnast(
                $firstCompetitionGymnast->gymnastId()
            );
            if (!$secondCompetitionGymnast) {
                $secondCompetitionGymnast = self::createGymnastWhenItDoesNotExist($firstCompetitionGymnast);
                $secondCompetitionScoreSheet->pushGymnast($secondCompetitionGymnast);
                $secondCompetitionScoreSheet->addRanking();
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
            $numberOfDistrictSpotsAvailable,
            $numberOfNationalSpotsAvailable,
            $numberOfReserveSpots
        );
    }

    private static function createGymnastWhenItDoesNotExist(Gymnast $gymnast)
    {
        return Gymnast::create(
            $gymnast->gymnastId(),
            $gymnast->name(),
            $gymnast->club(),
            0,
            0,
            false
        );
    }
}
