<div class="container mt-14">
    <div class="px-2">

        <h1 class="text-2xl font-bold mb-6">Discord Link</h1>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-background-secondary p-6 rounded-lg">
            @if ($discordLink)
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-2">Akun Discord Terhubung</h2>
                    <p class="text-gray-500 mb-4">Akun Discord Anda saat ini terhubung dengan Paymenter.</p>
                    
                    <div class="bg-background p-4 rounded-lg mb-4">
                        <p class="text-lg">
                            <span class="font-semibold">Username:</span> {{ $discordLink->discord_username }}
                        </p>
                        <p class="text-sm text-gray-500">
                            <span class="font-semibold">Discord ID:</span> {{ $discordLink->discord_user_id }}
                        </p>
                    </div>

                    @if ($hasActiveService)
                        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">
                            <p class="font-semibold">✓ Status Aktif</p>
                            <p class="text-sm">Anda memiliki layanan aktif dan telah menerima role Discord.</p>
                        </div>
                    @else
                        <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded mb-4">
                            <p class="font-semibold">⚠ Perhatian</p>
                            <p class="text-sm">Anda tidak memiliki layanan aktif saat ini. Role Discord mungkin tidak dapat ditambahkan kembali jika dihapus.</p>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('discordlink.unlink') }}" onsubmit="return confirm('Apakah Anda yakin ingin memutus koneksi akun Discord?');">
                        @csrf
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded">
                            Putus Koneksi
                        </button>
                    </form>
                </div>
            @else
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-2">Hubungkan Akun Discord</h2>
                    <p class="text-gray-500 mb-4">
                        Hubungkan akun Discord Anda dengan Paymenter untuk mendapatkan role khusus secara otomatis.
                    </p>

                    @if ($hasActiveService)
                        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">
                            <p class="font-semibold">✓ Layanan Aktif Terdeteksi</p>
                            <p class="text-sm">Anda memiliki layanan aktif. Anda dapat menghubungkan akun Discord sekarang untuk mendapatkan role.</p>
                        </div>
                    @else
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                            <p class="font-semibold">✗ Tidak Ada Layanan Aktif</p>
                            <p class="text-sm">Anda harus memiliki minimal 1 layanan aktif untuk menghubungkan akun Discord. Silakan beli layanan terlebih dahulu.</p>
                        </div>
                    @endif

                    @if ($hasActiveService)
                        <a href="{{ route('discordlink.redirect') }}" class="inline-flex items-center bg-[#5865F2] hover:bg-[#4752C4] text-white font-semibold py-3 px-6 rounded-lg transition-colors">
                            <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>
                            </svg>
                            Hubungkan dengan Discord
                        </a>
                    @else
                        <a href="{{ route('services.index') }}" class="inline-flex items-center bg-gray-400 text-white font-semibold py-3 px-6 rounded-lg cursor-not-allowed">
                            <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>
                            </svg>
                            Hubungkan dengan Discord (Dinonaktifkan)
                        </a>
                    @endif
                </div>
            @endif
        </div>

    </div>
</div>
