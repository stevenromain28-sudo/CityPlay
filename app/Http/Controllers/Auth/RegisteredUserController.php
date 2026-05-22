<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\TwoFactorCodeMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Auth/Register', [
            'adminRequest' => $request->query('admin_request') === '1',
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'admin_request' => 'nullable',
            'requested_city' => 'required_if:admin_request,true,1|nullable|string|max:255',
        ]);

        $isAdminRequest = $request->boolean('admin_request');

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'admin_request_status' => $isAdminRequest ? 'pending' : null,
            'requested_city' => $isAdminRequest ? $request->requested_city : null,
        ]);

        $user->assignRole('player');

        event(new Registered($user));

        // Generate a 6-digit random code for 2FA
        $code = sprintf("%06d", mt_rand(100000, 999999));

        $user->forceFill([
            'two_factor_code' => $code,
            'two_factor_expires_at' => now()->addMinutes(15),
        ])->save();

        // Send Email
        Mail::to($user->email)->send(new TwoFactorCodeMail($code));

        // Store user identifier temporarily in session
        $request->session()->put('login.two_factor_id', $user->id);
        $request->session()->put('login.remember', false); // No remember for new registration

        return redirect()->route('login.two-factor');
    }
}
