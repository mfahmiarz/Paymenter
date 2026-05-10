<?php

namespace Paymenter\Extensions\Others\DiscordLink;

use App\Classes\Extension\Extension;
use App\Helpers\ExtensionHelper;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Paymenter\Extensions\Others\DiscordLink\Models\DiscordLink as DiscordLinkModel;

class DiscordLink extends Extension
{
    public function __construct(public $config = []) {}

    /**
     * Get all the configuration for the extension
     *
     * @param  array  $values
     * @return array
     */
    public function getConfig($values = [])
    {
        return [
            [
                'name' => 'client_id',
                'type' => 'text',
                'label' => 'Discord Client ID',
                'description' => 'The client ID from your Discord application',
                'required' => true,
            ],
            [
                'name' => 'client_secret',
                'type' => 'text',
                'label' => 'Discord Client Secret',
                'description' => 'The client secret from your Discord application',
                'required' => true,
            ],
            [
                'name' => 'bot_token',
                'type' => 'text',
                'label' => 'Discord Bot Token',
                'description' => 'The bot token for assigning roles',
                'required' => true,
            ],
            [
                'name' => 'role_id',
                'type' => 'text',
                'label' => 'Discord Role ID',
                'description' => 'The role ID to assign to users with active services',
                'required' => true,
                'default' => '1037629681819725855',
            ],
            [
                'name' => 'guild_id',
                'type' => 'text',
                'label' => 'Discord Guild ID',
                'description' => 'The guild/server ID where the role will be assigned',
                'required' => true,
            ],
        ];
    }

    public function installed()
    {
        ExtensionHelper::runMigrations('extensions/Others/DiscordLink/database/migrations');
    }

    public function uninstalled()
    {
        ExtensionHelper::rollbackMigrations('extensions/Others/DiscordLink/database/migrations');
    }

    public function boot()
    {
        require __DIR__ . '/routes/web.php';
        View::addNamespace('discordlink', __DIR__ . '/resources/views');

        User::resolveRelationUsing('discordLink', function (User $userModel) {
            return $userModel->hasOne(DiscordLinkModel::class, 'user_id');
        });

        // Hook onto main navigation (Home, Shop)
        Event::listen('navigation', function () {
            // Add navigation if routes exist
            if (Route::has('discordlink.redirect') && Route::has('discordlink.unlink')) {
                $user = auth()->user();
                $isLinked = $user && $user->discordLink;
                
                return [
                    'name' => $isLinked ? 'Unlink Discord' : 'Link Discord',
                    'url' => $isLinked ? route('discordlink.unlink') : route('discordlink.redirect'),
                    'icon' => 'ri-discord',
                    'separator' => true,
                    'condition' => true,
                    'spa' => false,
                ];
            }
        });
    }
}
