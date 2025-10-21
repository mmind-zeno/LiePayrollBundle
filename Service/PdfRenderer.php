<?php
declare(strict_types=1);

namespace KimaiPlugin\LiePayrollBundle\Service;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

interface PdfRenderer
{
    /** @return string Absolute path of generated file */
    public function renderPayslipToFile(string $template, array $context, string $absolutePath): string;

    /** For quick preview */
    public function renderHtml(string $template, array $context): Response;
}

final class HtmlRenderer implements PdfRenderer
{
    public function __construct(private Environment $twig) {}

    public function renderPayslipToFile(string $template, array $context, string $absolutePath): string
    {
        $html = $this->twig->render($template, $context);

        $dir = dirname($absolutePath);
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" could not be created', $dir));
        }

        if (file_put_contents($absolutePath, $html) === false) {
            throw new \RuntimeException(sprintf('Failed to write payslip to file: %s', $absolutePath));
        }

        return $absolutePath;
    }

    public function renderHtml(string $template, array $context): Response
    {
        return new Response($this->twig->render($template, $context));
    }
}
