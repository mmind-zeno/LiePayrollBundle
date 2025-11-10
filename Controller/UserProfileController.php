<?php
declare(strict_types=1);

namespace KimaiPlugin\LiePayrollBundle\Controller;

use App\Controller\AbstractController;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KimaiPlugin\LiePayrollBundle\Entity\PayrollUserProfile;
use KimaiPlugin\LiePayrollBundle\Form\UserProfileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/payroll/users')]
#[IsGranted('ROLE_ADMIN')]
class UserProfileController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    #[Route('', name: 'payroll_users_index', methods: ['GET'])]
    public function index(): Response
    {
        $users = $this->em->getRepository(User::class)->findAll();
        
        return $this->render("@LiePayroll/user_profile/index.html.twig", [
            'users' => $users
        ]);
    }

    #[Route('/{id}/profile', name: 'payroll_users_profile', methods: ['GET', 'POST'])]
    public function profile(User $user, Request $request): Response
    {
        $profileRepo = $this->em->getRepository(PayrollUserProfile::class);
        $profile = $profileRepo->findOneBy(['user' => $user]);
        
        if ($profile === null) {
            $profile = new PayrollUserProfile($user);
            $this->em->persist($profile);
        }

        $form = $this->createForm(UserProfileType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->flashSuccess('Mitarbeiterprofil wurde erfolgreich gespeichert.');
            
            return $this->redirectToRoute('payroll_users_index');
        }

        return $this->render("@LiePayroll/user_profile/edit.html.twig", [
            'user' => $user,
            'profile' => $profile,
            'form' => $form,
        ]);
    }
}