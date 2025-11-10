<?php
declare(strict_types=1);

namespace KimaiPlugin\LiePayrollBundle\Entity;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Mitarbeiterstammdaten für Lohnabrechnung
 */
#[ORM\Entity(repositoryClass: "KimaiPlugin\\LiePayrollBundle\\Repository\\PayrollUserProfileRepository")]
#[ORM\Table(name: "lie_payroll_user_profile")]
#[ORM\UniqueConstraint(name: "uniq_user", columns: ["user_id"])]
class PayrollUserProfile
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private User $user;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(type: "string", length: 20, nullable: true)]
    private ?string $postalCode = null;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $birthdate = null;

    #[ORM\Column(type: "string", length: 20, nullable: true)]
    private ?string $ahvNumber = null;

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $hireDate = null;

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $terminationDate = null;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $position = null;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $department = null;

    #[ORM\Column(type: "string", length: 20, nullable: true)]
    private ?string $maritalStatus = null; // ledig, verheiratet, geschieden, verwitwet

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private int $numberOfChildren = 0;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $municipality = null;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $taxMunicipality = null;

    #[ORM\Column(type: "string", length: 34, nullable: true)]
    private ?string $iban = null;

    #[ORM\Column(type: "integer", options: ["default" => 100])]
    private int $employmentLevel = 100; // in %

    #[ORM\Column(type: "datetime_immutable")]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: "datetime_immutable")]
    private \DateTimeImmutable $updatedAt;

    public function __construct(User $user)
    {
        $this->user = $user;
        $now = new \DateTimeImmutable("now");
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable("now");
    }

    // Getters & Setters
    public function getId(): ?int { return $this->id; }
    public function getUser(): User { return $this->user; }

    public function getAddress(): ?string { return $this->address; }
    public function setAddress(?string $address): self { $this->address = $address; $this->touch(); return $this; }

    public function getPostalCode(): ?string { return $this->postalCode; }
    public function setPostalCode(?string $postalCode): self { $this->postalCode = $postalCode; $this->touch(); return $this; }

    public function getCity(): ?string { return $this->city; }
    public function setCity(?string $city): self { $this->city = $city; $this->touch(); return $this; }

    public function getFullAddress(): string
    {
        $parts = array_filter([$this->address, $this->postalCode, $this->city]);
        return implode(', ', $parts);
    }

    public function getBirthdate(): ?\DateTimeInterface { return $this->birthdate; }
    public function setBirthdate(?\DateTimeInterface $birthdate): self { $this->birthdate = $birthdate; $this->touch(); return $this; }

    public function getAhvNumber(): ?string { return $this->ahvNumber; }
    public function setAhvNumber(?string $ahvNumber): self { $this->ahvNumber = $ahvNumber; $this->touch(); return $this; }

    public function getHireDate(): ?\DateTimeInterface { return $this->hireDate; }
    public function setHireDate(?\DateTimeInterface $hireDate): self { $this->hireDate = $hireDate; $this->touch(); return $this; }

    public function getTerminationDate(): ?\DateTimeInterface { return $this->terminationDate; }
    public function setTerminationDate(?\DateTimeInterface $terminationDate): self { $this->terminationDate = $terminationDate; $this->touch(); return $this; }

    public function getPosition(): ?string { return $this->position; }
    public function setPosition(?string $position): self { $this->position = $position; $this->touch(); return $this; }

    public function getDepartment(): ?string { return $this->department; }
    public function setDepartment(?string $department): self { $this->department = $department; $this->touch(); return $this; }

    public function getMaritalStatus(): ?string { return $this->maritalStatus; }
    public function setMaritalStatus(?string $maritalStatus): self { $this->maritalStatus = $maritalStatus; $this->touch(); return $this; }

    public function getNumberOfChildren(): int { return $this->numberOfChildren; }
    public function setNumberOfChildren(int $numberOfChildren): self { $this->numberOfChildren = $numberOfChildren; $this->touch(); return $this; }

    public function getMunicipality(): ?string { return $this->municipality; }
    public function setMunicipality(?string $municipality): self { $this->municipality = $municipality; $this->touch(); return $this; }

    public function getTaxMunicipality(): ?string { return $this->taxMunicipality; }
    public function setTaxMunicipality(?string $taxMunicipality): self { $this->taxMunicipality = $taxMunicipality; $this->touch(); return $this; }

    public function getIban(): ?string { return $this->iban; }
    public function setIban(?string $iban): self { $this->iban = $iban; $this->touch(); return $this; }

    public function getEmploymentLevel(): int { return $this->employmentLevel; }
    public function setEmploymentLevel(int $employmentLevel): self { $this->employmentLevel = max(0, min(100, $employmentLevel)); $this->touch(); return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
}