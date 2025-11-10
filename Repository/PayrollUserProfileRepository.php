<?php
declare(strict_types=1);

namespace KimaiPlugin\LiePayrollBundle\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use KimaiPlugin\LiePayrollBundle\Entity\PayrollUserProfile;

class PayrollUserProfileRepository extends EntityRepository
{
    public function findByUser(User $user): ?PayrollUserProfile
    {
        return $this->findOneBy(['user' => $user]);
    }

    public function findOrCreate(User $user): PayrollUserProfile
    {
        $profile = $this->findByUser($user);
        if ($profile === null) {
            $profile = new PayrollUserProfile($user);
            $this->getEntityManager()->persist($profile);
            $this->getEntityManager()->flush();
        }
        return $profile;
    }

    public function save(PayrollUserProfile $profile): void
    {
        $this->getEntityManager()->persist($profile);
        $this->getEntityManager()->flush();
    }
}