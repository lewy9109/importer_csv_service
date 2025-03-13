<?php

namespace App\Tests\Entity;

use App\Entity\Error;
use App\Entity\ReportEnums\Status;
use App\Entity\ReportImport;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class ReportImportTest extends TestCase
{

    public function testCreateEntity(): void
    {
        $report = new ReportImport();
        $statusEnum = Status::IN_PROGRESS;

        $report
            ->setStatus($statusEnum)
            ->setFilePath('/test')
            ->setProcessedRows(10000)
            ->setCreatedAt(new DateTimeImmutable('now'));

        $error = New Error();
        $error->setRowId(30);
        $error->setMessage('test');
        $report->addError($error);

        $this->assertSame(10000, $report->getProcessedRows());
        $this->assertSame(1, $report->getErrors()->count());
        $this->assertSame($statusEnum, $report->getStatus());
        $this->assertSame('/test', $report->getFilePath());
    }
}
