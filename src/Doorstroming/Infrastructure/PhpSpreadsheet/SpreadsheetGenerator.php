<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Infrastructure\PhpSpreadsheet;

use Mark\Doorstroming\Domain\CategoryLevelCombination;
use Mark\Doorstroming\Domain\CategoryLevelSpecificDoorstromingLists;
use Mark\Doorstroming\Domain\CompetitionType;
use Mark\Doorstroming\Domain\UploadedFileId;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

final class SpreadsheetGenerator
{
    public static function generate(
        array $doorstromingen,
        string $apparatusInDutch,
        CompetitionType $competitionType,
        string $uploadDir
    ): array
    {
        $files               = [];
        $fullLists           = null;
        $reserveLists        = null;
        $doorstromingLists   = null;
        $extraSpotsAvailable = [];

        /** @var CategoryLevelSpecificDoorstromingLists $doorstromingCategoryLevel */
        foreach ($doorstromingen as $doorstromingCategoryLevel) {
            if ($doorstromingCategoryLevel->numberOfExtraSpots($competitionType) > 0) {
                $extraSpotsAvailable[] = [
                    'category'   => $doorstromingCategoryLevel->categoryLevelCombination()
                        ->category()
                        ->toString(),
                    'level'      => $doorstromingCategoryLevel->categoryLevelCombination()->level()->toString(),
                    'extraSpots' => $doorstromingCategoryLevel->numberOfExtraSpots($competitionType)
                ];
            }

            $doorstromingCategoryLevel->sortByIdentifier();
            foreach ($doorstromingCategoryLevel->doorstromingLists() as $list) {
                if ($list->numberOfAvailableSpots($competitionType) === 0) {
                    continue;
                }

                if ($fullLists === null) {
                    $fullLists            = self::createNewSpreadSheet();
                    $fullListsActiveSheet = self::getOrCreateSheetByName(
                        $fullLists,
                        $doorstromingCategoryLevel->categoryLevelCombination()
                    );
                    $fullLists->removeSheetByIndex(0);
                    $currentFullListsRow = 2;
                    $fullListsActiveSheet->getColumnDimension('A')->setWidth(12);
                    $fullListsActiveSheet->getColumnDimension('B')->setWidth(25);
                    $fullListsActiveSheet->getColumnDimension('C')->setWidth(30);
                    $fullListsActiveSheet->getColumnDimension('D')->setWidth(16);
                    $fullListsActiveSheet->getColumnDimension('E')->setWidth(16);
                    $fullListsActiveSheet->getColumnDimension('F')->setWidth(11);
                    $fullListsActiveSheet->getColumnDimension('G')->setWidth(25);
                }

                if ($fullListsActiveSheet->getTitle() !==
                    $doorstromingCategoryLevel->categoryLevelCombination()->category()->toString()
                    . $doorstromingCategoryLevel->categoryLevelCombination()->level()->toString()) {
                    $fullListsActiveSheet = self::getOrCreateSheetByName(
                        $fullLists,
                        $doorstromingCategoryLevel->categoryLevelCombination()
                    );
                    $fullListsActiveSheet->getColumnDimension('A')->setWidth(12);
                    $fullListsActiveSheet->getColumnDimension('B')->setWidth(25);
                    $fullListsActiveSheet->getColumnDimension('C')->setWidth(30);
                    $fullListsActiveSheet->getColumnDimension('D')->setWidth(16);
                    $fullListsActiveSheet->getColumnDimension('E')->setWidth(16);
                    $fullListsActiveSheet->getColumnDimension('F')->setWidth(11);
                    $fullListsActiveSheet->getColumnDimension('G')->setWidth(25);
                    $currentFullListsRow = 2;
                }

                $fullListsActiveSheet->setCellValue('A' . $currentFullListsRow, 'Doorstroming ' . $apparatusInDutch);
                $fullListsActiveSheet->getStyle('A' . $currentFullListsRow)->getFont()->setBold( true );
                $currentFullListsRow = $currentFullListsRow + 2;
                $fullListsActiveSheet->setCellValue('A' . $currentFullListsRow, $list->identifier());
                $fullListsActiveSheet->setCellValue('B' . $currentFullListsRow, 'Naam');
                $fullListsActiveSheet->setCellValue('C' . $currentFullListsRow, 'Vereniging');
                $fullListsActiveSheet->setCellValue('D' . $currentFullListsRow, 'Ranking Wedstrijd 1');
                $fullListsActiveSheet->setCellValue('E' . $currentFullListsRow, 'Ranking Wedstrijd 2');
                $fullListsActiveSheet->setCellValue('F' . $currentFullListsRow, 'Beste Ranking');
                $fullListsActiveSheet->setCellValue('G' . $currentFullListsRow, 'Gemiddelde Ranking (ex aequo)');
                $fullListsActiveSheet->getStyle('A' . $currentFullListsRow . ':G' . $currentFullListsRow)->getFont()->setBold( true );

                foreach ($list->doorstromingEntries() as $doorstromingEntry) {
                    $currentFullListsRow++;
                    if ($doorstromingEntry->totalRank() <= $list->numberOfAvailableSpots($competitionType)) {
                        $fullListsActiveSheet->getStyle('B' . $currentFullListsRow . ':G' . $currentFullListsRow)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setARGB('7CFC00');
                    }
                    $fullListsActiveSheet->setCellValue('B' . $currentFullListsRow, $doorstromingEntry->gymnastName());
                    $fullListsActiveSheet->setCellValue('C' . $currentFullListsRow, $doorstromingEntry->gymnastClub());
                    $fullListsActiveSheet->setCellValue(
                        'D' . $currentFullListsRow,
                        $doorstromingEntry->rankingFirstCompetition()
                    );
                    $fullListsActiveSheet->setCellValue(
                        'E' . $currentFullListsRow,
                        $doorstromingEntry->rankingSecondCompetition()
                    );
                    $fullListsActiveSheet->setCellValue('F' . $currentFullListsRow, $doorstromingEntry->bestRank());
                    $fullListsActiveSheet->setCellValue('G' . $currentFullListsRow, $doorstromingEntry->averageRank());
                }

                $currentFullListsRow = $currentFullListsRow + 5;

                if ($doorstromingLists === null) {
                    $doorstromingLists            = self::createNewSpreadSheet();
                    $doorstromingListsActiveSheet = self::getOrCreateSheetByName(
                        $doorstromingLists,
                        $doorstromingCategoryLevel->categoryLevelCombination()
                    );
                    $doorstromingLists->removeSheetByIndex(0);
                    $doorstromingListsActiveSheet->getColumnDimension('A')->setWidth(25);
                    $doorstromingListsActiveSheet->getColumnDimension('B')->setWidth(30);
                    $doorstromingListsActiveSheet->getColumnDimension('C')->setWidth(15);
                    $currentDoorstromingListsRow = 2;

                    $doorstromingListsActiveSheet->setCellValue(
                        'A' . $currentDoorstromingListsRow,
                        'Overzicht doorgestroomde turnsters: ' . $apparatusInDutch
                    );
                    $doorstromingListsActiveSheet->getStyle('A' . $currentDoorstromingListsRow)->getFont()->setBold( true );
                    $currentDoorstromingListsRow = $currentDoorstromingListsRow + 2;
                    $doorstromingListsActiveSheet->setCellValue('A' . $currentDoorstromingListsRow, 'Naam');
                    $doorstromingListsActiveSheet->setCellValue('B' . $currentDoorstromingListsRow, 'Vereniging');
                    $doorstromingListsActiveSheet->setCellValue('C' . $currentDoorstromingListsRow, 'Komt van groep');
                    $doorstromingListsActiveSheet->getStyle('A' . $currentDoorstromingListsRow . ':C' . $currentDoorstromingListsRow)->getFont()->setBold( true );
                    $currentDoorstromingListsRow++;
                }

                if ($doorstromingListsActiveSheet->getTitle() !==
                    $doorstromingCategoryLevel->categoryLevelCombination()->category()->toString()
                    . $doorstromingCategoryLevel->categoryLevelCombination()->level()->toString()) {
                    $doorstromingListsActiveSheet = self::getOrCreateSheetByName(
                        $doorstromingLists,
                        $doorstromingCategoryLevel->categoryLevelCombination()
                    );
                    $doorstromingListsActiveSheet->getColumnDimension('A')->setWidth(25);
                    $doorstromingListsActiveSheet->getColumnDimension('B')->setWidth(30);
                    $doorstromingListsActiveSheet->getColumnDimension('C')->setWidth(15);
                    $currentDoorstromingListsRow = 2;

                    $doorstromingListsActiveSheet->setCellValue(
                        'A' . $currentDoorstromingListsRow,
                        'Overzicht doorgestroomde turnsters: ' . $apparatusInDutch
                    );
                    $doorstromingListsActiveSheet->getStyle('A' . $currentDoorstromingListsRow)->getFont()->setBold( true );
                    $currentDoorstromingListsRow = $currentDoorstromingListsRow + 2;
                    $doorstromingListsActiveSheet->setCellValue('A' . $currentDoorstromingListsRow, 'Naam');
                    $doorstromingListsActiveSheet->setCellValue('B' . $currentDoorstromingListsRow, 'Vereniging');
                    $doorstromingListsActiveSheet->setCellValue('C' . $currentDoorstromingListsRow, 'Komt van groep');
                    $doorstromingListsActiveSheet->getStyle('A' . $currentDoorstromingListsRow . ':C' . $currentDoorstromingListsRow)->getFont()->setBold( true );
                    $currentDoorstromingListsRow++;
                }

                foreach ($list->doorstromingEntries() as $doorstromingEntry) {
                    if ($doorstromingEntry->totalRank() > $list->numberOfAvailableSpots($competitionType)) {
                        break;
                    }
                    $doorstromingListsActiveSheet->setCellValue(
                        'A' . $currentDoorstromingListsRow,
                        $doorstromingEntry->gymnastName()
                    );
                    $doorstromingListsActiveSheet->setCellValue(
                        'B' . $currentDoorstromingListsRow,
                        $doorstromingEntry->gymnastClub()
                    );
                    $doorstromingListsActiveSheet->setCellValue(
                        'C' . $currentDoorstromingListsRow,
                        $list->identifier()
                    );
                    $currentDoorstromingListsRow++;
                }


                if ($list->numberOfReserveSpots($competitionType) > 0) {
                    if ($reserveLists === null) {
                        $reserveLists            = self::createNewSpreadSheet();
                        $reserveListsActiveSheet = self::getOrCreateSheetByName(
                            $reserveLists,
                            $doorstromingCategoryLevel->categoryLevelCombination()
                        );
                        $reserveLists->removeSheetByIndex(0);
                        $reserveListsActiveSheet->getColumnDimension('A')->setWidth(13);
                        $reserveListsActiveSheet->getColumnDimension('B')->setWidth(25);
                        $reserveListsActiveSheet->getColumnDimension('C')->setWidth(30);
                        $reserveListsActiveSheet->getColumnDimension('D')->setWidth(15);
                        $currentReserveListsRow = 2;

                        $reserveListsActiveSheet->setCellValue(
                            'A' . $currentReserveListsRow,
                            'Overzicht reserve turnsters: ' . $apparatusInDutch
                        );
                        $reserveListsActiveSheet->getStyle('A' . $currentReserveListsRow)->getFont()->setBold( true );
                        $currentReserveListsRow = $currentReserveListsRow + 2;
                        $reserveListsActiveSheet->setCellValue('A' . $currentReserveListsRow, 'Reserve nummer');
                        $reserveListsActiveSheet->setCellValue('B' . $currentReserveListsRow, 'Naam');
                        $reserveListsActiveSheet->setCellValue('C' . $currentReserveListsRow, 'Vereniging');
                        $reserveListsActiveSheet->setCellValue('D' . $currentReserveListsRow, 'Komt van groep');
                        $reserveListsActiveSheet->getStyle('A' . $currentReserveListsRow . ':D' . $currentReserveListsRow)->getFont()->setBold( true );
                        $currentReserveListsRow++;
                    }

                    if ($reserveListsActiveSheet->getTitle() !==
                        $doorstromingCategoryLevel->categoryLevelCombination()->category()->toString()
                        . $doorstromingCategoryLevel->categoryLevelCombination()->level()->toString()) {
                        $reserveListsActiveSheet = self::getOrCreateSheetByName(
                            $reserveLists,
                            $doorstromingCategoryLevel->categoryLevelCombination()
                        );
                        $reserveListsActiveSheet->getColumnDimension('A')->setWidth(13);
                        $reserveListsActiveSheet->getColumnDimension('B')->setWidth(25);
                        $reserveListsActiveSheet->getColumnDimension('C')->setWidth(30);
                        $reserveListsActiveSheet->getColumnDimension('D')->setWidth(15);
                        $currentReserveListsRow = 2;

                        $reserveListsActiveSheet->setCellValue(
                            'A' . $currentReserveListsRow,
                            'Overzicht reserve turnsters: ' . $apparatusInDutch
                        );
                        $reserveListsActiveSheet->getStyle('A' . $currentReserveListsRow)->getFont()->setBold( true );
                        $currentReserveListsRow = $currentReserveListsRow + 2;
                        $reserveListsActiveSheet->setCellValue('A' . $currentReserveListsRow, 'Reserve nummer');
                        $reserveListsActiveSheet->setCellValue('B' . $currentReserveListsRow, 'Naam');
                        $reserveListsActiveSheet->setCellValue('C' . $currentReserveListsRow, 'Vereniging');
                        $reserveListsActiveSheet->setCellValue('D' . $currentReserveListsRow, 'Komt van groep');
                        $reserveListsActiveSheet->getStyle('A' . $currentReserveListsRow . ':D' . $currentReserveListsRow)->getFont()->setBold( true );
                        $currentReserveListsRow++;
                    }

                    $reserveNumber = 1;
                    foreach ($list->doorstromingEntries() as $doorstromingEntry) {
                        if ($doorstromingEntry->totalRank() <= $list->numberOfAvailableSpots($competitionType)) {
                            continue;
                        }

                        if ($doorstromingEntry->totalRank() > $list->numberOfAvailableSpots($competitionType)
                            + $list->numberOfReserveSpots($competitionType)) {
                            break;
                        }

                        $reserveListsActiveSheet->setCellValue(
                            'A' . $currentReserveListsRow,
                            'R' . $reserveNumber
                        );
                        $reserveListsActiveSheet->setCellValue(
                            'B' . $currentReserveListsRow,
                            $doorstromingEntry->gymnastName()
                        );
                        $reserveListsActiveSheet->setCellValue(
                            'C' . $currentReserveListsRow,
                            $doorstromingEntry->gymnastClub()
                        );
                        $reserveListsActiveSheet->setCellValue(
                            'D' . $currentReserveListsRow,
                            $list->identifier()
                        );
                        $currentReserveListsRow++;
                        $reserveNumber++;
                    }
                }
            }
        }

        if (!empty($fullLists)) {
            $fullListsId      = UploadedFileId::generate();
            $fullListFileName = $fullListsId->toString() . '.xlsx';
            $writer           = new Xlsx($fullLists);
            $writer->save($uploadDir . $fullListFileName);
            $doorstromingListsId       = UploadedFileId::generate();
            $doorstromingListsFileName = $doorstromingListsId->toString() . '.xlsx';
            $writer                    = new Xlsx($doorstromingLists);
            $writer->save($uploadDir . $doorstromingListsFileName);
            $files = [
                'Volledige lijsten'                              => $fullListFileName,
                'Lijsten met alleen de doorgestroomde turnsters' => $doorstromingListsFileName,
                'extraSpotsAvailable'                            => $extraSpotsAvailable,
            ];
        }
        if (!empty($reserveLists)) {
            $reserveListsId       = UploadedFileId::generate();
            $reserveListsFileName = $reserveListsId->toString() . '.xlsx';
            $writer               = new Xlsx($reserveLists);
            $writer->save($uploadDir . $reserveListsFileName);
            $files['Reserves'] = $reserveListsFileName;
        }

        return $files;
    }

    private static function createNewSpreadSheet(): Spreadsheet
    {
        $spreadSheet = new Spreadsheet();
        $spreadSheet->getProperties()->setCreator("Mark Meijerman")
            ->setLastModifiedBy("Mark Meijerman")
            ->setTitle("DG doorstromingen")
            ->setSubject("DG doorstromingen")
            ->setDescription("DG doorstromingen");

        return $spreadSheet;
    }

    private static function getOrCreateSheetByName(Spreadsheet $spreadsheet, CategoryLevelCombination $categoryLevelCombination): Worksheet
    {
        $sheet = $spreadsheet->getSheetByName(
            $categoryLevelCombination->category()->toString()
            . $categoryLevelCombination->level()->toString()
        );
        if (!$sheet) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle(
                $categoryLevelCombination->category()->toString()
                . $categoryLevelCombination->level()->toString()
            );
        }

        return $sheet;
    }
}
