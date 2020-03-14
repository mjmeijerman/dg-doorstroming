<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

use LogicException;

final class CsvScoreSheetParser
{
    public static function parse(string $location, int $scoreSheetNumber): array
    {
        $handle    = fopen($location, "r");
        $seperator = ';';

        $totalScoreSheets = [];
        $vaultScoreSheets = [];
        $barScoreSheets   = [];
        $beamScoreSheets  = [];
        $floorScoreSheets = [];
        /** @var ScoreSheet|null $totalScoreSheet */
        $totalScoreSheet = null;
        /** @var ScoreSheet|null $vaultScoreSheet */
        $vaultScoreSheet = null;
        /** @var ScoreSheet|null $barScoreSheet */
        $barScoreSheet = null;
        /** @var ScoreSheet|null $beamScoreSheet */
        $beamScoreSheet = null;
        /** @var ScoreSheet|null $floorScoreSheet */
        $floorScoreSheet = null;
        while (($data = fgetcsv($handle, 0, $seperator)) !== false) {
            if (strpos(strtolower((string) $data[1]), 'med_grp_titel') !== false) {
                continue;
            }
            if (strpos(strtolower((string) $data[9]), 'bm') !== false) {
                continue;
            }
            $csvData[] = $data;
            if (!empty($data[1])) {

                if ($totalScoreSheet !== null) {
                    $totalScoreSheet->addRanking();
                    $vaultScoreSheet->addRanking();
                    $barScoreSheet->addRanking();
                    $beamScoreSheet->addRanking();
                    $floorScoreSheet->addRanking();

                    $totalScoreSheets[] = $totalScoreSheet;
                    $vaultScoreSheets[] = $vaultScoreSheet;
                    $barScoreSheets[]   = $barScoreSheet;
                    $beamScoreSheets[]  = $beamScoreSheet;
                    $floorScoreSheets[] = $floorScoreSheet;
                }

                $categoryLevelArray = array_values(array_filter(explode(' ', trim($data[1]))));
                $category           = Category::guess($categoryLevelArray[0]);
                $level              = Level::guess($categoryLevelArray[1], $category);
                if (!in_array($level->toString(), Level::getAvailableLevelsForCategoryAsString($category))) {
                    throw new LogicException(sprintf('Something went wrong while parsing "%s"', $data[1]));
                }

                $totalScoreSheet = ScoreSheet::create(
                    $data[1],
                    $scoreSheetNumber,
                    CategoryLevelCombination::create($category, $level),
                    []
                );
                $vaultScoreSheet = ScoreSheet::create(
                    $data[1],
                    $scoreSheetNumber,
                    CategoryLevelCombination::create($category, $level),
                    []
                );
                $barScoreSheet   = ScoreSheet::create(
                    $data[1],
                    $scoreSheetNumber,
                    CategoryLevelCombination::create($category, $level),
                    []
                );
                $beamScoreSheet  = ScoreSheet::create(
                    $data[1],
                    $scoreSheetNumber,
                    CategoryLevelCombination::create($category, $level),
                    []
                );
                $floorScoreSheet = ScoreSheet::create(
                    $data[1],
                    $scoreSheetNumber,
                    CategoryLevelCombination::create($category, $level),
                    []
                );
            }

            if (!empty($data[10])) {
                $participatedEntireCompetition = true;
                $scores                        = array_values(array_filter(explode('|', $data[10])));
                $vaultScores                   = array_values(array_filter(explode(' ', trim($scores[0]))));
                $barScores                     = array_values(array_filter(explode(' ', trim($scores[1]))));
                $beamScores                    = array_values(array_filter(explode(' ', trim($scores[2]))));
                $floorScores                   = array_values(array_filter(explode(' ', trim($scores[3]))));

                $totalScore               = (float) str_replace(',', '.', $data[8]);
                $firstTotalVaultScore     = (float) str_replace(',', '.', $vaultScores[1]);
                $firstVaultDScore         = (float) str_replace(',', '.', $vaultScores[0]);
                $firstVaultEScore         = ($firstTotalVaultScore - $firstVaultDScore) > 0
                    ? $firstTotalVaultScore - $firstVaultDScore : 0;
                $secondTotalVaultScore    = (float) str_replace(',', '.', $vaultScores[3]);
                $secondVaultDScore        = (float) str_replace(',', '.', $vaultScores[2]);
                $secondVaultEScore        = ($secondTotalVaultScore - $secondVaultDScore) > 0
                    ? $secondTotalVaultScore - $secondVaultDScore : 0;
                $totalVaultScoreForVault  = ($firstTotalVaultScore + $secondTotalVaultScore) / 2;
                $totalVaultEScoreForVault = ($firstVaultEScore + $secondVaultEScore) / 2;
                $totalBarScore            = (float) str_replace(',', '.', $barScores[1]);
                $barDScore                = (float) str_replace(',', '.', $barScores[0]);
                $barEScore                = ($totalBarScore - $barDScore) > 0 ? $totalBarScore - $barDScore : 0;
                $totalBeamScore           = (float) str_replace(',', '.', $beamScores[1]);
                $beamDScore               = (float) str_replace(',', '.', $beamScores[0]);
                $beamEScore               = ($totalBeamScore - $beamDScore) > 0 ? $totalBeamScore - $beamDScore : 0;
                $totalFloorScore          = (float) str_replace(',', '.', $floorScores[1]);
                $floorDScore              = (float) str_replace(',', '.', $floorScores[0]);
                $floorEScore              = ($totalFloorScore - $floorDScore) > 0 ? $totalFloorScore - $floorDScore : 0;

                if (Category::isCompulsory($category)) {
                    $totalVaultEScoreForTotal = $totalVaultEScoreForVault;

                    if (
                        ($firstVaultDScore === (float) 0 && $secondVaultDScore === (float) 0)
                        || $barDScore === (float) 0
                        || $beamDScore === (float) 0
                        || $floorDScore === (float) 0
                    ) {
                        $participatedEntireCompetition = false;
                    }
                } else {
                    $totalVaultEScoreForTotal = $firstVaultEScore;

                    if (
                        $firstVaultDScore === (float) 0
                        || $barDScore === (float) 0
                        || $beamDScore === (float) 0
                        || $floorDScore === (float) 0
                    ) {
                        $participatedEntireCompetition = false;
                    }
                }
                $eScoreSum = $totalVaultEScoreForTotal + $barEScore + $beamEScore + $floorEScore;

                $totalGymnast = Gymnast::create(
                    GymnastId::fromInteger((int) $data[4]),
                    $data[5],
                    $data[6],
                    $totalScore,
                    $eScoreSum,
                    $participatedEntireCompetition
                );
                $vaultGymnast = Gymnast::create(
                    GymnastId::fromInteger((int) $data[4]),
                    $data[5],
                    $data[6],
                    $totalVaultScoreForVault,
                    $totalVaultEScoreForVault,
                    $participatedEntireCompetition
                );
                $barGymnast   = Gymnast::create(
                    GymnastId::fromInteger((int) $data[4]),
                    $data[5],
                    $data[6],
                    $totalBarScore,
                    $barEScore,
                    $participatedEntireCompetition
                );
                $beamGymnast  = Gymnast::create(
                    GymnastId::fromInteger((int) $data[4]),
                    $data[5],
                    $data[6],
                    $totalBeamScore,
                    $beamEScore,
                    $participatedEntireCompetition
                );
                $floorGymnast = Gymnast::create(
                    GymnastId::fromInteger((int) $data[4]),
                    $data[5],
                    $data[6],
                    $totalFloorScore,
                    $floorEScore,
                    $participatedEntireCompetition
                );

                $totalScoreSheet->pushGymnast($totalGymnast);
                $vaultScoreSheet->pushGymnast($vaultGymnast);
                $barScoreSheet->pushGymnast($barGymnast);
                $beamScoreSheet->pushGymnast($beamGymnast);
                $floorScoreSheet->pushGymnast($floorGymnast);
            }
        }

        if ($totalScoreSheet !== null) {
            $totalScoreSheet->addRanking();
            $vaultScoreSheet->addRanking();
            $barScoreSheet->addRanking();
            $beamScoreSheet->addRanking();
            $floorScoreSheet->addRanking();

            $totalScoreSheets[] = $totalScoreSheet;
            $vaultScoreSheets[] = $vaultScoreSheet;
            $barScoreSheets[]   = $barScoreSheet;
            $beamScoreSheets[]  = $beamScoreSheet;
            $floorScoreSheets[] = $floorScoreSheet;
        }

        return [
            'total' => ScoreSheets::create($totalScoreSheets),
            'vault' => ScoreSheets::create($vaultScoreSheets),
            'bar'   => ScoreSheets::create($barScoreSheets),
            'beam'  => ScoreSheets::create($beamScoreSheets),
            'floor' => ScoreSheets::create($floorScoreSheets),
        ];
    }
}
