<?php
declare(strict_types=1);

namespace KimaiPlugin\LiePayrollBundle\Service;

interface PdfRenderer
{
    public function render(string $template, array $data): string;
}
