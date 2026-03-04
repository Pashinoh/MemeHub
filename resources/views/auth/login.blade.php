<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="space-y-5 text-center">
        <h1 class="text-xl font-semibold text-slate-100">Masuk ke MemeHub</h1>

        <a href="{{ route('auth.google.redirect') }}"
           class="inline-flex w-full items-center justify-center gap-2 rounded-md border border-slate-600 bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 focus:ring-offset-slate-900">
            <svg class="h-4 w-4" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="#EA4335" d="M12 10.2v3.9h5.4c-.2 1.2-.9 2.2-1.9 2.9l3.1 2.4c1.8-1.7 2.9-4.1 2.9-7 0-.7-.1-1.4-.2-2H12z"/>
                <path fill="#34A853" d="M12 22c2.6 0 4.7-.8 6.3-2.3l-3.1-2.4c-.9.6-1.9 1-3.2 1-2.4 0-4.5-1.6-5.2-3.9H3.6v2.5C5.2 20.1 8.3 22 12 22z"/>
                <path fill="#4A90E2" d="M6.8 14.4c-.2-.6-.3-1.2-.3-1.9s.1-1.3.3-1.9V8.1H3.6A10 10 0 002.5 12c0 1.6.4 3.1 1.1 4.4l3.2-2z"/>
                <path fill="#FBBC05" d="M12 6.7c1.4 0 2.7.5 3.6 1.4l2.7-2.7C16.7 3.9 14.6 3 12 3 8.3 3 5.2 4.9 3.6 8.1l3.2 2.5c.7-2.3 2.8-3.9 5.2-3.9z"/>
            </svg>
            Masuk dengan Google
        </a>
    </div>
</x-guest-layout>
