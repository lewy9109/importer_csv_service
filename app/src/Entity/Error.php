<?php

namespace App\Entity;

use App\Repository\ErrorRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ErrorRepository::class)]
class Error
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'errors', cascade: ['persist', 'remove'])]
    private ?ReportImport $report = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $message = null;

    #[ORM\Column(nullable: true)]
    private ?int $rowId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReport(): ?ReportImport
    {
        return $this->report;
    }

    public function setReport(?ReportImport $report): static
    {
        $this->report = $report;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getRowId(): ?int
    {
        return $this->rowId;
    }

    public function setRowId(?int $rowId): static
    {
        $this->rowId = $rowId;

        return $this;
    }
}
