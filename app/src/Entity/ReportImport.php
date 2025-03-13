<?php

namespace App\Entity;

use App\Entity\ReportEnums\Status;
use App\Repository\ReportImportRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ReportImportRepository::class)]
#[ORM\Index(columns: ["business_id"])]
class ReportImport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $filePath = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $endTime = null;

    #[ORM\Column(nullable: true)]
    private ?int $processedRows = null;

    /**
     * @var Collection<int, Error>
     */
    #[ORM\OneToMany(targetEntity: Error::class, mappedBy: 'report')]
    private Collection $errors;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'report')]
    private Collection $users;

    #[ORM\Column(type: Types::TEXT, nullable: false)]
    private string $businessId;

    public function __construct(?Uuid $businessId = null)
    {
        $this->businessId = ($businessId === null) ? Uuid::v4()->toBase32() : $businessId->toBase32();

        $this->errors = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): static
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function getStatus(): Status
    {
        return Status::tryFrom($this->status);
    }

    public function setStatus(Status $status): static
    {
        $this->status = $status->value;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getProcessedRows(): ?int
    {
        return $this->processedRows;
    }

    public function setProcessedRows(?int $processedRows): static
    {
        $this->processedRows = $processedRows;

        return $this;
    }

    /**
     * @return Collection<int, Error>
     */
    public function getErrors(): Collection
    {
        return $this->errors;
    }

    public function addError(Error $error): static
    {
        if (!$this->errors->contains($error)) {
            $this->errors->add($error);
            $error->setReport($this);
        }

        return $this;
    }

    public function removeError(Error $error): static
    {
        if ($this->errors->removeElement($error)) {
            // set the owning side to null (unless already changed)
            if ($error->getReport() === $this) {
                $error->setReport(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setReport($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getReport() === $this) {
                $user->setReport(null);
            }
        }

        return $this;
    }

    public function getBusinessId(): string
    {
        return $this->businessId;
    }

    public function getEndTime(): ?\DateTimeImmutable
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTimeImmutable $endTime): ReportImport
    {
        $this->endTime = $endTime;
        return $this;
    }
}
