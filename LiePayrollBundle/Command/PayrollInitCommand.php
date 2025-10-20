<?php
declare(strict_types=1);

namespace KimaiPlugin\LiePayrollBundle\Command;

use KimaiPlugin\LiePayrollBundle\Service\SettingsService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: "lie-payroll:init", description: "Initialize default payroll settings.")]
final class PayrollInitCommand extends \Symfony\Component\Console\Command\Command
{
    public function __construct(private SettingsService $settings) { parent::__construct(); }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->settings->set("payroll.AHV_RATE", "5.30");
        $this->settings->set("payroll.ALV_RATE", "1.10");
        $this->settings->set("payroll.NBU_RATE", "1.00");
        $output->writeln("<info>LiePayroll default rates initialized.</info>");
        return self::SUCCESS;
    }
}
