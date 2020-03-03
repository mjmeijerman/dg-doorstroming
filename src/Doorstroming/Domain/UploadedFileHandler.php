<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class UploadedFileHandler
{
    private string $storageLocation;

    private array $allowedMimeTypes;

    public function __construct(string $storageLocation, array $allowedMimeTypes)
    {
        $this->storageLocation  = $storageLocation;
        $this->allowedMimeTypes = $allowedMimeTypes;
    }

    public function handle(UploadedFile $file, int $fileNumber): UploadedFileId
    {
        if (!in_array($file->getMimeType(), $this->allowedMimeTypes)) {
            throw new \LogicException('Het bestand moet een CSV-bestand zijn');
        }

        $uploadedFileId = UploadedFileId::generate();
        $fileName       = $uploadedFileId->toString() . '.' . $file->guessExtension();
        $file->move($this->storageLocation, $fileName);

        $parsedFile = CsvScoreSheetParser::parse($this->storageLocation . $fileName, $fileNumber);
        file_put_contents($this->storageLocation . $uploadedFileId->toString(), serialize($parsedFile));

        unlink($this->storageLocation . $fileName);

        return $uploadedFileId;
    }
}
