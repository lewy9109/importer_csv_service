<?php

namespace App\Service;

use App\Entity\ReportEnums\Status;
use App\Entity\ReportImport;
use App\Message\CreatedReportImportFile;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;

class Uploader
{
    public function __construct(
        private readonly string $uploadsPath,
        private readonly Filesystem $filesystem,
        private readonly MessageBusInterface $messageBus,
        private readonly EntityManagerInterface $entityManager,
    ) {
        if (!$this->filesystem->exists($this->uploadsPath)) {
            $this->filesystem->mkdir($this->uploadsPath, 777);
        }
    }

    /**
     * @param UploadedFile $chunk
     * @param string       $fileName
     * @param int          $chunkIndex
     * @param int          $totalChunks
     *
     * @return array<string>
     */
    public function saveChunk(
        UploadedFile $chunk,
        string $fileName,
        int $chunkIndex,
        int $totalChunks
    ): array {
        $chunk->move($this->uploadsPath, "{$fileName}_part_{$chunkIndex}");

        if ($chunkIndex === $totalChunks - 1) {
            return $this->mergeChunks($fileName, $totalChunks);
        }

        return ["status" => "saved"];
    }

    /**
     * @param string $fileName
     * @param int    $totalChunks
     *
     * @return string[]
     */
    private function mergeChunks(string $fileName, int $totalChunks): array
    {
        $finalFile = $this->uploadsPath . $fileName;
        $output = fopen($finalFile, "wb");

        if ($output === false) {
            return [
                "status" => "error",
                "message" => "Cannot open file",
            ];
        }

        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkPath = $this->uploadsPath . "{$fileName}_part_{$i}";

            if (!file_exists($chunkPath)) {
                return [
                    "status" => "error",
                    "message" => "Missing chunk {$i}",
                ];
            }

            $input = fopen($chunkPath, "rb");
            if ($input === false) {
                fclose($output);
                return [
                    "status" => "error",
                    "message" => "Cannot open the chunk {$i}",
                ];
            }

            stream_copy_to_stream($input, $output);
            fclose($input);
            unlink($chunkPath);
        }

        fclose($output);

        $raportId = $this->createRaport($finalFile);

        $this->messageBus->dispatch(new CreatedReportImportFile($raportId));

        return ["status" => "completed", "file" => $finalFile, "raportId" => $raportId];
    }

    private function createRaport(string $finalFile): string
    {
        $report = new ReportImport();
        $report
            ->setFilePath($finalFile)
            ->setStatus(Status::IN_PROGRESS)
            ->setCreatedAt(new DateTimeImmutable('now'));

        $this->entityManager->persist($report);
        $this->entityManager->flush();

        return $report->getBusinessId();
    }
}
