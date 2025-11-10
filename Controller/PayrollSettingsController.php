<?php
declare(strict_types=1);

namespace KimaiPlugin\LiePayrollBundle\Controller;

use App\Repository\UserRepository;
use KimaiPlugin\LiePayrollBundle\Security\PayrollPermissions;
use KimaiPlugin\LiePayrollBundle\Service\SettingsService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response, RedirectResponse};
use Symfony\Component\Routing\Attribute\Route;

#[Route("/admin/payroll-settings")]
#[IsGranted(PayrollPermissions::ROLE_PAYROLL_ADMIN)]
final class PayrollSettingsController extends AbstractController
{
    public function __construct(
        private SettingsService $settings,
        private UserRepository $userRepo
    ) {}

    #[Route("", name: "payroll_settings_index", methods: ["GET"])]
    public function index(): Response
    {
        $users = $this->userRepo->findAll();
        
        $globalSettings = [
            'default_hourly_rate' => $this->settings->get('payroll.default_hourly_rate', '30.00'),
            'ahv_rate' => $this->settings->get('payroll.AHV_RATE', '5.30'),
            'alv_rate' => $this->settings->get('payroll.ALV_RATE', '1.10'),
            'nbu_rate' => $this->settings->get('payroll.NBU_RATE', '1.00'),
            'bvg_rate' => $this->settings->get('payroll.BVG_RATE', '0.00'),
        ];
        
        $userRates = [];
        foreach ($users as $user) {
            $key = "payroll.hourly_rate.user_{$user->getId()}";
            $rate = $this->settings->get($key);
            if ($rate !== null) {
                $userRates[$user->getId()] = $rate;
            }
        }
        
        return $this->render('@LiePayroll/settings/index.html.twig', [
            'global' => $globalSettings,
            'users' => $users,
            'userRates' => $userRates,
        ]);
    }

    #[Route("/save", name: "payroll_settings_save", methods: ["POST"])]
    public function save(Request $req): RedirectResponse
    {
        // Globale Einstellungen speichern
        $this->settings->set('payroll.default_hourly_rate', $req->request->get('default_hourly_rate', '30.00'));
        $this->settings->set('payroll.AHV_RATE', $req->request->get('ahv_rate', '5.30'));
        $this->settings->set('payroll.ALV_RATE', $req->request->get('alv_rate', '1.10'));
        $this->settings->set('payroll.NBU_RATE', $req->request->get('nbu_rate', '1.00'));
        $this->settings->set('payroll.BVG_RATE', $req->request->get('bvg_rate', '0.00'));
        
        // User-spezifische Stundensätze speichern
        $userRates = $req->request->all('user_rate');
        foreach ($userRates as $userId => $rate) {
            if (!empty($rate)) {
                $key = "payroll.hourly_rate.user_{$userId}";
                $this->settings->set($key, $rate);
            }
        }
        
        $this->addFlash('success', 'Einstellungen gespeichert!');
        return $this->redirectToRoute('payroll_settings_index');
    }
}