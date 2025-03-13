<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\CreatedReportImportFile;
use App\Repository\ReportImportRepository;
use App\Service\Processor\CsvProcessor;
use Exception;
use Psr\Log\LoggerInterface;
use RedisException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ProcessReportHandler
{
    public function __construct(
        private readonly CsvProcessor           $normalizing,
        private readonly ReportImportRepository $reportImportRepo,
        private readonly LoggerInterface        $logger
    ) {
    }

    /**
     * @throws Exception
     */
    public function __invoke(CreatedReportImportFile $message): void
    {
        try{
            $report = $this->reportImportRepo->findOneBy(['businessId' => $message->getRaportBusinessId()]);
            $this->normalizing->process($report);
        }catch (Exception|RedisException $exception){
            $this->logger->error('Error while processing csv file',[
                'message' => $exception->getMessage(),
                'reportId' => $message->getRaportBusinessId()
            ]);
            throw $exception;
        }
    }
}