<?php

declare(strict_types=1);

namespace App\Service\Processor;

use App\Entity\Error;
use App\Entity\ReportEnums\Status;
use App\Entity\ReportImport;
use App\Message\CreatedReportImportFile;
use App\Message\CreateUsersFromImport;
use App\Service\Exception\NormalizerException;
use App\Service\User\UserDto;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;

class CsvProcessor implements ProcessorInterface
{
    private const CHUNK_SIZE = 5000;
    private const BATCH_SIZE = 2000;

    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly MessageBusInterface $messageBus,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @param ReportImport $report
     *
     * @throws Exception
     */
    public function process(ReportImport &$report): void
    {
        try {
            $sourceFeed = $report->getFilePath();
            if (!file_exists($sourceFeed) || filesize($sourceFeed) === 0) {
                throw new NormalizerException(sprintf('The given CSV file %s is empty or does not exist!', $sourceFeed));
            }

            $file = new \SplFileObject($sourceFeed);
            $file->setFlags(\SplFileObject::READ_CSV);

            $this->entityManager->beginTransaction();
            $currentRow = $report->getProcessedRows() ?? 1;
            $file->seek($currentRow);
            $users = [];
            $userRows = 0;
            $rows = 1;
            while ($row = $file->current()) {
                if($row[0] === null) {
                    $this->dispatchUsers($users, $report->getBusinessId());
                    $this->endProcess(Status::SUCCESS, $report, $currentRow);
                    return;
                }
                if (count($row) < 4) {
                    $currentRow++;
                    $rows ++;
                    continue;
                }

                [$id, $fullName, $email, $city] = array_map('trim', $row);
                $processDataDTO = new UserDto($currentRow, $fullName, $email, $city, $report->getBusinessId());
                $violations = $this->validator->validate($processDataDTO);

                if (count($violations) > 0) {
                    $this->handleViolations($violations, $report, $currentRow);
                    unset($violations);
                } else {
                    $users[] = $processDataDTO->toArray();
                    $userRows++;
                    unset($processDataDTO);
                }

                if ($userRows >= self::BATCH_SIZE) {
                    $this->dispatchUsers($users, $report->getBusinessId());
                    $users = [];
                    $userRows = 0;
                }

                if ($rows >= self::CHUNK_SIZE) {
                    $this->endProcess(Status::PARTIAL, $report, $currentRow);
                    $this->messageBus->dispatch(new CreateUsersFromImport($users, $report->getBusinessId()));
                    $this->messageBus->dispatch(new CreatedReportImportFile($report->getBusinessId()));
                    return;
                }
                unset($row, $processDataDTO, $violations);

                $currentRow++;
                $rows ++;
                $file->next();
            }
        } catch (Throwable $e) {
            $this->endProcess(Status::ERROR, $report, $currentRow ?? null);
            throw new NormalizerException(sprintf('Error: %s', $e->getMessage()));
        }
    }

    private function handleViolations(ConstraintViolationListInterface $violations, ReportImport $reportImport, int $currentRow): void
    {
        foreach ($violations as $violation) {
            $error = new Error();
            $error
                ->setRowId($currentRow)
                ->setReport($reportImport)
                ->setMessage($violation->getMessage());

            $this->entityManager->persist($error);
        }

        $this->entityManager->flush();
    }

    private function endProcess(Status $status, ReportImport $report, ?int $processedRows): void
    {
        if ($processedRows) {
            $report->setProcessedRows(++$processedRows);
        }
        $report->setStatus($status);
        $report->setEndTime(new \DateTimeImmutable('now'));
        $this->entityManager->flush();
        $this->entityManager->commit();
        $this->entityManager->clear();
    }

    private function dispatchUsers(array $users, string $reportBusinessId): void
    {
        if (!empty($users)) {
            $this->messageBus->dispatch(new CreateUsersFromImport($users, $reportBusinessId));
        }
    }
}
