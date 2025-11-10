<?php
declare(strict_types=1);

namespace KimaiPlugin\LiePayrollBundle\Service;

use Twig\Environment;

final class HtmlRenderer implements PdfRenderer
{
    public function __construct(
        private Environment $twig
    ) {}

    public function render(string $template, array $data): string
    {
        return $this->twig->render($template, $data);
    }
}
