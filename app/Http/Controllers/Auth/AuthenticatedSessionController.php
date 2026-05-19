<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

use App\Models\User;
use App\Mail\TwoFactorCodeMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->ensureIsNotRateLimited();

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            RateLimiter::hit($request->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($request->throttleKey());

        // Generate a 6-digit random code
        $code = sprintf("%06d", mt_rand(100000, 999999));

        $user->forceFill([
            'two_factor_code' => $code,
            'two_factor_expires_at' => now()->addMinutes(15),
        ])->save();

        // Send Email
        Mail::to($user->email)->send(new TwoFactorCodeMail($code));

        // Store user identifier temporarily in session
        $request->session()->put('login.two_factor_id', $user->id);
        $request->session()->put('login.remember', $request->boolean('remember'));

        return redirect()->route('login.two-factor');
    }

    /**
     * Display the 2FA verification challenge view.
     */
    public function showTwoFactorForm(Request $request): Response|RedirectResponse
    {
        if (! $request->session()->has('login.two_factor_id')) {
            return redirect()->route('login');
        }

        return Inertia::render('Auth/TwoFactorChallenge', [
            'status' => session('status'),
        ]);
    }

    /**
     * Handle the 2FA code verification.
     */
    public function verifyTwoFactor(Request $request): RedirectResponse
    {
        if (! $request->session()->has('login.two_factor_id')) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $userId = $request->session()->get('login.two_factor_id');
        $user = User::findOrFail($userId);

        if ($user->two_factor_code !== $request->code || now()->greaterThan($user->two_factor_expires_at)) {
            throw ValidationException::withMessages([
                'code' => 'Le code de double authentification saisi est invalide ou a expiré.',
            ]);
        }

        // Clean user 2FA fields
        $user->forceFill([
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
        ])->save();

        // Authenticate
        Auth::login($user, $request->session()->pull('login.remember', false));

        $request->session()->forget('login.two_factor_id');
        $request->session()->regenerate();

        // Redirect based on role
        if ($user->hasRole('admin') || $user->hasRole('super_admin')) {
            return redirect()->intended(route('admin.dashboard', absolute: false));
        }

        if ($user->hasRole('player')) {
            return redirect()->intended(route('player.dashboard', absolute: false));
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Resend the 2FA code via email.
     */
    public function resendTwoFactor(Request $request): RedirectResponse
    {
        if (! $request->session()->has('login.two_factor_id')) {
            return redirect()->route('login');
        }

        $userId = $request->session()->get('login.two_factor_id');
        $user = User::findOrFail($userId);

        // Regenerate 2FA code
        $code = sprintf("%06d", mt_rand(100000, 999999));

        $user->forceFill([
            'two_factor_code' => $code,
            'two_factor_expires_at' => now()->addMinutes(15),
        ])->save();

        Mail::to($user->email)->send(new TwoFactorCodeMail($code));

        return back()->with('status', 'Un nouveau code de sécurité a été envoyé sur votre adresse e-mail.');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
