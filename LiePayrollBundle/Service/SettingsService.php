<?php
declare(strict_types=1);

namespace KimaiPlugin\LiePayrollBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use KimaiPlugin\LiePayrollBundle\Entity\PayrollSetting;

final class SettingsService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function get(string $key, ?string $default = null): ?string
    {
        $repo = $this->em->getRepository(PayrollSetting::class);
        $ent = $repo->findOneBy(["keyName" => $key]);
        return $ent?->getValue() ?? $default;
    }

    public function set(string $key, ?string $value): void
    {
        $repo = $this->em->getRepository(PayrollSetting::class);
        $ent = $repo->findOneBy(["keyName" => $key]) ?? new PayrollSetting($key, $value);
        $ent->setValue($value);
        self::persistAndFlush($this->em, $ent);
    }

    private static function persistAndFlush(EntityManagerInterface $em, object $entity): void
    {
        $em->persist($entity);
        $em->flush();
    }
}
