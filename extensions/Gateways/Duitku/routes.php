<?php

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Paymenter\Extensions\Gateways\Duitku\Duitku;

Route::post('/extensions/duitku/webhook', [Duitku::class, 'webhook'])->withoutMiddleware([VerifyCsrfToken::class])->name('extensions.duitku.webhook');
