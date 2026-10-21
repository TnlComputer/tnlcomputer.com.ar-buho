<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Totp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/** Verificación en dos pasos y cambio de contraseña del usuario logueado. */
class SecurityController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();
        $setup = null;

        // Mientras no se confirme, la clave nueva vive solo en la sesión.
        if (! $user->hasTwoFactor()) {
            $secret = $request->session()->get('two_factor.setup') ?? tap(Totp::generateSecret(), fn ($s) => $request->session()->put('two_factor.setup', $s));
            $setup = [
                'secret' => trim(chunk_split($secret, 4, ' ')),
                'qr' => Totp::qrSvg(Totp::uri($secret, $user->email, config('tnl.name'))),
            ];
        }

        return view('admin.security', [
            'user' => $user,
            'setup' => $setup,
            'recoveryCodes' => $request->session()->get('two_factor.codes'),
        ]);
    }

    public function enable(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);
        $user = $request->user();
        $secret = $request->session()->get('two_factor.setup');

        if ($user->hasTwoFactor() || ! $secret || ! Totp::verify($secret, $request->input('code'), "setup{$user->id}")) {
            return back()->withErrors(['code' => 'El código no coincide. Revisá que la hora del celular esté bien y probá con el código nuevo.']);
        }

        $user->forceFill(['two_factor_secret' => $secret, 'two_factor_confirmed_at' => now()])->save();
        $request->session()->forget('two_factor.setup');

        return redirect()->route('admin.security')
            ->with('status', 'Verificación en dos pasos activada.')
            ->with('two_factor.codes', $user->regenerateRecoveryCodes());
    }

    public function recoveryCodes(Request $request): RedirectResponse
    {
        $request->validate(['codes_password' => ['required', 'current_password']], ['current_password' => 'La contraseña no es correcta.']);

        return redirect()->route('admin.security')
            ->with('status', 'Códigos de recuperación nuevos: los anteriores ya no sirven.')
            ->with('two_factor.codes', $request->user()->regenerateRecoveryCodes());
    }

    public function disable(Request $request): RedirectResponse
    {
        $request->validate(['disable_password' => ['required', 'current_password']], ['current_password' => 'La contraseña no es correcta.']);
        $request->user()->disableTwoFactor();

        return redirect()->route('admin.security')->with('status', 'Verificación en dos pasos desactivada.');
    }

    public function password(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(12)->uncompromised()],
        ], [
            'current_password.current_password' => 'La contraseña actual no es correcta.',
            'password.uncompromised' => 'Esa contraseña apareció en filtraciones de datos conocidas: elegí otra.',
        ]);

        // Las sesiones abiertas en otros navegadores (y los "Recordarme") se cierran solas: el middleware
        // auth.session del panel compara el hash de la contraseña en cada pedido.
        $request->user()->forceFill(['password' => $request->input('password')])->save();

        return redirect()->route('admin.security')->with('status', 'Contraseña cambiada. Se cerraron las sesiones en otros dispositivos.');
    }
}
