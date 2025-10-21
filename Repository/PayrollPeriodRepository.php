<?php
declare(strict_types=1);

namespace KimaiPlugin\LiePayrollBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use KimaiPlugin\LiePayrollBundle\Entity\PayrollPeriod;
use App\Entity\User;

final class PayrollPeriodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PayrollPeriod::class);
    }

    public function findOneByUserAndMonth(User $user, string $month): ?PayrollPeriod
    {
        return $this->createQueryBuilder("p")
            ->andWhere("p.user = :u")->setParameter("u", $user)
            ->andWhere("p.month = :m")->setParameter("m", $month)
            ->getQuery()->getOneOrNullResult();
    }
}
