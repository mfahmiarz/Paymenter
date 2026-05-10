<?php

use Illuminate\Support\Facades\Route;
use Paymenter\Extensions\Others\DiscordLink\Http\Controllers\DiscordLinkController;

Route::group(['middleware' => ['web', 'auth']], function () {
    Route::get('/account/discord-link', [DiscordLinkController::class, 'index'])->name('discordlink.index');
    Route::get('/discord/link', [DiscordLinkController::class, 'redirectToDiscord'])->name('discordlink.redirect');
    Route::get('/discord/callback', [DiscordLinkController::class, 'handleCallback'])->name('discordlink.callback');
    Route::get('/account/discord-link/unlink', [DiscordLinkController::class, 'unlink'])->name('discordlink.unlink');
});
