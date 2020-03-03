<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

final class CategoryLevelSpecificDoorstromingListsCreator
{
    public static function create(
        CategoryLevelCombination $categoryLevelCombination,
        ScoreSheets $scoreSheetsFirstCompetition,
        ScoreSheets $scoreSheetsSecondCompetition,
        int $districtSpotsPerGroup,
        int $districtReserveSpotsPerGroup,
        int $districtExtraSpots,
        int $nationalSpotsPerGroup,
        int $nationalExtraSpots
    ): CategoryLevelSpecificDoorstromingLists
    {
        $categoryLevelSpecificDoorstromingLists = [];
        $filteredScoreSheetsFirstCompetition    = $scoreSheetsFirstCompetition->findByCategoryLevelCombination(
            $categoryLevelCombination
        );

        foreach ($filteredScoreSheetsFirstCompetition as $firstSheet) {
            $secondSheet = $scoreSheetsSecondCompetition->findByIdentifier($firstSheet->identifier());

            $categoryLevelSpecificDoorstromingLists[] = DoorstromingListCreator::create(
                $firstSheet,
                $secondSheet,
                $districtSpotsPerGroup,
                $nationalSpotsPerGroup,
                $districtReserveSpotsPerGroup
            );
        }

        return CategoryLevelSpecificDoorstromingLists::create(
            $categoryLevelCombination,
            $categoryLevelSpecificDoorstromingLists,
            $districtExtraSpots,
            $nationalExtraSpots
        );
    }
}
