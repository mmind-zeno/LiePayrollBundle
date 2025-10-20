<?php
declare(strict_types=1);

namespace KimaiPlugin\LiePayrollBundle\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use KimaiPlugin\LiePayrollBundle\Security\PayrollPermissions;
use KimaiPlugin\LiePayrollBundle\Service\PayrollService;
use KimaiPlugin\LiePayrollBundle\Service\PdfRenderer;
use KimaiPlugin\LiePayrollBundle\Repository\PayrollPeriodRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request,Response,RedirectResponse};
use Symfony\Component\Routing\Annotation\Route;

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
        $this->service->buildOrRecalculate($user, $month);
        $this->addFlash("success", "Payroll für {$user->getDisplayName()} {$month} berechnet.");
        return $this->redirectToRoute("lie_payroll_index", ["month" => $month]);
    }

    #[Route("/show/{id}/{month}", name: "lie_payroll_show", methods: ["GET"])]
    public function show(User $user, string $month, PayrollPeriodRepository $repo): Response
    {
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
        $period = $repo->findOneByUserAndMonth($user, $month);
        if (!$period) {
            $period = $this->service->buildOrRecalculate($user, $month);
        }
        return $this->renderer->renderHtml("@LiePayroll/payroll/payslip.html.twig", [
            "p" => $period
        ]);
    }
}
