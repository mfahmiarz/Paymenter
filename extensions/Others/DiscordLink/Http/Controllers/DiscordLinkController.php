<?php

namespace Paymenter\Extensions\Others\DiscordLink\Http\Controllers;

use App\Helpers\ExtensionHelper;
use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Paymenter\Extensions\Others\DiscordLink\Models\DiscordLink as DiscordLinkModel;

class DiscordLinkController extends Controller
{
    private function getConfig()
    {
        $ext = \App\Models\Extension::where('extension', 'DiscordLink')->first();
        if ($ext) {
            return $ext->settings->pluck('value', 'key')->toArray();
        }
        return [];
    }

    public function index()
    {
        $user = auth()->user();
        $discordLink = $user->discordLink;
        $hasActiveService = $user->services()->where('status', Service::STATUS_ACTIVE)->count() > 0;

        return view('discordlink::index', [
            'discordLink' => $discordLink,
            'hasActiveService' => $hasActiveService,
        ]);
    }

    public function redirectToDiscord()
    {
        $config = $this->getConfig();
        
        if (empty($config) || !isset($config['client_id']) || !$config['client_id']) {
            return redirect()->route('home')->with('error', 'Discord Client ID is not configured');
        }

        $state = bin2hex(random_bytes(16));
        session(['discord_oauth_state' => $state]);

        $params = [
            'client_id' => $config['client_id'],
            'redirect_uri' => route('discordlink.callback'),
            'response_type' => 'code',
            'scope' => 'identify guilds.join',
            'state' => $state,
        ];

        $url = 'https://discord.com/oauth2/authorize?' . http_build_query($params);

        return redirect()->away($url);
    }

    public function handleCallback(Request $request)
    {
        try {
            $config = $this->getConfig();
            
            if (empty($config) || !isset($config['client_id']) || !isset($config['client_secret']) || !$config['client_id'] || !$config['client_secret']) {
                return redirect()->route('home')->with('error', 'Discord configuration is incomplete');
            }

            // Verify state
            if ($request->state !== session('discord_oauth_state')) {
                return redirect()->route('home')->with('error', 'Invalid state parameter');
            }

            if (!$request->code) {
                return redirect()->route('home')->with('error', 'No authorization code provided');
            }

            // Exchange code for access token
            $response = Http::asForm()->post('https://discord.com/api/oauth2/token', [
                'client_id' => $config['client_id'],
                'client_secret' => $config['client_secret'],
                'grant_type' => 'authorization_code',
                'code' => $request->code,
                'redirect_uri' => route('discordlink.callback'),
            ]);

            if (!$response->successful()) {
                return redirect()->route('home')->with('error', 'Failed to exchange authorization code');
            }

            $tokenData = $response->json();
            $accessToken = $tokenData['access_token'];

            // Get user info from Discord
            $userResponse = Http::withToken($accessToken)->get('https://discord.com/api/users/@me');

            if (!$userResponse->successful()) {
                return redirect()->route('home')->with('error', 'Failed to get user information from Discord');
            }

            $discordUser = $userResponse->json();
            $discordUserId = $discordUser['id'];
            $discordUsername = $discordUser['username'] . '#' . $discordUser['discriminator'];

            // Check if user has at least 1 active service
            $user = auth()->user();
            $activeServiceCount = $user->services()->where('status', Service::STATUS_ACTIVE)->count();

            if ($activeServiceCount < 1) {
                return redirect()->route('home')->with('error', 'Anda harus memiliki minimal 1 layanan aktif untuk menghubungkan akun Discord Anda. Silakan beli layanan terlebih dahulu.');
            }

            // Assign Discord role
            $roleId = $config['role_id'] ?? '1037629681819725855';
            $guildId = $config['guild_id'] ?? null;
            $botToken = $config['bot_token'] ?? null;

            $roleAssigned = false;
            $roleError = null;

            if ($guildId && $botToken) {
                // Assign the role using bot token (with "Bot " prefix)
                $roleResponse = Http::withHeaders([
                    'Authorization' => 'Bot ' . $botToken,
                    'Content-Type' => 'application/json',
                ])->put(
                    "https://discord.com/api/guilds/{$guildId}/members/{$discordUserId}/roles/{$roleId}"
                );


                if ($roleResponse->successful()) {
                    $roleAssigned = true;
                } else {
                    $roleError = $roleResponse->body();
                    \Log::error('Discord role assignment failed', [
                        'discord_user_id' => $discordUserId,
                        'guild_id' => $guildId,
                        'role_id' => $roleId,
                        'role_response' => $roleError,
                    ]);
                }
            }

            // Save or update Discord link
            $discordLink = DiscordLinkModel::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'discord_user_id' => $discordUserId,
                    'discord_username' => $discordUsername,
                    'access_token' => $tokenData['access_token'] ?? null,
                    'refresh_token' => $tokenData['refresh_token'] ?? null,
                    'token_expires_at' => isset($tokenData['expires_in']) ? now()->addSeconds($tokenData['expires_in']) : null,
                ]
            );

            if ($roleAssigned) {
                return redirect()->route('home')->with('success', 'Akun Discord Anda berhasil terhubung dan role telah ditambahkan.');
            } else {
                $message = 'Akun Discord Anda berhasil terhubung. ';
                if (!$guildId || !$botToken) {
                    $message .= 'Namun role tidak dapat ditambahkan karena konfigurasi bot tidak lengkap.';
                } else {
                    $message .= 'Namun role tidak dapat ditambahkan. Pastikan Anda sudah join ke server Discord dan bot memiliki permission yang cukup.';
                }
                return redirect()->route('home')->with('warning', $message);
            }
        } catch (\Exception $e) {
            \Log::error('Discord callback error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('home')->with('error', 'Terjadi kesalahan saat memproses callback Discord: ' . $e->getMessage());
        }
    }

    public function unlink()
    {
        $user = auth()->user();
        $discordLink = $user->discordLink;

        if ($discordLink) {
            $discordLink->delete();
            return redirect()->route('home')->with('success', 'Akun Discord Anda berhasil diputus.');
        }

        return redirect()->route('home')->with('error', 'Tidak ada akun Discord yang terhubung.');
    }
}
