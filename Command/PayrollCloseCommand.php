<?php
declare(strict_types=1);

namespace KimaiPlugin\LiePayrollBundle\Command;

use App\Repository\UserRepository;
use KimaiPlugin\LiePayrollBundle\Service\PayrollService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: "lie-payroll:close", description: "Close payroll for given YYYY-MM (recalculate all users).")]
final class PayrollCloseCommand extends \Symfony\Component\Console\Command\Command
{
    public function __construct(private PayrollService $svc, private UserRepository $users) { parent::__construct(); }

    protected function configure(): void
    {
        $this->addArgument("month", InputArgument::REQUIRED, "YYYY-MM");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $month = (string) $input->getArgument("month");

        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $output->writeln("<error>Invalid month format '{$month}'. Expected YYYY-MM</error>");
            return self::FAILURE;
        }

        foreach ($this->users->findAll() as $u) {
            $this->svc->buildOrRecalculate($u, $month);
            $output->writeln("Calculated: {$u->getDisplayName()} - {$month}");
        }
        $output->writeln("<info>Done.</info>");
        return self::SUCCESS;
    }
}
