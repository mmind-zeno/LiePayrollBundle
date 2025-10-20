<?php
declare(strict_types=1);

namespace KimaiPlugin\LiePayrollBundle\EventSubscriber;

use App\Event\ConfigureMainMenuEvent;
use KimaiPlugin\LiePayrollBundle\Security\PayrollPermissions;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class MenuSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [ConfigureMainMenuEvent::class => ["onMenuConfigure", 100]];
    }

    public function onMenuConfigure(ConfigureMainMenuEvent $event): void
    {
        $menu = $event->getMenu();
        $child = $menu->addChild("lie_payroll", [
            "route" => "lie_payroll_index",
            "label" => "Payroll",
            "extras" => ["icon" => "ti ti-cash"]
        ]);

        if (!$event->isGranted(PayrollPermissions::ROLE_PAYROLL_VIEW)) {
            $menu->removeChild("lie_payroll");
        }
    }
}
