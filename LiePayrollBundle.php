<?php
namespace KimaiPlugin\LiePayrollBundle;

use App\Plugin\PluginInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class LiePayrollBundle extends Bundle implements PluginInterface
{
    public function getPath(): string
    {
        return \dirname(__DIR__) . '/LiePayrollBundle';
    }
}
