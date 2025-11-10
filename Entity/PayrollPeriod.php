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

    #[ORM\Column(type: "string", length: 7)]
    private string $month;

    // Basis-Felder
    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private string $totalHours = "0.00";

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, nullable: true)]
    private ?string $targetHours = null;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private string $hourlyRate = "0.00";

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, nullable: true)]
    private ?string $baseSalary = null;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private string $grossSalary = "0.00";

    // Zusätzliche Bezüge
    #[ORM\Column(type: "decimal", precision: 10, scale: 2, options: ["default" => "0.00"])]
    private string $overtime = "0.00";

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, options: ["default" => "0.00"])]
    private string $nightShift = "0.00";

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, options: ["default" => "0.00"])]
    private string $sundayWork = "0.00";

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, options: ["default" => "0.00"])]
    private string $bonus = "0.00";

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, options: ["default" => "0.00"])]
    private string $thirteenthSalary = "0.00";

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, options: ["default" => "0.00"])]
    private string $childAllowance = "0.00";

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, options: ["default" => "0.00"])]
    private string $vacationCompensation = "0.00";

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, options: ["default" => "0.00"])]
    private string $expenseAllowance = "0.00";

    // Abzüge
    #[ORM\Column(type: "json")]
    private array $deductions = [];

    #[ORM\Column(type: "json", nullable: true)]
    private ?array $otherDeductions = null;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private string $netSalary = "0.00";

    // Ferien & Überzeit
    #[ORM\Column(type: "decimal", precision: 10, scale: 2, options: ["default" => "0.00"])]
    private string $vacationBalance = "0.00";

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, options: ["default" => "0.00"])]
    private string $vacationTaken = "0.00";

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, options: ["default" => "0.00"])]
    private string $vacationRemaining = "0.00";

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, options: ["default" => "0.00"])]
    private string $overtimeBalance = "0.00";

    // YTD (Year-to-Date)
    #[ORM\Column(type: "decimal", precision: 10, scale: 2, options: ["default" => "0.00"])]
    private string $ytdGross = "0.00";

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, options: ["default" => "0.00"])]
    private string $ytdNet = "0.00";

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, options: ["default" => "0.00"])]
    private string $ytdDeductions = "0.00";

    // Status & Meta
    #[ORM\Column(type: "string", length: 16)]
    private string $status = self::STATUS_DRAFT;

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $paymentDate = null;

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

    public function touch(): void { 
        $this->updatedAt = new \DateTimeImmutable("now"); 
    }

    // Basic Getters
    public function getId(): ?int { return $this->id; }
    public function getUser(): User { return $this->user; }
    public function getMonth(): string { return $this->month; }

    // Hours & Rate
    public function getTotalHours(): string { return $this->totalHours; }
    public function setTotalHours(string $hours): self { $this->totalHours = $hours; $this->touch(); return $this; }

    public function getTargetHours(): ?string { return $this->targetHours; }
    public function setTargetHours(?string $hours): self { $this->targetHours = $hours; $this->touch(); return $this; }

    public function getHourlyRate(): string { return $this->hourlyRate; }
    public function setHourlyRate(string $rate): self { $this->hourlyRate = $rate; $this->touch(); return $this; }

    // Salary
    public function getBaseSalary(): ?string { return $this->baseSalary; }
    public function setBaseSalary(?string $salary): self { $this->baseSalary = $salary; $this->touch(); return $this; }

    public function getGrossSalary(): string { return $this->grossSalary; }
    public function setGrossSalary(string $salary): self { $this->grossSalary = $salary; $this->touch(); return $this; }

    public function getNetSalary(): string { return $this->netSalary; }
    public function setNetSalary(string $salary): self { $this->netSalary = $salary; $this->touch(); return $this; }

    // Additional Income
    public function getOvertime(): string { return $this->overtime; }
    public function setOvertime(string $overtime): self { $this->overtime = $overtime; $this->touch(); return $this; }

    public function getNightShift(): string { return $this->nightShift; }
    public function setNightShift(string $nightShift): self { $this->nightShift = $nightShift; $this->touch(); return $this; }

    public function getSundayWork(): string { return $this->sundayWork; }
    public function setSundayWork(string $sundayWork): self { $this->sundayWork = $sundayWork; $this->touch(); return $this; }

    public function getBonus(): string { return $this->bonus; }
    public function setBonus(string $bonus): self { $this->bonus = $bonus; $this->touch(); return $this; }

    public function getThirteenthSalary(): string { return $this->thirteenthSalary; }
    public function setThirteenthSalary(string $salary): self { $this->thirteenthSalary = $salary; $this->touch(); return $this; }

    public function getChildAllowance(): string { return $this->childAllowance; }
    public function setChildAllowance(string $allowance): self { $this->childAllowance = $allowance; $this->touch(); return $this; }

    public function getVacationCompensation(): string { return $this->vacationCompensation; }
    public function setVacationCompensation(string $compensation): self { $this->vacationCompensation = $compensation; $this->touch(); return $this; }

    public function getExpenseAllowance(): string { return $this->expenseAllowance; }
    public function setExpenseAllowance(string $allowance): self { $this->expenseAllowance = $allowance; $this->touch(); return $this; }

    // Deductions
    public function getDeductions(): array { return $this->deductions; }
    public function setDeductions(array $deductions): self { $this->deductions = $deductions; $this->touch(); return $this; }

    public function getOtherDeductions(): ?array { return $this->otherDeductions; }
    public function setOtherDeductions(?array $deductions): self { $this->otherDeductions = $deductions; $this->touch(); return $this; }

    // Vacation & Overtime Balance
    public function getVacationBalance(): string { return $this->vacationBalance; }
    public function setVacationBalance(string $balance): self { $this->vacationBalance = $balance; $this->touch(); return $this; }

    public function getVacationTaken(): string { return $this->vacationTaken; }
    public function setVacationTaken(string $taken): self { $this->vacationTaken = $taken; $this->touch(); return $this; }

    public function getVacationRemaining(): string { return $this->vacationRemaining; }
    public function setVacationRemaining(string $remaining): self { $this->vacationRemaining = $remaining; $this->touch(); return $this; }

    public function getOvertimeBalance(): string { return $this->overtimeBalance; }
    public function setOvertimeBalance(string $balance): self { $this->overtimeBalance = $balance; $this->touch(); return $this; }

    // YTD
    public function getYtdGross(): string { return $this->ytdGross; }
    public function setYtdGross(string $ytd): self { $this->ytdGross = $ytd; $this->touch(); return $this; }

    public function getYtdNet(): string { return $this->ytdNet; }
    public function setYtdNet(string $ytd): self { $this->ytdNet = $ytd; $this->touch(); return $this; }

    public function getYtdDeductions(): string { return $this->ytdDeductions; }
    public function setYtdDeductions(string $ytd): self { $this->ytdDeductions = $ytd; $this->touch(); return $this; }

    // Status & Payment
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; $this->touch(); return $this; }

    public function getPaymentDate(): ?\DateTimeInterface { return $this->paymentDate; }
    public function setPaymentDate(?\DateTimeInterface $date): self { $this->paymentDate = $date; $this->touch(); return $this; }

    public function getPayslipPath(): ?string { return $this->payslipPath; }
    public function setPayslipPath(?string $path): self { $this->payslipPath = $path; $this->touch(); return $this; }

    // Legacy Method für Kompatibilität
    public function setTotals(string $hours, string $rate, string $gross, array $deductions, string $net): void {
        $this->totalHours = $hours;
        $this->hourlyRate = $rate;
        $this->grossSalary = $gross;
        $this->deductions = $deductions;
        $this->netSalary = $net;
        $this->touch();
    }
}