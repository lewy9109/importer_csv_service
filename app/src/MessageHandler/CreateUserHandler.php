<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\User;
use App\Message\CreateUsersFromImport;
use App\Repository\ReportImportRepository;
use App\Service\User\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CreateUserHandler
{
    private const BATCH_SIZE = 1000;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ReportImportRepository $reportImportRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function __invoke(CreateUsersFromImport $message): void
    {
        try{
            $counter = 0;
            $this->entityManager->beginTransaction();
            $report = $this->reportImportRepository->findOneBy(['businessId' => $message->getReportBusinessId()]);
            if (!$report) {
                $this->logger->error('ReportImport not found', [
                    'businessId' =>  $message->getReportBusinessId()
                ]);
                $this->entityManager->rollback();
                $this->entityManager->clear();
                return;
            }

            foreach ($message->getUsers() as $user){
                $userDto = UserFactory::fromArray($user);

                $user = new User();
                $user
                    ->setEmail($userDto->getEmail())
                    ->setCity($userDto->getCity())
                    ->setFullName($userDto->getFullName());
                $this->entityManager->persist($user);

                $counter++;

                if ($counter % self::BATCH_SIZE === 0) {
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                    $counter = 0;
                }
            }

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->logger->error('Failed to create user from import, message: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
            ]);
        }
    }
}