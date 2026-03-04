<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Support\ReservedAccountNames;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        if (! $this->hasGoogleConfig()) {
            return redirect()
                ->route('login')
                ->with('status', 'Konfigurasi Google login belum lengkap. Isi GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, dan GOOGLE_REDIRECT_URI di .env.');
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $exception) {
            report($exception);

            $message = $exception->getMessage();
            if (str_contains($message, 'client_secret is missing')) {
                $message = 'GOOGLE_CLIENT_SECRET masih kosong di .env.';
            } elseif (str_contains($message, 'redirect_uri_mismatch')) {
                $message = 'GOOGLE_REDIRECT_URI tidak cocok dengan Google Console.';
            } else {
                $message = 'Login Google gagal. Silakan coba lagi.';
            }

            return redirect()
                ->route('login')
                ->with('status', $message);
        }

        $email = (string) ($googleUser->getEmail() ?? '');

        if ($email === '') {
            return redirect()
                ->route('login')
                ->with('status', 'Google tidak mengirim alamat email. Gunakan akun Google lain.');
        }

        $user = User::query()
            ->where('google_id', $googleUser->getId())
            ->orWhere('email', $email)
            ->first();

        if (! $user) {
            $name = (string) ($googleUser->getName() ?: Str::before($email, '@'));

            $user = User::create([
                'name' => ReservedAccountNames::sanitize($name),
                'email' => $email,
                'google_id' => (string) $googleUser->getId(),
                'avatar' => (string) ($googleUser->getAvatar() ?? ''),
                'email_verified_at' => now(),
                'password' => Hash::make(Str::random(32)),
            ]);
        } else {
            $user->forceFill([
                'google_id' => (string) $googleUser->getId(),
                'avatar' => (string) ($googleUser->getAvatar() ?? $user->avatar),
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();
        }

        Auth::login($user, true);

        request()->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    protected function hasGoogleConfig(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'))
            && filled(config('services.google.redirect'));
    }
}
