<?php

namespace App\Message;

class CreatedReportImportFile
{
    public function __construct(private readonly string $raportBusinessId)
    {}

    public function getRaportBusinessId(): string
    {
        return $this->raportBusinessId;
    }
}