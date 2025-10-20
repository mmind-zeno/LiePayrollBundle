<?php
declare(strict_types=1);

namespace KimaiPlugin\LiePayrollBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "payroll_item")]
class PayrollItem
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PayrollPeriod::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private PayrollPeriod $period;

    #[ORM\Column(type: "string", length: 32)]
    private string $type; // work|holiday|sickness|bonus|deduction

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private string $quantity = "0.00"; // hours or amount

    #[ORM\Column(type: "string", length: 8)]
    private string $unit = "h"; // h|CHF

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private string $amount = "0.00"; // CHF

    public function __construct(PayrollPeriod $period, string $type, string $quantity, string $unit, string $amount)
    {
        $this->period = $period; $this->type = $type; $this->quantity = $quantity; $this->unit = $unit; $this->amount = $amount;
    }
}
