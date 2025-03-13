<?php

namespace App\Tests\Unit\MessageHandler;

use App\Message\CreatedReportImportFile;
use App\MessageHandler\ProcessReportHandler;
use App\Repository\ReportImportRepository;
use App\Service\Processor\CsvProcessor;
use App\Service\Raport\ReportDto;
use App\Service\RedisStorage\ReportStorage;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\MockObject\MockObject;

class ProcessCsvHandlerTest extends TestCase
{
    /**
     * @var ProcessReportHandler
     */
    private ProcessReportHandler $handler;

    /**
     * @var MockObject&ProcessReportHandler
     */
    private LoggerInterface $logger;

    /**
     * @var MockObject&CsvProcessor
     */
    private CsvProcessor $normalizing;

    private ReportImportRepository $reportStorage;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->normalizing = $this->createMock(CsvProcessor::class);
        $this->reportStorage = $this->createMock(ReportImportRepository::class);

        $this->handler = new ProcessReportHandler($this->normalizing, $this->reportStorage, $this->logger);
    }


    public function testHandlerThrowException(): void
    {
        $this->markTestSkipped();
        $this->normalizing->expects($this->never())->method('process');
        $this->reportStorage
            ->expects($this->once())
            ->method('findOneBy')
            ->with('idstorage')
            ->willThrowException(new \RedisException('test'));

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                'Error while processing csv file',
                [
                    'message' => 'test',
                    'reportId' => 'idstorage'
                ]
            );

        $this->expectException(\RedisException::class);
        $this->expectExceptionMessage('test');

        $this->handler->__invoke(new CreatedReportImportFile("idstorage"));
    }

}
