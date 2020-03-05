<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Infrastructure\PhpSpreadsheet;

use Mark\Doorstroming\Domain\CategoryLevelCombination;
use Mark\Doorstroming\Domain\CategoryLevelSpecificDoorstromingLists;
use Mark\Doorstroming\Domain\CompetitionType;
use Mark\Doorstroming\Domain\UploadedFileId;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

final class SpreadsheetCombinedApparatusGenerator
{
    public static function generate(
        array $vaultDoorstromingen,
        array $barDoorstromingen,
        array $beamDoorstromingen,
        array $floorDoorstromingen,
        string $uploadDir
    ): ?string
    {
        $competitionType     = CompetitionType::DISTRICT();
        $combinedSpreadSheet = null;
        $reservesCombinedSheet = null;
        /** @var CategoryLevelSpecificDoorstromingLists $doorstromingCategoryLevel */
        foreach ($vaultDoorstromingen as $doorstromingCategoryLevel) {
            $doorstromingCategoryLevel->sortByIdentifier();
            foreach ($doorstromingCategoryLevel->doorstromingLists() as $list) {
                if ($list->numberOfAvailableSpots($competitionType) === 0) {
                    continue;
                }

                if ($combinedSpreadSheet === null) {
                    $combinedSpreadSheet            = self::createNewSpreadSheet();
                    $combinedSpreadsheetActiveSheet = self::getOrCreateSheetByName(
                        $combinedSpreadSheet,
                        $doorstromingCategoryLevel->categoryLevelCombination()
                    );
                    $combinedSpreadSheet->removeSheetByIndex(0);
                    $combinedSpreadsheetActiveSheet->getColumnDimension('A')->setWidth(12);
                    $combinedSpreadsheetActiveSheet->getColumnDimension('B')->setWidth(25);
                    $combinedSpreadsheetActiveSheet->getColumnDimension('C')->setWidth(30);
                    $combinedSpreadsheetActiveSheet->getColumnDimension('D')->setWidth(16);
                    $combinedSpreadsheetActiveSheet->getColumnDimension('E')->setWidth(16);
                    $combinedSpreadsheetActiveSheet->getColumnDimension('F')->setWidth(16);
                    $combinedSpreadsheetActiveSheet->getColumnDimension('G')->setWidth(16);
                    $combinedSpreadsheetActiveSheet->getColumnDimension('H')->setWidth(16);
                    $currentCombinedSpreadSheetRow = 2;

                    $combinedSpreadsheetActiveSheet->setCellValue(
                        'A' . $currentCombinedSpreadSheetRow,
                        'Totaaloverzicht doorgestroomde turnsters toestelfinales'
                    );
                    $combinedSpreadsheetActiveSheet->getStyle('A' . $currentCombinedSpreadSheetRow)->getFont()->setBold(
                        true
                    );
                    $currentCombinedSpreadSheetRow = $currentCombinedSpreadSheetRow + 2;
                    $combinedSpreadsheetActiveSheet->setCellValue(
                        'A' . $currentCombinedSpreadSheetRow,
                        $doorstromingCategoryLevel->categoryLevelCombination()
                            ->category()
                            ->toString() . ' ' . $doorstromingCategoryLevel->categoryLevelCombination()
                            ->level()
                            ->toString()
                    );
                    $combinedSpreadsheetActiveSheet->setCellValue('B' . $currentCombinedSpreadSheetRow, 'Turnster');
                    $combinedSpreadsheetActiveSheet->setCellValue('C' . $currentCombinedSpreadSheetRow, 'Vereniging');
                    $combinedSpreadsheetActiveSheet->setCellValue(
                        'D' . $currentCombinedSpreadSheetRow,
                        'Komt van groep'
                    );
                    $combinedSpreadsheetActiveSheet->setCellValue('E' . $currentCombinedSpreadSheetRow, 'Sprong');
                    $combinedSpreadsheetActiveSheet->setCellValue('F' . $currentCombinedSpreadSheetRow, 'Brug');
                    $combinedSpreadsheetActiveSheet->setCellValue('G' . $currentCombinedSpreadSheetRow, 'Balk');
                    $combinedSpreadsheetActiveSheet->setCellValue('H' . $currentCombinedSpreadSheetRow, 'Vloer');
                    $combinedSpreadsheetActiveSheet->getStyle(
                        'A' . $currentCombinedSpreadSheetRow . ':C' . $currentCombinedSpreadSheetRow
                    )->getFont()->setBold(true);
                    $currentCombinedSpreadSheetRow++;
                }

                if ($combinedSpreadsheetActiveSheet->getTitle() !==
                    $doorstromingCategoryLevel->categoryLevelCombination()->category()->toString()
                    . $doorstromingCategoryLevel->categoryLevelCombination()->level()->toString()) {
                    $combinedSpreadsheetActiveSheet = self::getOrCreateSheetByName(
                        $combinedSpreadSheet,
                        $doorstromingCategoryLevel->categoryLevelCombination()
                    );
                    $combinedSpreadsheetActiveSheet->getColumnDimension('A')->setWidth(12);
                    $combinedSpreadsheetActiveSheet->getColumnDimension('B')->setWidth(25);
                    $combinedSpreadsheetActiveSheet->getColumnDimension('C')->setWidth(30);
                    $combinedSpreadsheetActiveSheet->getColumnDimension('D')->setWidth(16);
                    $combinedSpreadsheetActiveSheet->getColumnDimension('E')->setWidth(16);
                    $combinedSpreadsheetActiveSheet->getColumnDimension('F')->setWidth(16);
                    $combinedSpreadsheetActiveSheet->getColumnDimension('G')->setWidth(16);
                    $combinedSpreadsheetActiveSheet->getColumnDimension('H')->setWidth(16);
                    $currentCombinedSpreadSheetRow = 2;

                    $combinedSpreadsheetActiveSheet->setCellValue(
                        'A' . $currentCombinedSpreadSheetRow,
                        'Totaaloverzicht doorgestroomde turnsters toestelfinales'
                    );
                    $combinedSpreadsheetActiveSheet->getStyle('A' . $currentCombinedSpreadSheetRow)->getFont()->setBold(
                        true
                    );
                    $currentCombinedSpreadSheetRow = $currentCombinedSpreadSheetRow + 2;
                    $combinedSpreadsheetActiveSheet->setCellValue(
                        'A' . $currentCombinedSpreadSheetRow,
                        $doorstromingCategoryLevel->categoryLevelCombination()
                            ->category()
                            ->toString() . ' ' . $doorstromingCategoryLevel->categoryLevelCombination()
                            ->level()
                            ->toString()
                    );
                    $combinedSpreadsheetActiveSheet->setCellValue('B' . $currentCombinedSpreadSheetRow, 'Turnster');
                    $combinedSpreadsheetActiveSheet->setCellValue('C' . $currentCombinedSpreadSheetRow, 'Vereniging');
                    $combinedSpreadsheetActiveSheet->setCellValue(
                        'D' . $currentCombinedSpreadSheetRow,
                        'Komt van groep'
                    );
                    $combinedSpreadsheetActiveSheet->setCellValue('E' . $currentCombinedSpreadSheetRow, 'Sprong');
                    $combinedSpreadsheetActiveSheet->getStyle('E' . $currentCombinedSpreadSheetRow)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $combinedSpreadsheetActiveSheet->setCellValue('F' . $currentCombinedSpreadSheetRow, 'Brug');
                    $combinedSpreadsheetActiveSheet->getStyle('F' . $currentCombinedSpreadSheetRow)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $combinedSpreadsheetActiveSheet->setCellValue('G' . $currentCombinedSpreadSheetRow, 'Balk');
                    $combinedSpreadsheetActiveSheet->getStyle('G' . $currentCombinedSpreadSheetRow)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $combinedSpreadsheetActiveSheet->setCellValue('H' . $currentCombinedSpreadSheetRow, 'Vloer');
                    $combinedSpreadsheetActiveSheet->getStyle('H' . $currentCombinedSpreadSheetRow)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $combinedSpreadsheetActiveSheet->getStyle(
                        'A' . $currentCombinedSpreadSheetRow . ':C' . $currentCombinedSpreadSheetRow
                    )->getFont()->setBold(true);
                    $currentCombinedSpreadSheetRow++;
                }
                $highestRowNumbers[$doorstromingCategoryLevel->categoryLevelCombination()->category()->toString()
                . $doorstromingCategoryLevel->categoryLevelCombination()->level()->toString()]
                    = 0;
                foreach ($list->doorstromingEntries() as $doorstromingEntry) {
                    if ($doorstromingEntry->totalRank() > $list->numberOfAvailableSpots($competitionType)) {
                        break;
                    }
                    $combinedSpreadsheetActiveSheet->setCellValue(
                        'B' . $currentCombinedSpreadSheetRow,
                        $doorstromingEntry->gymnastName()
                    );
                    $combinedSpreadsheetActiveSheet->setCellValue(
                        'C' . $currentCombinedSpreadSheetRow,
                        $doorstromingEntry->gymnastClub()
                    );
                    $combinedSpreadsheetActiveSheet->setCellValue(
                        'D' . $currentCombinedSpreadSheetRow,
                        $list->identifier()
                    );
                    $combinedSpreadsheetActiveSheet->setCellValue('E' . $currentCombinedSpreadSheetRow, 'X');
                    $combinedSpreadsheetActiveSheet->getStyle('E' . $currentCombinedSpreadSheetRow)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $highestRowNumbers[$doorstromingCategoryLevel->categoryLevelCombination()->category()->toString()
                    . $doorstromingCategoryLevel->categoryLevelCombination()->level()->toString()]
                        = $currentCombinedSpreadSheetRow;
                    $currentCombinedSpreadSheetRow++;
                }
            }
        }

        foreach ($barDoorstromingen as $doorstromingCategoryLevel) {
            $doorstromingCategoryLevel->sortByIdentifier();
            foreach ($doorstromingCategoryLevel->doorstromingLists() as $list) {
                if ($combinedSpreadsheetActiveSheet->getTitle() !==
                    $doorstromingCategoryLevel->categoryLevelCombination()->category()->toString()
                    . $doorstromingCategoryLevel->categoryLevelCombination()->level()->toString()) {
                    $combinedSpreadsheetActiveSheet = self::getOrCreateSheetByName(
                        $combinedSpreadSheet,
                        $doorstromingCategoryLevel->categoryLevelCombination()
                    );
                }

                foreach ($list->doorstromingEntries() as $doorstromingEntry) {
                    if ($doorstromingEntry->totalRank() > $list->numberOfAvailableSpots($competitionType)) {
                        break;
                    }
                    for ($currentCombinedSpreadSheetRow
                             = 5; $currentCombinedSpreadSheetRow <= $highestRowNumbers[$doorstromingCategoryLevel->categoryLevelCombination(
                    )->category()->toString()
                    . $doorstromingCategoryLevel->categoryLevelCombination()->level()->toString(
                    )]; $currentCombinedSpreadSheetRow++) {
                        if ($combinedSpreadsheetActiveSheet->getCell('B' . $currentCombinedSpreadSheetRow)->getValue()
                            === $doorstromingEntry->gymnastName() &&
                            $combinedSpreadsheetActiveSheet->getCell('C' . $currentCombinedSpreadSheetRow)->getValue()
                            === $doorstromingEntry->gymnastClub()) {
                            $combinedSpreadsheetActiveSheet->setCellValue('F' . $currentCombinedSpreadSheetRow, 'X');
                            $combinedSpreadsheetActiveSheet->getStyle('F' . $currentCombinedSpreadSheetRow)
                                ->getAlignment()
                                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                            continue 2;
                        }
                    }

                    $combinedSpreadsheetActiveSheet->setCellValue(
                        'B' . $currentCombinedSpreadSheetRow,
                        $doorstromingEntry->gymnastName()
                    );
                    $combinedSpreadsheetActiveSheet->setCellValue(
                        'C' . $currentCombinedSpreadSheetRow,
                        $doorstromingEntry->gymnastClub()
                    );
                    $combinedSpreadsheetActiveSheet->setCellValue(
                        'D' . $currentCombinedSpreadSheetRow,
                        $list->identifier()
                    );
                    $combinedSpreadsheetActiveSheet->setCellValue('F' . $currentCombinedSpreadSheetRow, 'X');
                    $combinedSpreadsheetActiveSheet->getStyle('F' . $currentCombinedSpreadSheetRow)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $highestRowNumbers[$doorstromingCategoryLevel->categoryLevelCombination()->category()->toString()
                    . $doorstromingCategoryLevel->categoryLevelCombination()->level()->toString()]
                        = $currentCombinedSpreadSheetRow;
                }
            }
        }

        foreach ($beamDoorstromingen as $doorstromingCategoryLevel) {
            $doorstromingCategoryLevel->sortByIdentifier();
            foreach ($doorstromingCategoryLevel->doorstromingLists() as $list) {
                if ($combinedSpreadsheetActiveSheet->getTitle() !==
                    $doorstromingCategoryLevel->categoryLevelCombination()->category()->toString()
                    . $doorstromingCategoryLevel->categoryLevelCombination()->level()->toString()) {
                    $combinedSpreadsheetActiveSheet = self::getOrCreateSheetByName(
                        $combinedSpreadSheet,
                        $doorstromingCategoryLevel->categoryLevelCombination()
                    );
                }

                foreach ($list->doorstromingEntries() as $doorstromingEntry) {
                    if ($doorstromingEntry->totalRank() > $list->numberOfAvailableSpots($competitionType)) {
                        break;
                    }
                    for ($currentCombinedSpreadSheetRow = 5; $currentCombinedSpreadSheetRow <=
                    $highestRowNumbers[$doorstromingCategoryLevel->categoryLevelCombination()->category()->toString()
                    . $doorstromingCategoryLevel->categoryLevelCombination()->level()->toString()];
                         $currentCombinedSpreadSheetRow++) {
                        if ($combinedSpreadsheetActiveSheet->getCell('B' . $currentCombinedSpreadSheetRow)->getValue()
                            === $doorstromingEntry->gymnastName() &&
                            $combinedSpreadsheetActiveSheet->getCell('C' . $currentCombinedSpreadSheetRow)->getValue()
                            === $doorstromingEntry->gymnastClub()) {
                            $combinedSpreadsheetActiveSheet->setCellValue('G' . $currentCombinedSpreadSheetRow, 'X');
                            $combinedSpreadsheetActiveSheet->getStyle('G' . $currentCombinedSpreadSheetRow)
                                ->getAlignment()
                                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                            continue 2;
                        }
                    }

                    $combinedSpreadsheetActiveSheet->setCellValue(
                        'B' . $currentCombinedSpreadSheetRow,
                        $doorstromingEntry->gymnastName()
                    );
                    $combinedSpreadsheetActiveSheet->setCellValue(
                        'C' . $currentCombinedSpreadSheetRow,
                        $doorstromingEntry->gymnastClub()
                    );
                    $combinedSpreadsheetActiveSheet->setCellValue(
                        'D' . $currentCombinedSpreadSheetRow,
                        $list->identifier()
                    );
                    $combinedSpreadsheetActiveSheet->setCellValue('G' . $currentCombinedSpreadSheetRow, 'X');
                    $combinedSpreadsheetActiveSheet->getStyle('G' . $currentCombinedSpreadSheetRow)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $highestRowNumbers[$doorstromingCategoryLevel->categoryLevelCombination()->category()->toString()
                    . $doorstromingCategoryLevel->categoryLevelCombination()->level()->toString()]
                        = $currentCombinedSpreadSheetRow;
                }
            }
        }

        foreach ($floorDoorstromingen as $doorstromingCategoryLevel) {
            $doorstromingCategoryLevel->sortByIdentifier();
            foreach ($doorstromingCategoryLevel->doorstromingLists() as $list) {
                if ($combinedSpreadsheetActiveSheet->getTitle() !==
                    $doorstromingCategoryLevel->categoryLevelCombination()->category()->toString()
                    . $doorstromingCategoryLevel->categoryLevelCombination()->level()->toString()) {
                    $combinedSpreadsheetActiveSheet = self::getOrCreateSheetByName(
                        $combinedSpreadSheet,
                        $doorstromingCategoryLevel->categoryLevelCombination()
                    );
                }

                foreach ($list->doorstromingEntries() as $doorstromingEntry) {
                    if ($doorstromingEntry->totalRank() > $list->numberOfAvailableSpots($competitionType)) {
                        break;
                    }
                    for ($currentCombinedSpreadSheetRow = 5; $currentCombinedSpreadSheetRow <=
                    $highestRowNumbers[$doorstromingCategoryLevel->categoryLevelCombination()->category()->toString()
                    . $doorstromingCategoryLevel->categoryLevelCombination()->level()->toString()];
                         $currentCombinedSpreadSheetRow++) {
                        if ($combinedSpreadsheetActiveSheet->getCell('B' . $currentCombinedSpreadSheetRow)->getValue()
                            === $doorstromingEntry->gymnastName() &&
                            $combinedSpreadsheetActiveSheet->getCell('C' . $currentCombinedSpreadSheetRow)->getValue()
                            === $doorstromingEntry->gymnastClub()) {
                            $combinedSpreadsheetActiveSheet->setCellValue('H' . $currentCombinedSpreadSheetRow, 'X');
                            $combinedSpreadsheetActiveSheet->getStyle('H' . $currentCombinedSpreadSheetRow)
                                ->getAlignment()
                                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                            continue 2;
                        }
                    }

                    $combinedSpreadsheetActiveSheet->setCellValue(
                        'B' . $currentCombinedSpreadSheetRow,
                        $doorstromingEntry->gymnastName()
                    );
                    $combinedSpreadsheetActiveSheet->setCellValue(
                        'C' . $currentCombinedSpreadSheetRow,
                        $doorstromingEntry->gymnastClub()
                    );
                    $combinedSpreadsheetActiveSheet->setCellValue(
                        'D' . $currentCombinedSpreadSheetRow,
                        $list->identifier()
                    );
                    $combinedSpreadsheetActiveSheet->setCellValue('H' . $currentCombinedSpreadSheetRow, 'X');
                    $combinedSpreadsheetActiveSheet->getStyle('H' . $currentCombinedSpreadSheetRow)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $highestRowNumbers[$doorstromingCategoryLevel->categoryLevelCombination()->category()->toString()
                    . $doorstromingCategoryLevel->categoryLevelCombination()->level()->toString()]
                        = $currentCombinedSpreadSheetRow;
                }
            }
        }

        $combinedSpreadSheetFileName = null;
        if ($combinedSpreadSheet) {
            $combinedSpreadSheetId       = UploadedFileId::generate();
            $combinedSpreadSheetFileName = $combinedSpreadSheetId->toString() . '.xlsx';
            $writer                      = new Xlsx($combinedSpreadSheet);
            $writer->save($uploadDir . $combinedSpreadSheetFileName);
        }

        return $combinedSpreadSheetFileName;
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
