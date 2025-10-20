<?php
declare(strict_types=1);

namespace KimaiPlugin\LiePayrollBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "payroll_settings")]
class PayrollSetting
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(name: "key_name", type: "string", length: 191, unique: true)]
    private string $keyName;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $value = null;

    public function __construct(string $keyName, ?string $value)
    {
        $this->keyName = $keyName;
        $this->value = $value;
    }

    public function getId(): ?int { return $this->id; }
    public function getKeyName(): string { return $this->keyName; }
    public function getValue(): ?string { return $this->value; }
    public function setValue(?string $value): void { $this->value = $value; }
}
