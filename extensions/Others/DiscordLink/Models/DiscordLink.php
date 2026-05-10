<?php

namespace Paymenter\Extensions\Others\DiscordLink\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscordLink extends Model
{
    protected $table = 'ext_discord_links';

    protected $fillable = [
        'user_id',
        'discord_user_id',
        'discord_username',
        'access_token',
        'refresh_token',
        'token_expires_at',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
