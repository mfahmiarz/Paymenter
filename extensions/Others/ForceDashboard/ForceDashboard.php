<?php

namespace Paymenter\Extensions\Others\ForceDashboard;

use App\Classes\Extension\Extension;
use App\Helpers\ExtensionHelper;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class ForceDashboard extends Extension
{
    public function getConfig($values = []): array
    {
        return [
            [
                'name' => 'enabled',
                'label' => 'Enabled',
                'type' => 'checkbox',
                'description' => 'Enable Force Dashboard extension',
                'required' => false,
                'default' => true,
            ],
        ];
    }

    public function installed()
    {
        // Run migrations if needed
    }

    public function uninstalled()
    {
        // Rollback migrations if needed
    }

    public function boot()
    {
        if (!$this->config('enabled', true)) {
            return;
        }

        // Register middleware
        ExtensionHelper::registerMiddleware(\Paymenter\Extensions\Others\ForceDashboard\Http\Middleware\ForceDashboardMiddleware::class, 'web');
        
        // Register JavaScript injection middleware
        ExtensionHelper::registerMiddleware(\Paymenter\Extensions\Others\ForceDashboard\Http\Middleware\InjectJavaScriptMiddleware::class, 'web');
    }
}
