<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Infrastructure\SymfonyController;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use LogicException;
use Mark\Doorstroming\Domain\CategoryLevelCombination;
use Mark\Doorstroming\Domain\CategoryLevelSpecificDoorstromingLists;
use Mark\Doorstroming\Domain\CategoryLevelSpecificDoorstromingListsCreator;
use Mark\Doorstroming\Domain\CompetitionType;
use Mark\Doorstroming\Domain\ScoreSheets;
use Mark\Doorstroming\Domain\UploadedFileHandler;
use Mark\Doorstroming\Domain\UploadedFileId;
use Mark\Doorstroming\Infrastructure\PhpSpreadsheet\SpreadsheetGenerator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

final class DoorstromingController extends AbstractController
{
    private ?array $parsedScoreSheets;

    private Filesystem $fileSystem;

    private string $uploadDir;

    public function __construct(string $uploadDir)
    {
        $this->uploadDir  = $uploadDir;
        $this->fileSystem = new Filesystem(new Local($this->uploadDir));
    }

    /**
     * @Route("/", name="index", methods={"GET", "POST"})
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request): Response
    {
        $errors = [];

        if ($request->getMethod() === Request::METHOD_POST) {
            try {
                [$firstUploadedFileId, $secondUploadedFileId] = $this->processFileUploads(
                    $request->files->get('FirstCompetitionScores'),
                    $request->files->get('SecondCompetitionScores')
                );
                $this->checkParsedUploads($firstUploadedFileId, $secondUploadedFileId);
            } catch (LogicException $exception) {
                $errors[] = $exception->getMessage();
            }

            if (!$errors) {
                return $this->redirectToRoute(
                    'askNumbers',
                    [
                        'firstCompetitionId'  => $firstUploadedFileId->toString(),
                        'secondCompetitionId' => $secondUploadedFileId->toString(),
                    ]
                );
            }
        }

        return $this->render('index.html.twig', ['errors' => $errors]);
    }

    /**
     * @Route("/askNumbers/{firstCompetitionId}/{secondCompetitionId}", name="askNumbers", methods={"GET", "POST"})
     * @param Request $request
     * @param string  $firstCompetitionId
     * @param string  $secondCompetitionId
     *
     * @return Response
     */
    public function askNumbers(Request $request, string $firstCompetitionId, string $secondCompetitionId): Response
    {
        $errors = [];
        if ($request->getMethod() === Request::METHOD_POST) {
            try {
                return $this->generateExcelFiles($request, $firstCompetitionId, $secondCompetitionId);
            } catch (LogicException $exception) {
                $errors[] = $exception->getMessage();
            }
        }
        $firstFileId                      = UploadedFileId::fromString($firstCompetitionId);
        $totalScoreSheetsFirstCompetition = $this->retrieveScoreSheets($firstFileId, 'total');

        return $this->render(
            'ask_numbers.html.twig',
            [
                'categoryLevels'      => $this->generateAskNumbersParameters($totalScoreSheetsFirstCompetition),
                'errors'              => $errors,
                'firstCompetitionId'  => $firstCompetitionId,
                'secondCompetitionId' => $secondCompetitionId,
            ]
        );
    }

    /**
     * @Route("/downloadFile/{fileName}", name="downloadSpecificFile", methods={"GET"})
     * @param string $fileName
     *
     * @return Response
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function downloadSpecificFile(string $fileName): Response
    {
        $spreadsheet      = IOFactory::load($this->uploadDir . $fileName);
        $streamedResponse = new StreamedResponse();
        $streamedResponse->setCallback(
            function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            }
        );

        $streamedResponse->setStatusCode(200);
        $streamedResponse->headers->set(
            'Content-Type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
        $streamedResponse->headers->set(
            'Content-Disposition',
            'attachment; filename="' . $fileName . '"'
        );
        return $streamedResponse->send();
    }

    /**
     * @Route("/downloadFiles/{firstCompetitionId}/{secondCompetitionId}", name="downloadFiles", methods={"GET"})
     * @param string $firstCompetitionId
     * @param string $secondCompetitionId
     *
     * @return Response
     */
    public function downloadFiles(string $firstCompetitionId, string $secondCompetitionId): Response
    {
        $allDoorstromingFiles = unserialize(
            file_get_contents($this->uploadDir . $firstCompetitionId . '-' . $secondCompetitionId)
        );

        return $this->render('download_files.html.twig', ['doorstromingFiles' => $allDoorstromingFiles]);
    }

    private function generateExcelFiles(Request $request, string $firstCompetitionId, string $secondCompetitionId): Response
    {
        $firstFileId  = UploadedFileId::fromString($firstCompetitionId);
        $secondFileId = UploadedFileId::fromString($secondCompetitionId);

        $totalDoorstromingen = [];
        $vaultDoorstromingen = [];
        $barDoorstromingen   = [];
        $beamDoorstromingen  = [];
        $floorDoorstromingen = [];
        $totalSheet          = $this->retrieveScoreSheets($firstFileId, 'total');
        foreach ($totalSheet->getCategoryLevelCombinations()->toArray() as $categoryLevelCombination) {

            $districtSpots         = (int) $request->request->get(
                str_replace(
                    ' ',
                    '_',
                    $categoryLevelCombination->category()->toString() . '-'
                    . $categoryLevelCombination->level()->toString() . '-district'
                )
            );
            $districtReserveSpots  = (int) $request->request->get(
                str_replace(
                    ' ',
                    '_',
                    $categoryLevelCombination->category()->toString() . '-'
                    . $categoryLevelCombination->level()->toString() . '-district-reserve'
                )
            );
            $districtExtraSpots    = (int) $request->request->get(
                str_replace(
                    ' ',
                    '_',
                    $categoryLevelCombination->category()->toString() . '-'
                    . $categoryLevelCombination->level()->toString() . '-district-extra'
                )
            );
            $nationalSpots         = (int) $request->request->get(
                str_replace(
                    ' ',
                    '_',
                    $categoryLevelCombination->category()->toString() . '-'
                    . $categoryLevelCombination->level()->toString() . '-national'
                )
            );
            $nationalExtraSpots    = (int) $request->request->get(
                str_replace(
                    ' ',
                    '_',
                    $categoryLevelCombination->category()->toString() . '-'
                    . $categoryLevelCombination->level()->toString() . '-national-extra'
                )
            );
            $apparatusSpots        = (int) $request->request->get(
                str_replace(
                    ' ',
                    '_',
                    $categoryLevelCombination->category()->toString() . '-'
                    . $categoryLevelCombination->level()->toString() . '-apparatus'
                )
            );
            $apparatusReserveSpots = (int) $request->request->get(
                str_replace(
                    ' ',
                    '_',
                    $categoryLevelCombination->category()->toString() . '-'
                    . $categoryLevelCombination->level()->toString() . '-apparatus-reserve'
                )
            );
            $apparatusExtraSpots   = (int) $request->request->get(
                str_replace(
                    ' ',
                    '_',
                    $categoryLevelCombination->category()->toString() . '-'
                    . $categoryLevelCombination->level()->toString() . '-apparatus-extra'
                )
            );

            $totalDoorstromingen[] = $this->createDoorstromingenForApparatusAndCategoryLevel(
                $firstFileId,
                $secondFileId,
                'total',
                $categoryLevelCombination,
                $districtSpots,
                $districtReserveSpots,
                $districtExtraSpots,
                $nationalSpots,
                $nationalExtraSpots
            );
            $vaultDoorstromingen[] = $this->createDoorstromingenForApparatusAndCategoryLevel(
                $firstFileId,
                $secondFileId,
                'vault',
                $categoryLevelCombination,
                $apparatusSpots,
                $apparatusReserveSpots,
                $apparatusExtraSpots,
                0,
                0
            );
            $barDoorstromingen[]   = $this->createDoorstromingenForApparatusAndCategoryLevel(
                $firstFileId,
                $secondFileId,
                'bar',
                $categoryLevelCombination,
                $apparatusSpots,
                $apparatusReserveSpots,
                $apparatusExtraSpots,
                0,
                0
            );
            $beamDoorstromingen[]  = $this->createDoorstromingenForApparatusAndCategoryLevel(
                $firstFileId,
                $secondFileId,
                'beam',
                $categoryLevelCombination,
                $apparatusSpots,
                $apparatusReserveSpots,
                $apparatusExtraSpots,
                0,
                0
            );
            $floorDoorstromingen[] = $this->createDoorstromingenForApparatusAndCategoryLevel(
                $firstFileId,
                $secondFileId,
                'floor',
                $categoryLevelCombination,
                $apparatusSpots,
                $apparatusReserveSpots,
                $apparatusExtraSpots,
                0,
                0
            );
        }

        $nationalDoorstromingFiles = SpreadSheetGenerator::generate(
            $totalDoorstromingen,
            'Landelijke meerkamp',
            CompetitionType::NATIONAL(),
            $this->uploadDir
        );
        $districtDoorstromingFiles = SpreadSheetGenerator::generate(
            $totalDoorstromingen,
            'Districtsfinale',
            CompetitionType::DISTRICT(),
            $this->uploadDir
        );
        $vaultDoorstromingFiles    = SpreadSheetGenerator::generate(
            $vaultDoorstromingen,
            'Toestelfinale sprong',
            CompetitionType::DISTRICT(),
            $this->uploadDir
        );
        $barDoorstromingFiles      = SpreadSheetGenerator::generate(
            $barDoorstromingen,
            'Toestelfinale brug',
            CompetitionType::DISTRICT(),
            $this->uploadDir
        );
        $beamDoorstromingFiles     = SpreadSheetGenerator::generate(
            $beamDoorstromingen,
            'Toestelfinale balk',
            CompetitionType::DISTRICT(),
            $this->uploadDir
        );
        $floorDoorstromingFiles    = SpreadSheetGenerator::generate(
            $floorDoorstromingen,
            'Toestelfinale vloer',
            CompetitionType::DISTRICT(),
            $this->uploadDir
        );

        $allDoorstromingFiles = [];
        if (!empty($nationalDoorstromingFiles)) {
            $allDoorstromingFiles['Nationale doorstroming'] = $nationalDoorstromingFiles;
        }
        if (!empty($districtDoorstromingFiles)) {
            $allDoorstromingFiles['Districtsfinale'] = $districtDoorstromingFiles;
        }
        if (!empty($vaultDoorstromingFiles)) {
            $allDoorstromingFiles['Sprong finale'] = $vaultDoorstromingFiles;
        }
        if (!empty($barDoorstromingFiles)) {
            $allDoorstromingFiles['Brug finale'] = $barDoorstromingFiles;
        }
        if (!empty($beamDoorstromingFiles)) {
            $allDoorstromingFiles['Balk finale'] = $beamDoorstromingFiles;
        }
        if (!empty($floorDoorstromingFiles)) {
            $allDoorstromingFiles['Vloer finale'] = $floorDoorstromingFiles;
        }

        file_put_contents(
            $this->uploadDir . $firstCompetitionId . '-' . $secondCompetitionId,
            serialize($allDoorstromingFiles)
        );

        return new RedirectResponse(
            $this->generateUrl(
                'downloadFiles',
                [
                    'firstCompetitionId'  => $firstCompetitionId,
                    'secondCompetitionId' => $secondCompetitionId
                ]
            )
        );
    }

    /**
     * @param UploadedFile $firstCompetitionScores
     * @param UploadedFile $secondCompetitionScores
     *
     * @return UploadedFileId[]
     */
    private function processFileUploads(UploadedFile $firstCompetitionScores, UploadedFile $secondCompetitionScores): array
    {
        $fileHandler = new UploadedFileHandler(
            $this->uploadDir,
            [
                'text/csv',
                'application/csv',
                'text/x-csv',
                'application/x-csv',
                'text/x-comma-separated-values',
                'text/comma-separated-values',
                'text/plain',
            ]
        );
        try {
            $firstUploadedFileId = $fileHandler->handle($firstCompetitionScores, 1);
        } catch (LogicException $exception) {
            throw new LogicException(
                sprintf(
                    'An exception occured while handling the file upload of the first competition scores: "%s"',
                    $exception->getMessage()
                )
            );
        }

        try {
            $secondUploadedFileId = $fileHandler->handle($secondCompetitionScores, 2);
        } catch (LogicException $exception) {
            throw new LogicException(
                sprintf(
                    'An exception occured while handling the file upload of the second competition scores: "%s"',
                    $exception->getMessage()
                )
            );
        }

        return [$firstUploadedFileId, $secondUploadedFileId];
    }

    private function checkParsedUploads(UploadedFileId $firstUploadedFileId, UploadedFileId $secondUploadedFileId): void
    {
        $firstCompetitionScoreSheetsForTotal  = $this->retrieveScoreSheets($firstUploadedFileId, 'total');
        $secondCompetitionScoreSheetsForTotal = $this->retrieveScoreSheets($secondUploadedFileId, 'total');

        $identifiersFirstSheets  = $firstCompetitionScoreSheetsForTotal->getAllIdentifiers();
        $identifiersSecondSheets = $secondCompetitionScoreSheetsForTotal->getAllIdentifiers();
        if ($identifiersFirstSheets === $identifiersSecondSheets) {
            return;
        }

        $this->removeFileById($firstUploadedFileId);
        $this->removeFileById($secondUploadedFileId);

        throw new LogicException(
            sprintf(
                'The identifiers for the first sheet differ from the identifiers from the second sheet. First sheet contains "%s", second sheet contains "%s"',
                implode(', ', $identifiersFirstSheets),
                implode(', ', $identifiersSecondSheets),
            )
        );
    }

    private function retrieveScoreSheets(UploadedFileId $fileId, string $apparatus): ScoreSheets
    {
        if (!isset($this->parsedScoreSheets[$fileId->toString()])) {
            $this->parsedScoreSheets[$fileId->toString()] = unserialize(
                file_get_contents($this->getFileLocationFromId($fileId))
            );
        }

        return $this->parsedScoreSheets[$fileId->toString()][$apparatus];
    }

    private function removeScoreSheets(UploadedFileId $fileId): void
    {
        unlink($this->getFileLocationFromId($fileId));
    }

    private function getFileLocationFromId(UploadedFileId $fileId): string
    {
        return $this->uploadDir . $fileId->toString();
    }

    private function removeFileById(UploadedFileId $fileId): void
    {
        unlink($this->getFileLocationFromId($fileId));
    }

    private function createDoorstromingenForApparatusAndCategoryLevel(
        UploadedFileId $firstFileId,
        UploadedFileId $secondFileId,
        string $apparatus,
        CategoryLevelCombination $categoryLevelCombination,
        int $districtSpotsPerGroup,
        int $districtReserveSpotsPerGroup,
        int $districtExtraSpots,
        int $nationalSpotsPerGroup,
        int $nationalExtraSpots
    ): CategoryLevelSpecificDoorstromingLists
    {
        $scoreSheetsFirstCompetition  = $this->retrieveScoreSheets($firstFileId, $apparatus);
        $scoreSheetsSecondCompetition = $this->retrieveScoreSheets($secondFileId, $apparatus);

        return CategoryLevelSpecificDoorstromingListsCreator::create(
            $categoryLevelCombination,
            ScoreSheets::create(
                $scoreSheetsFirstCompetition->findByCategoryLevelCombination($categoryLevelCombination)
            ),
            ScoreSheets::create(
                $scoreSheetsSecondCompetition->findByCategoryLevelCombination($categoryLevelCombination)
            ),
            $districtSpotsPerGroup,
            $districtReserveSpotsPerGroup,
            $districtExtraSpots,
            $nationalSpotsPerGroup,
            $nationalExtraSpots
        );
    }

    private function generateAskNumbersParameters(ScoreSheets $scoreSheets): array
    {
        $parameters = [];
        foreach ($scoreSheets->getCategoryLevelCombinations()->toArray()
                 as $categoryLevelCombination) {
            $specificScoreSheets         = ScoreSheets::create(
                $scoreSheets->findByCategoryLevelCombination($categoryLevelCombination)
            );
            $districtNumberOfExtraSpots  = 36 % count($specificScoreSheets->getAllIdentifiers());
            $districtNumberOfSpots       = (36 - $districtNumberOfExtraSpots) /
                count($specificScoreSheets->getAllIdentifiers());
            $apparatusNumberOfExtraSpots = 10 % count($specificScoreSheets->getAllIdentifiers());
            $apparatusNumberOfSpots      = (10 - $apparatusNumberOfExtraSpots) /
                count($specificScoreSheets->getAllIdentifiers());
            if ($apparatusNumberOfExtraSpots === 1) {
                $apparatusNumberOfExtraSpots = 0;
            }
            $parameters[] = [
                'category'                      => $categoryLevelCombination->category()->toString(),
                'level'                         => $categoryLevelCombination->level()->toString(),
                'identifiers'                   => $specificScoreSheets->getAllIdentifiers(),
                'districtNumberOfSpots'         => $districtNumberOfSpots,
                'districtNumberOfExtraSpots'    => $districtNumberOfExtraSpots,
                'districtNumberOfReserveSpots'  => 0,
                'apparatusNumberOfSpots'        => $apparatusNumberOfSpots,
                'apparatusNumberOfExtraSpots'   => $apparatusNumberOfExtraSpots,
                'apparatusNumberOfReserveSpots' => 2,
                'nationalSpots'                 => 0,
                'nationalExtraSpots'            => 0,
            ];
        }

        return $parameters;
    }
}
