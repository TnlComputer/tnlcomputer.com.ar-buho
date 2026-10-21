<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AdminLogin;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AuthController extends Controller
{
    /** Minutos que tiene quien puso bien la contraseña para ingresar el código de verificación. */
    private const CHALLENGE_MINUTES = 5;

    public function create(): View
    {
        return view('admin.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Se valida sin iniciar sesión: con verificación en dos pasos, la sesión empieza recién con el código.
        $user = Auth::getProvider()->retrieveByCredentials($credentials);
        if (! $user || ! Auth::getProvider()->validateCredentials($user, $credentials)) {
            Log::warning('Ingreso al panel fallido', ['email' => $credentials['email'], 'ip' => $request->ip()]);

            return back()->withErrors(['email' => 'Email o contraseña incorrectos.'])->onlyInput('email');
        }

        if ($user->hasTwoFactor()) {
            $request->session()->put('login.two_factor', [
                'id' => $user->id, 'remember' => $request->boolean('remember'), 'until' => now()->addMinutes(self::CHALLENGE_MINUTES)->timestamp,
            ]);

            return redirect()->route('admin.two-factor');
        }

        return $this->login($request, $user, $request->boolean('remember'));
    }

    public function challenge(Request $request): View|RedirectResponse
    {
        if (! $this->pendingUser($request)) {
            return redirect()->route('admin.login');
        }

        return view('admin.two-factor');
    }

    public function verify(Request $request): RedirectResponse
    {
        $user = $this->pendingUser($request);
        if (! $user) {
            return redirect()->route('admin.login')->withErrors(['email' => 'Pasó demasiado tiempo: volvé a ingresar.']);
        }

        $data = $request->validate(['code' => ['nullable', 'string', 'max:20'], 'recovery_code' => ['nullable', 'string', 'max:20']]);

        $ok = filled($data['recovery_code'] ?? null)
            ? $user->useRecoveryCode($data['recovery_code'])
            : $user->verifyTwoFactorCode((string) ($data['code'] ?? ''));

        if (! $ok) {
            Log::warning('Código de verificación del panel incorrecto', ['email' => $user->email, 'ip' => $request->ip()]);

            return back()->withErrors(['code' => 'El código no es válido. Probá con el que muestra ahora la app.']);
        }

        $remember = (bool) $request->session()->pull('login.two_factor')['remember'];

        return $this->login($request, $user, $remember, usedRecoveryCode: filled($data['recovery_code'] ?? null));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function login(Request $request, User $user, bool $remember, bool $usedRecoveryCode = false): RedirectResponse
    {
        Auth::login($user, $remember);
        $request->session()->forget('login.two_factor');
        $request->session()->regenerate();

        Log::info('Ingreso al panel', ['email' => $user->email, 'ip' => $request->ip()]);
        // Aviso de cada ingreso: si no fuiste vos, te enterás en el momento.
        try {
            Mail::to($user->email)->send(new AdminLogin($user, $request->ip(), (string) $request->userAgent(), $usedRecoveryCode));
        } catch (\Throwable $e) {
            Log::error('No se pudo enviar el aviso de ingreso al panel', ['error' => $e->getMessage()]);
        }

        return redirect()->intended(route('admin.dashboard'));
    }

    /** Usuario que ya puso bien la contraseña y todavía tiene que ingresar el código. */
    private function pendingUser(Request $request): ?User
    {
        $pending = $request->session()->get('login.two_factor');
        if (! $pending || $pending['until'] < now()->timestamp) {
            $request->session()->forget('login.two_factor');

            return null;
        }

        return User::find($pending['id']);
    }
}
