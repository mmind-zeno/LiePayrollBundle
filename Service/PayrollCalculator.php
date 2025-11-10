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

        // Liechtenstein Sozialversicherungssätze 2025
        $ahvIv = $def("payroll.AHV_IV_RATE", "4.70");  // AHV/IV: 4.7%
        $alv = $def("payroll.ALV_RATE", "1.10");       // ALV: 1.1%
        $nbu = $def("payroll.NBU_RATE", "1.19");       // NBU: ~1.19% (Durchschnitt)
        $bpvg = $def("payroll.BPVG_RATE", "0.00");     // BPVG: optional, variabel

        $ded = [];
        $ded[] = ["code" => "AHV/IV", "rate" => $ahvIv, "amount" => $this->pct($gross, $ahvIv)];
        $ded[] = ["code" => "ALV", "rate" => $alv, "amount" => $this->pct($gross, $alv)];
        $ded[] = ["code" => "NBU Anteil", "rate" => $nbu, "amount" => $this->pct($gross, $nbu)];
        
        if ($bpvg !== "0.00") {
            $ded[] = ["code" => "BPVG", "rate" => $bpvg, "amount" => $this->pct($gross, $bpvg)];
        }

        $totalDed = "0.00";
        foreach ($ded as $d) { 
            $totalDed = bcadd($totalDed, $d["amount"], 2); 
        }

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