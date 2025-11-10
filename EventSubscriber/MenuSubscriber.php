<?php
declare(strict_types=1);

namespace KimaiPlugin\LiePayrollBundle\EventSubscriber;

use App\Event\ConfigureMainMenuEvent;
use App\Utils\MenuItemModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class MenuSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [ConfigureMainMenuEvent::class => ['onMenuConfigure', 100]];
    }

    public function onMenuConfigure(ConfigureMainMenuEvent $event): void
    {
        $menu = $event->getMenu();
        
        // Hauptmenü: Payroll
        $payrollMenu = new MenuItemModel('lie_payroll_menu', 'Payroll', 'lie_payroll_index');
        $payrollMenu->setIcon('ti ti-cash');
        
        // Submenu-Einträge
        $overviewMenu = new MenuItemModel('lie_payroll_index', 'Übersicht', 'lie_payroll_index');
        $overviewMenu->setIcon('ti ti-list');
        
        // NEU: Mitarbeiterstammdaten
        $usersMenu = new MenuItemModel('payroll_users_index', 'Mitarbeiterstammdaten', 'payroll_users_index');
        $usersMenu->setIcon('ti ti-users');
        
        $settingsMenu = new MenuItemModel('payroll_settings_index', 'Einstellungen', 'payroll_settings_index');
        $settingsMenu->setIcon('ti ti-settings');
        
        $payrollMenu->addChild($overviewMenu);
        $payrollMenu->addChild($usersMenu);  // NEU
        $payrollMenu->addChild($settingsMenu);
        
        $menu->addChild($payrollMenu);
    }
}