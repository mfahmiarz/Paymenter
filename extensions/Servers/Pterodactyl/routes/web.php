<?php

use Illuminate\Support\Facades\Route;
use Paymenter\Extensions\Servers\Pterodactyl\Pterodactyl;

Route::get('/pterodactyl/sso/login/{service}/{server}', function ($service, $server) {
    $pterodactyl = app(Pterodactyl::class);
    $serviceModel = \App\Models\Service::findOrFail($service);
    
    return $pterodactyl->ssoLogin($serviceModel, $server);
})->name('pterodactyl.sso.login');
