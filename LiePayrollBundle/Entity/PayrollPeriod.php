<?php
declare(strict_types=1);

namespace KimaiPlugin\LiePayrollBundle\Entity;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "KimaiPlugin\\LiePayrollBundle\\Repository\\PayrollPeriodRepository")]
#[ORM\Table(name: "payroll_period")]
#[ORM\UniqueConstraint(name: "uniq_user_month", columns: ["user_id", "month"])]
class PayrollPeriod
{
    public const STATUS_DRAFT    = "draft";
    public const STATUS_APPROVED = "approved";
    public const STATUS_PAID     = "paid";

    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private User $user;

    #[ORM\Column(type: "string", length: 7)] // YYYY-MM
    private string $month;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private string $totalHours = "0.00";

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private string $hourlyRate = "0.00";

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private string $grossSalary = "0.00";

    #[ORM\Column(type: "json")]
    private array $deductions = []; // [{code,rate,amount}]

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private string $netSalary = "0.00";

    #[ORM\Column(type: "string", length: 16)]
    private string $status = self::STATUS_DRAFT;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $payslipPath = null;

    #[ORM\Column(type: "datetime_immutable")]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: "datetime_immutable")]
    private \DateTimeImmutable $updatedAt;

    public function __construct(User $user, string $month)
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            throw new \InvalidArgumentException(sprintf('Invalid month format "%s". Expected YYYY-MM', $month));
        }
        $this->user = $user;
        $this->month = $month;
        $now = new \DateTimeImmutable("now");
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function touch(): void { $this->updatedAt = new \DateTimeImmutable("now"); }

    public function getId(): ?int { return $this->id; }
    public function getUser(): User { return $this->user; }
    public function getMonth(): string { return $this->month; }
    public function setTotals(string $hours, string $rate, string $gross, array $deductions, string $net): void {
        $this->totalHours = $hours; $this->hourlyRate = $rate; $this->grossSalary = $gross;
        $this->deductions = $deductions; $this->netSalary = $net; $this->touch();
    }
    public function getTotalHours(): string { return $this->totalHours; }
    public function getHourlyRate(): string { return $this->hourlyRate; }
    public function getGrossSalary(): string { return $this->grossSalary; }
    public function getDeductions(): array { return $this->deductions; }
    public function getNetSalary(): string { return $this->netSalary; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): void { $this->status = $status; $this->touch(); }
    public function getPayslipPath(): ?string { return $this->payslipPath; }
    public function setPayslipPath(?string $p): void { $this->payslipPath = $p; $this->touch(); }
}
