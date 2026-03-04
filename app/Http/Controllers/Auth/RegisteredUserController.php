<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Support\ReservedAccountNames;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (ReservedAccountNames::isBlocked((string) $value)) {
                        $fail('Nama akun tersebut tidak diperbolehkan.');
                    }
                },
            ],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'cf-turnstile-response' => ['required', 'string'],
            'company' => ['nullable', 'max:0'],
        ], [
            'cf-turnstile-response.required' => 'Please complete the human verification first.',
            'company.max' => 'Registration request is invalid.',
        ]);

        $ipKey = 'register:ip:'.$request->ip();
        $emailIpKey = 'register:email-ip:'.Str::lower((string) $request->input('email')).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($ipKey, 3)) {
            $seconds = RateLimiter::availableIn($ipKey);

            throw ValidationException::withMessages([
                'email' => 'Too many registrations from this IP. Please try again in '.$seconds.' seconds.',
            ]);
        }

        if (RateLimiter::tooManyAttempts($emailIpKey, 5)) {
            $seconds = RateLimiter::availableIn($emailIpKey);

            throw ValidationException::withMessages([
                'email' => 'Too many attempts for this email/IP pair. Please try again in '.$seconds.' seconds.',
            ]);
        }

        RateLimiter::hit($ipKey, 86400);
        RateLimiter::hit($emailIpKey, 3600);

        $domain = Str::lower((string) Str::after((string) $request->input('email'), '@'));
        $defaultBlockedDomains = [
            'mailinator.com',
            '10minutemail.com',
            'guerrillamail.com',
            'tempmail.com',
            'trashmail.com',
            'yopmail.com',
        ];
        $extraBlockedDomains = array_map('strtolower', (array) config('services.registration.blocked_email_domains', []));
        $blockedDomains = array_unique(array_filter(array_merge($defaultBlockedDomains, $extraBlockedDomains)));

        if (in_array($domain, $blockedDomains, true)) {
            throw ValidationException::withMessages([
                'email' => 'Temporary email domains are not allowed. Please use your primary email.',
            ]);
        }

        $turnstileSecret = config('services.turnstile.secret_key');

        if (! is_string($turnstileSecret) || $turnstileSecret === '') {
            throw ValidationException::withMessages([
                'cf-turnstile-response' => 'Turnstile is not configured on the server yet.',
            ]);
        }

        $turnstileResponse = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => $turnstileSecret,
            'response' => (string) $request->input('cf-turnstile-response'),
            'remoteip' => $request->ip(),
        ]);

        if (! $turnstileResponse->ok() || ! (bool) $turnstileResponse->json('success')) {
            throw ValidationException::withMessages([
                'cf-turnstile-response' => 'Human verification failed. Please try again.',
            ]);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        try {
            event(new Registered($user));
        } catch (Throwable $exception) {
            report($exception);
        }

        return redirect(RouteServiceProvider::HOME);
    }
}
