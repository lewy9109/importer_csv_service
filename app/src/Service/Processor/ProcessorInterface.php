<?php

namespace App\Service\Processor;

use App\Entity\ReportImport;

interface ProcessorInterface
{
    public function process(ReportImport &$report): void;
}