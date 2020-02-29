<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Infrastructure\SymfonyController;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Mark\Doorstroming\Domain\UploadedFileId;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

final class DoorstromingController
{
    /**
     * @var Environment
     */
    private Environment $twig;

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    public function __construct(Environment $twig)
    {
        $this->twig       = $twig;
        $adapter          = new Local('var/uploadedScores');
        $this->filesystem = new Filesystem($adapter);
    }

    /**
     * @Route("/", name="index", methods={"GET", "POST"})
     */
    public function index(Request $request)
    {
        $allowedMimeTypes = [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        if ($request->getMethod() === Request::METHOD_POST) {
            /** @var UploadedFile $firstCompetitionScores */
            $firstCompetitionScores = $request->files->get('FirstCompetitionScores');
            /** @var UploadedFile $secondCompetitionScores */
            $secondCompetitionScores = $request->files->get('SecondCompetitionScores');
            if (!$firstCompetitionScores || !$secondCompetitionScores) {

                // todo: foutmelding
                return new Response($this->twig->render('index.html.twig'));
            }
            if (!in_array($firstCompetitionScores->getMimeType(), $allowedMimeTypes) ||
                !in_array($secondCompetitionScores->getMimeType(), $allowedMimeTypes)) {

                // todo: foutmelding
                return new Response($this->twig->render('index.html.twig'));
            }

            $firstUploadedFileId = UploadedFileId::generate();
            $secondUploadedFileId = UploadedFileId::generate();
            $firstUploadedFileName  = $firstUploadedFileId->toString() . '.' . $firstCompetitionScores->guessExtension();
            $secondUploadedFileName = $secondUploadedFileId->toString() . '.' . $secondCompetitionScores->guessExtension();
            $firstCompetitionScores->move('../var/uploadedScores', $firstUploadedFileName);
            $secondCompetitionScores->move('../var/uploadedScores', $secondUploadedFileName);
        }

        return new Response($this->twig->render('index.html.twig'));
    }
}
