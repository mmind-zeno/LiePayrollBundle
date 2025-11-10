<?php
declare(strict_types=1);

namespace KimaiPlugin\LiePayrollBundle\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use KimaiPlugin\LiePayrollBundle\Entity\PayrollPeriod;
use KimaiPlugin\LiePayrollBundle\Security\PayrollPermissions;
use KimaiPlugin\LiePayrollBundle\Service\PayrollService;
use KimaiPlugin\LiePayrollBundle\Service\PdfRenderer;
use KimaiPlugin\LiePayrollBundle\Repository\PayrollPeriodRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request,Response,RedirectResponse};
use Symfony\Component\Routing\Attribute\Route;

#[Route("/admin/lie-payroll")]
#[IsGranted(PayrollPermissions::ROLE_PAYROLL_VIEW)]
final class PayrollAdminController extends AbstractController
{
    public function __construct(
        private PayrollService $service,
        private PdfRenderer $renderer
    ) {}

    #[Route("", name: "lie_payroll_index", methods: ["GET"])]
    public function index(UserRepository $users, Request $req): Response
    {
        $month = $req->query->get("month") ?? (new \DateTimeImmutable("first day of this month"))->format("Y-m");

        return $this->render("@LiePayroll/payroll/index.html.twig", [
            "month" => $month,
            "users" => $users->findAll(),
        ]);
    }

    #[Route("/recalculate/{id}/{month}", name: "lie_payroll_recalculate", methods: ["POST","GET"])]
    #[IsGranted(PayrollPermissions::ROLE_PAYROLL_ADMIN)]
    public function recalc(User $user, string $month): RedirectResponse
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $this->addFlash("error", "Ungültiges Monatsformat. Erwartet: YYYY-MM");
            return $this->redirectToRoute("lie_payroll_index");
        }

        $this->service->buildOrRecalculate($user, $month);
        $this->addFlash("success", "Payroll für {$user->getDisplayName()} {$month} berechnet.");

        return $this->redirectToRoute("lie_payroll_index", ["month" => $month]);
    }

    #[Route("/show/{id}/{month}", name: "lie_payroll_show", methods: ["GET"])]
    public function show(User $user, string $month, PayrollPeriodRepository $repo): Response
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $this->addFlash("error", "Ungültiges Monatsformat. Erwartet: YYYY-MM");
            return $this->redirectToRoute("lie_payroll_index");
        }

        $period = $repo->findOneByUserAndMonth($user, $month);
        if (!$period) {
            $period = $this->service->buildOrRecalculate($user, $month);
        }

        return $this->render("@LiePayroll/payroll/show.html.twig", [
            "period" => $period
        ]);
    }

    #[Route("/payslip/{id}/{month}", name: "lie_payroll_payslip", methods: ["GET"])]
    public function payslip(User $user, string $month, PayrollPeriodRepository $repo): Response
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $this->addFlash("error", "Ungültiges Monatsformat. Erwartet: YYYY-MM");
            return $this->redirectToRoute("lie_payroll_index");
        }

        $period = $repo->findOneByUserAndMonth($user, $month);
        if (!$period) {
            $period = $this->service->buildOrRecalculate($user, $month);
        }

        $html = $this->renderer->render("@LiePayroll/payroll/payslip.html.twig", [
            "p" => $period
        ]);

        return new Response($html, 200, ["Content-Type" => "text/html"]);
    }

    #[Route("/edit/{id}/{month}", name: "lie_payroll_edit", methods: ["GET", "POST"])]
    #[IsGranted(PayrollPermissions::ROLE_PAYROLL_ADMIN)]
    public function edit(User $user, string $month, PayrollPeriodRepository $repo, Request $req): Response
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $this->addFlash("error", "Ungültiges Monatsformat. Erwartet: YYYY-MM");
            return $this->redirectToRoute("lie_payroll_index");
        }

        $period = $repo->findOneByUserAndMonth($user, $month);
        if (!$period) {
            $this->addFlash("error", "Periode nicht gefunden. Bitte zuerst berechnen.");
            return $this->redirectToRoute("lie_payroll_index", ["month" => $month]);
        }

        if ($req->isMethod('POST')) {
            $hours = $req->request->get('hours');
            $rate = $req->request->get('rate');
            
            // Neu berechnen mit manuellen Werten
            $this->service->recalculateWithManualValues($period, $hours, $rate);
            
            $this->addFlash("success", "Periode aktualisiert!");
            return $this->redirectToRoute("lie_payroll_show", ["id" => $user->getId(), "month" => $month]);
        }

        return $this->render("@LiePayroll/payroll/edit.html.twig", [
            "period" => $period,
            "user" => $user,
            "month" => $month
        ]);
    }

    #[Route("/approve/{id}/{month}", name: "lie_payroll_approve", methods: ["POST"])]
    #[IsGranted(PayrollPermissions::ROLE_PAYROLL_ADMIN)]
    public function approve(User $user, string $month, PayrollPeriodRepository $repo): RedirectResponse
    {
        $period = $repo->findOneByUserAndMonth($user, $month);
        if ($period && $period->getStatus() === PayrollPeriod::STATUS_DRAFT) {
            $period->setStatus(PayrollPeriod::STATUS_APPROVED);
            $this->service->savePeriod($period);
            $this->addFlash("success", "Periode freigegeben!");
        }
        return $this->redirectToRoute("lie_payroll_show", ["id" => $user->getId(), "month" => $month]);
    }

    #[Route("/mark-paid/{id}/{month}", name: "lie_payroll_mark_paid", methods: ["POST"])]
    #[IsGranted(PayrollPermissions::ROLE_PAYROLL_ADMIN)]
    public function markPaid(User $user, string $month, PayrollPeriodRepository $repo): RedirectResponse
    {
        $period = $repo->findOneByUserAndMonth($user, $month);
        if ($period && $period->getStatus() === PayrollPeriod::STATUS_APPROVED) {
            $period->setStatus(PayrollPeriod::STATUS_PAID);
            $this->service->savePeriod($period);
            $this->addFlash("success", "Als bezahlt markiert!");
        }
        return $this->redirectToRoute("lie_payroll_show", ["id" => $user->getId(), "month" => $month]);
    }

    #[Route("/batch-calculate/{month}", name: "lie_payroll_batch_calculate", methods: ["POST"])]
    #[IsGranted(PayrollPermissions::ROLE_PAYROLL_ADMIN)]
    public function batchCalculate(string $month, UserRepository $users): RedirectResponse
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $this->addFlash("error", "Ungültiges Monatsformat. Erwartet: YYYY-MM");
            return $this->redirectToRoute("lie_payroll_index");
        }

        $allUsers = $users->findAll();
        $count = 0;

        foreach ($allUsers as $user) {
            try {
                $this->service->buildOrRecalculate($user, $month);
                $count++;
            } catch (\Exception $e) {
                // Log error but continue
                continue;
            }
        }

        $this->addFlash("success", "Payroll für {$count} Mitarbeiter berechnet!");
        return $this->redirectToRoute("lie_payroll_index", ["month" => $month]);
    }

    #[Route("/batch-approve/{month}", name: "lie_payroll_batch_approve", methods: ["POST"])]
    #[IsGranted(PayrollPermissions::ROLE_PAYROLL_ADMIN)]
    public function batchApprove(string $month, PayrollPeriodRepository $repo): RedirectResponse
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $this->addFlash("error", "Ungültiges Monatsformat");
            return $this->redirectToRoute("lie_payroll_index");
        }

        $periods = $repo->findBy(['month' => $month, 'status' => PayrollPeriod::STATUS_DRAFT]);
        $count = 0;

        foreach ($periods as $period) {
            $period->setStatus(PayrollPeriod::STATUS_APPROVED);
            $this->service->savePeriod($period);
            $count++;
        }

        $this->addFlash("success", "{$count} Perioden freigegeben!");
        return $this->redirectToRoute("lie_payroll_index", ["month" => $month]);
    }
}