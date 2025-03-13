<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Normalize;

use App\Entity\ReportEnums\Status;
use App\Entity\ReportImport;
use App\Service\Exception\NormalizerException;
use App\Service\Processor\CsvProcessor;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CsvNormalizingProcessorTest extends TestCase
{
    private CsvProcessor $processor;
    private ValidatorInterface $validator;
    private EntityManagerInterface $entityManager;
    private MessageBusInterface $bus;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->bus = $this->createMock(MessageBusInterface::class);
        $this->processor = new CsvProcessor($this->validator, $this->bus,  $this->entityManager);
    }

    public function testProcessValidCsv(): void
    {
        $this->markTestSkipped();
        $csvContent = <<<CSV
            id,fullName,email,city
            1,John Doe,john.doe@example.com,New York
            2,Jane Smith,jane.smith@example.com,Los Angeles
            3,Bob Johnson,bob.johnson@example.com,Chicago
        CSV;

        $csvFilePath = sys_get_temp_dir() . '/test_valid.csv';
        file_put_contents($csvFilePath, $csvContent);

        $report = new ReportImport();
        $report
            ->setFilePath($csvFilePath)
            ->setStatus(Status::IN_PROGRESS)
            ->setCreatedAt(new DateTimeImmutable('now'));

        $this->processor->process($report);

        unlink($csvFilePath);
    }

    public function testProcessThrowsExceptionForMissingFile(): void
    {
        $report = new ReportImport();
        $report
            ->setFilePath('/non/existing/file.csv')
            ->setStatus(Status::IN_PROGRESS)
            ->setCreatedAt(new DateTimeImmutable('now'));

        $this->expectException(NormalizerException::class);
        $this->expectExceptionMessage('The given CSV file /non/existing/file.csv is empty or does not exist!');

        $this->processor->process($report);
    }

    public function testProcessThrowsExceptionForEmptyFile(): void
    {
        $emptyCsvFilePath = sys_get_temp_dir() . '/empty.csv';
        touch($emptyCsvFilePath);

        $report = new ReportImport();
        $report
            ->setFilePath($emptyCsvFilePath)
            ->setStatus(Status::IN_PROGRESS)
            ->setCreatedAt(new DateTimeImmutable('now'));

        $this->expectException(NormalizerException::class);
        $this->expectExceptionMessage(sprintf('The given CSV file %s is empty or does not exist!', $emptyCsvFilePath));

        $this->processor->process($report);

        unlink($emptyCsvFilePath);
    }

    public function testProcessHandlesBatching(): void
    {
        $this->markTestSkipped();
        $csvContent = "id,fullName,email,city\n";
        for ($i = 1; $i <= 1000; $i++) {
            $csvContent .= "$i,User$i,user$i@example.com,City$i\n";
        }

        $csvFilePath = sys_get_temp_dir() . '/batch_test.csv';
        file_put_contents($csvFilePath, $csvContent);

        $report = new ReportDto(
            id: 'batchTest',
            filePath: $csvFilePath,
            status: 'pending',
            createdAt: '2025-02-17 12:00:00',
            startTime: '2025-02-17 12:01:00'
        );


        $this->processor->process($report);

        unlink($csvFilePath);
    }
}
