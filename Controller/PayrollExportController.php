<?php
declare(strict_types=1);

namespace KimaiPlugin\LiePayrollBundle\Controller;

use App\Repository\UserRepository;
use KimaiPlugin\LiePayrollBundle\Security\PayrollPermissions;
use KimaiPlugin\LiePayrollBundle\Repository\PayrollPeriodRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Response, StreamedResponse};
use Symfony\Component\Routing\Attribute\Route;

#[Route("/admin/payroll-export")]
#[IsGranted(PayrollPermissions::ROLE_PAYROLL_VIEW)]
final class PayrollExportController extends AbstractController
{
    public function __construct(
        private PayrollPeriodRepository $periodRepo,
        private UserRepository $userRepo
    ) {}

    #[Route("/csv/{month}", name: "payroll_export_csv", methods: ["GET"])]
    public function exportCsv(string $month): Response
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $this->addFlash("error", "Ungültiges Monatsformat");
            return $this->redirectToRoute("lie_payroll_index");
        }

        $users = $this->userRepo->findAll();
        $periods = [];
        
        foreach ($users as $user) {
            $period = $this->periodRepo->findOneByUserAndMonth($user, $month);
            if ($period) {
                $periods[] = $period;
            }
        }

        $response = new StreamedResponse(function() use ($periods) {
            $handle = fopen('php://output', 'w');
            
            // UTF-8 BOM für Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($handle, [
                'Mitarbeiter',
                'Monat',
                'Status',
                'Stunden',
                'Stundenlohn',
                'Bruttolohn',
                'AHV/IV/EO',
                'ALV',
                'NBU',
                'BVG',
                'Total Abzüge',
                'Nettolohn'
            ], ';');

            // Daten
            foreach ($periods as $p) {
                $deductions = $p->getDeductions();
                $ahv = $alv = $nbu = $bvg = '0.00';
                $totalDed = '0.00';
                
                foreach ($deductions as $d) {
                    $totalDed = bcadd($totalDed, $d['amount'], 2);
                    if ($d['code'] === 'AHV/IV/EO') $ahv = $d['amount'];
                    if ($d['code'] === 'ALV') $alv = $d['amount'];
                    if ($d['code'] === 'NBU') $nbu = $d['amount'];
                    if ($d['code'] === 'BVG') $bvg = $d['amount'];
                }
                
                fputcsv($handle, [
                    $p->getUser()->getDisplayName(),
                    $p->getMonth(),
                    $p->getStatus(),
                    $p->getTotalHours(),
                    $p->getHourlyRate(),
                    $p->getGrossSalary(),
                    $ahv,
                    $alv,
                    $nbu,
                    $bvg,
                    $totalDed,
                    $p->getNetSalary()
                ], ';');
            }
            
            fclose($handle);
        });

        $filename = "payroll_{$month}.csv";
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}\"");

        return $response;
    }

    #[Route("/summary/{month}", name: "payroll_export_summary", methods: ["GET"])]
    public function summary(string $month): Response
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $this->addFlash("error", "Ungültiges Monatsformat");
            return $this->redirectToRoute("lie_payroll_index");
        }

        $users = $this->userRepo->findAll();
        $periods = [];
        $totals = [
            'hours' => '0.00',
            'gross' => '0.00',
            'deductions' => '0.00',
            'net' => '0.00'
        ];
        
        foreach ($users as $user) {
            $period = $this->periodRepo->findOneByUserAndMonth($user, $month);
            if ($period) {
                $periods[] = $period;
                $totals['hours'] = bcadd($totals['hours'], $period->getTotalHours(), 2);
                $totals['gross'] = bcadd($totals['gross'], $period->getGrossSalary(), 2);
                $totals['net'] = bcadd($totals['net'], $period->getNetSalary(), 2);
                
                foreach ($period->getDeductions() as $d) {
                    $totals['deductions'] = bcadd($totals['deductions'], $d['amount'], 2);
                }
            }
        }

        return $this->render('@LiePayroll/export/summary.html.twig', [
            'month' => $month,
            'periods' => $periods,
            'totals' => $totals
        ]);
    }
}