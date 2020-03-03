<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Infrastructure\SymfonyController;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Mark\Doorstroming\Domain\CategoryLevelCombination;
use Mark\Doorstroming\Domain\CategoryLevelSpecificDoorstromingLists;
use Mark\Doorstroming\Domain\CategoryLevelSpecificDoorstromingListsCreator;
use Mark\Doorstroming\Domain\ScoreSheets;
use Mark\Doorstroming\Domain\UploadedFileHandler;
use Mark\Doorstroming\Domain\UploadedFileId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DoorstromingController extends AbstractController
{
    private ?array $parsedScoreSheets;

    private Filesystem $fileSystem;

    public function __construct()
    {
        $this->fileSystem = new Filesystem(new Local($this->getUploadDirectory()));
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
            } catch (\LogicException $exception) {
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
                $this->generateExcelFiles($request, $firstCompetitionId, $secondCompetitionId);
            } catch (\LogicException $exception) {
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
     * @Route("/downloadFiles/{firstCompetitionId}/{secondCompetitionId}", name="downloadFiles", methods={"GET"})
     * @param string $firstCompetitionId
     * @param string $secondCompetitionId
     *
     * @return Response
     */
    public function downloadFiles(string $firstCompetitionId, string $secondCompetitionId): Response
    {
        // todo: als er nog aanwijsplekken over zijn bij een categorie niveau combinatie, opmerking bij de download link plaatsen
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
                $categoryLevelCombination->category()->toString() . '-'
                . $categoryLevelCombination->level()->toString() . '-district'
            );
            $districtReserveSpots  = (int) $request->request->get(
                $categoryLevelCombination->category()->toString() . '-'
                . $categoryLevelCombination->level()->toString() . '-district-reserve'
            );
            $districtExtraSpots    = (int) $request->request->get(
                $categoryLevelCombination->category()->toString() . '-'
                . $categoryLevelCombination->level()->toString() . '-district-extra'
            );
            $nationalSpots         = (int) $request->request->get(
                $categoryLevelCombination->category()->toString() . '-'
                . $categoryLevelCombination->level()->toString() . '-national'
            );
            $nationalExtraSpots    = (int) $request->request->get(
                $categoryLevelCombination->category()->toString() . '-'
                . $categoryLevelCombination->level()->toString() . '-national-extra'
            );
            $apparatusSpots        = (int) $request->request->get(
                $categoryLevelCombination->category()->toString() . '-'
                . $categoryLevelCombination->level()->toString() . '-apparatus'
            );
            $apparatusReserveSpots = (int) $request->request->get(
                $categoryLevelCombination->category()->toString() . '-'
                . $categoryLevelCombination->level()->toString() . '-apparatus-reserve'
            );
            $apparatusExtraSpots   = (int) $request->request->get(
                $categoryLevelCombination->category()->toString() . '-'
                . $categoryLevelCombination->level()->toString() . '-apparatus-extra'
            );
            $totalDoorstromingen[] = [
                'categoryLevel' => $categoryLevelCombination,
                'list'          => $this->createDoorstromingenForApparatusAndCategoryLevel(
                    $firstFileId,
                    $secondFileId,
                    'total',
                    $categoryLevelCombination,
                    $districtSpots,
                    $districtReserveSpots,
                    $districtExtraSpots,
                    $nationalSpots,
                    $nationalExtraSpots
                )
            ];
            $vaultDoorstromingen[] = [
                'categoryLevel' => $categoryLevelCombination,
                'list'          => $this->createDoorstromingenForApparatusAndCategoryLevel(
                    $firstFileId,
                    $secondFileId,
                    'vault',
                    $categoryLevelCombination,
                    $apparatusSpots,
                    $apparatusReserveSpots,
                    $apparatusExtraSpots,
                    $nationalSpots,
                    $nationalExtraSpots
                )
            ];
            $barDoorstromingen[]   = [
                'categoryLevel' => $categoryLevelCombination,
                'list'          => $this->createDoorstromingenForApparatusAndCategoryLevel(
                    $firstFileId,
                    $secondFileId,
                    'bar',
                    $categoryLevelCombination,
                    $apparatusSpots,
                    $apparatusReserveSpots,
                    $apparatusExtraSpots,
                    $nationalSpots,
                    $nationalExtraSpots
                )
            ];
            $beamDoorstromingen[]  = [
                'categoryLevel' => $categoryLevelCombination,
                'list'          => $this->createDoorstromingenForApparatusAndCategoryLevel(
                    $firstFileId,
                    $secondFileId,
                    'beam',
                    $categoryLevelCombination,
                    $apparatusSpots,
                    $apparatusReserveSpots,
                    $apparatusExtraSpots,
                    $nationalSpots,
                    $nationalExtraSpots
                )
            ];
            $floorDoorstromingen[] = [
                'categoryLevel' => $categoryLevelCombination,
                'list'          => $this->createDoorstromingenForApparatusAndCategoryLevel(
                    $firstFileId,
                    $secondFileId,
                    'floor',
                    $categoryLevelCombination,
                    $apparatusSpots,
                    $apparatusReserveSpots,
                    $apparatusExtraSpots,
                    $nationalSpots,
                    $nationalExtraSpots
                )
            ];
        }

        /** @var CategoryLevelSpecificDoorstromingLists $list */
        foreach ($barDoorstromingen as $categoryLevel) {
            foreach ($categoryLevel['list']->doorstromingLists() as $list) {
                var_dump($list);
                die;
            }
        }

        // todo: excel bestanden genereren (skippen bij aantal plekken 0)
        // todo: bestanden District meerkamp: Totaallijst, Lijst met alleen reserves (met identifier), lijst met alleen de doorstromers (met identifier)
        // todo: bestanden Landelijk meerkamp: Totaallijst, Lijst met alleen reserves (met identifier), lijst met alleen de doorstromers (met identifier)
        // todo: bestanden District toestelfinale per toestel een file: Totaallijst, Lijst met alleen reserves (met identifier), lijst met alleen de doorstromers (met identifier)
        // todo: result array (zie onderstaand) opslaan als file (id1-id2) en redirectToRoute met id's. Deze route kunnen ze een week gebruiken voor downloaden files
        // todo: alleen file genereren en aan de lijst toevoegen als er plekken beschikbaar zijn
        // todo: route om bestand op te halen en te outputten
        [
            'Nationale doorstroming' => [
                'Volledige lijsten'                              => 'filename',
                'Reserves'                                       => 'filename',
                'Lijsten met alleen de doorgestroomde turnsters' => 'filename'
            ],
            'Districtsfinale'        => [
                'Volledige lijsten'                              => 'filename',
                'Reserves'                                       => 'filename',
                'Lijsten met alleen de doorgestroomde turnsters' => 'filename'
            ],
            'Sprong finale'          => [
                'Volledige lijsten'                              => 'filename',
                'Reserves'                                       => 'filename',
                'Lijsten met alleen de doorgestroomde turnsters' => 'filename'
            ],
            'Brug finale'            => [
                'Volledige lijsten'                              => 'filename',
                'Reserves'                                       => 'filename',
                'Lijsten met alleen de doorgestroomde turnsters' => 'filename'
            ],
            'Balk finale'            => [
                'Volledige lijsten'                              => 'filename',
                'Reserves'                                       => 'filename',
                'Lijsten met alleen de doorgestroomde turnsters' => 'filename'
            ],
            'Vloer finale'           => [
                'Volledige lijsten'                              => 'filename',
                'Reserves'                                       => 'filename',
                'Lijsten met alleen de doorgestroomde turnsters' => 'filename'
            ],
        ];
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
            $this->getUploadDirectory(),
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
        } catch (\LogicException $exception) {
            throw new \LogicException(
                sprintf(
                    'An exception occured while handling the file upload of the first competition scores: "%s"',
                    $exception->getMessage()
                )
            );
        }

        try {
            $secondUploadedFileId = $fileHandler->handle($secondCompetitionScores, 2);
        } catch (\LogicException $exception) {
            throw new \LogicException(
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

        throw new \LogicException(
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

    private function getFileLocationFromId(UploadedFileId $fileId): string
    {
        return $this->getParameter('upload_dir') . $fileId->toString();
    }

    private function getUploadDirectory(): string
    {
        return $this->getParameter('upload_dir');
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
