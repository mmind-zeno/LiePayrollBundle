<?php
declare(strict_types=1);

namespace KimaiPlugin\LiePayrollBundle\Service;

use App\Entity\User;
use App\Repository\TimesheetRepository;
use Doctrine\ORM\EntityManagerInterface;
use KimaiPlugin\LiePayrollBundle\Entity\PayrollPeriod;
use KimaiPlugin\LiePayrollBundle\Repository\PayrollPeriodRepository;

final class PayrollService
{
    public function __construct(
        private EntityManagerInterface $em,
        private PayrollCalculator $calc,
        private TimesheetRepository $timesheetRepo,
        private SettingsService $settings
    ) {}

    public function buildOrRecalculate(User $user, string $month): PayrollPeriod
    {
        /** @var PayrollPeriodRepository $repo */
        $repo = $this->em->getRepository(PayrollPeriod::class);
        $period = $repo->findOneByUserAndMonth($user, $month) ?? new PayrollPeriod($user, $month);

        // Berechne Start- und End-Datum für den Monat
        $start = new \DateTime($month . '-01 00:00:00');
        $end = (clone $start)->modify('last day of this month')->setTime(23, 59, 59);

        // Aggregiere echte Stunden aus Kimai Timesheet
        $totalSeconds = $this->timesheetRepo->createQueryBuilder('t')
            ->select('SUM(t.duration) as totalDuration')
            ->where('t.user = :user')
            ->andWhere('t.begin >= :start')
            ->andWhere('t.begin <= :end')
            ->setParameter('user', $user)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();

        // Konvertiere Sekunden zu Stunden (mit 2 Dezimalstellen)
        $hours = $totalSeconds ? number_format($totalSeconds / 3600, 2, '.', '') : '0.00';

        // Hole Stundensatz
        $rateKey = "payroll.hourly_rate.user_{$user->getId()}";
        $rate = $this->settings->get($rateKey) 
             ?? $this->settings->get("payroll.default_hourly_rate", "30.00");

        // Berechne Brutto/Netto mit Abzügen
        $res = $this->calc->calc($hours, $rate);
        $period->setTotals($hours, $rate, $res["gross"], $res["deductions"], $res["net"]);

        $this->em->persist($period);
        $this->em->flush();

        return $period;
    }

    public function recalculateWithManualValues(PayrollPeriod $period, string $hours, string $rate): PayrollPeriod
    {
        $res = $this->calc->calc($hours, $rate);
        $period->setTotals($hours, $rate, $res["gross"], $res["deductions"], $res["net"]);

        $this->em->persist($period);
        $this->em->flush();

        return $period;
    }

    public function savePeriod(PayrollPeriod $period): void
    {
        $this->em->persist($period);
        $this->em->flush();
    }
}