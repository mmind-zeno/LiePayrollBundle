<?php
declare(strict_types=1);

namespace KimaiPlugin\LiePayrollBundle\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KimaiPlugin\LiePayrollBundle\Entity\PayrollPeriod;
use KimaiPlugin\LiePayrollBundle\Repository\PayrollPeriodRepository;

final class PayrollService
{
    public function __construct(
        private EntityManagerInterface $em,
        private PayrollCalculator $calc
    ) {}

    /** Aggregiert Stunden (Worklog) aus Kimai – MVP: Dummy-Rate/Hours */
    public function buildOrRecalculate(User $user, string $month): PayrollPeriod
    {
        /** @var PayrollPeriodRepository $repo */
        $repo = $this->em->getRepository(PayrollPeriod::class);
        $period = $repo->findOneByUserAndMonth($user, $month) ?? new PayrollPeriod($user, $month);

        // TODO: echte Stundenaggregation via TimesheetRepository (Summe im Monat)
        $hours = "160.00";
        $rate  = "30.00";

        $res = $this->calc->calc($hours, $rate);
        $period->setTotals($hours, $rate, $res["gross"], $res["deductions"], $res["net"]);

        $this->em->persist($period);
        $this->em->flush();

        return $period;
    }
}
