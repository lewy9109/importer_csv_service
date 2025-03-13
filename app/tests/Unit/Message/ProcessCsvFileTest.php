<?php

namespace App\Tests\Unit\Message;

use App\Message\CreatedReportImportFile;
use PHPUnit\Framework\TestCase;

class ProcessCsvFileTest extends TestCase
{

    public function testCreatedProcessCsvFileMessage(): void
    {
        $path = "/path/to/file.csv";
        $processCsvFile = new CreatedReportImportFile($path);

        $this->assertSame($path, $processCsvFile->getRaportBusinessId());
    }

}
