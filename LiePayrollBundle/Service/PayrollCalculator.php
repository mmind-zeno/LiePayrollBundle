<?php
declare(strict_types=1);

namespace KimaiPlugin\LiePayrollBundle\Service;

final class PayrollCalculator
{
    public function __construct(private SettingsService $settings) {}

    public function calc(string $hours, string $rate): array
    {
        $gross = bcmul($hours, $rate, 2);

        $def = fn(string $k, string $fallback) => (string) ($this->settings->get($k, $fallback) ?? $fallback);

        $ahv = $def("payroll.AHV_RATE", "5.30"); // %
        $alv = $def("payroll.ALV_RATE", "1.10");
        $nbu = $def("payroll.NBU_RATE", "1.00");

        $ded = [];
        $ded[] = ["code" => "AHV", "rate" => $ahv, "amount" => $this->pct($gross, $ahv)];
        $ded[] = ["code" => "ALV", "rate" => $alv, "amount" => $this->pct($gross, $alv)];
        $ded[] = ["code" => "NBU", "rate" => $nbu, "amount" => $this->pct($gross, $nbu)];

        $totalDed = "0.00";
        foreach ($ded as $d) { $totalDed = bcadd($totalDed, $d["amount"], 2); }

        $net = bcsub($gross, $totalDed, 2);

        return [
            "gross" => $gross,
            "deductions" => $ded,
            "net" => $net
        ];
    }

    private function pct(string $base, string $ratePercent): string
    {
        $rate = bcdiv($ratePercent, "100", 6);
        return bcmul($base, $rate, 2);
    }
}
