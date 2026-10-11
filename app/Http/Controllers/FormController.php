<?php

namespace App\Http\Controllers;

use App\Mail\NewMessage;
use App\Models\Message;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class FormController extends Controller
{
    public function contact(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'subject' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string', 'max:4000'],
        ]);

        return $this->store($request, 'contacto', $data);
    }

    public function service(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:160'],
            'subject' => ['required', Rule::in(config('tnl.service_reasons'))],
            'equipment' => ['nullable', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:4000'],
        ]);

        if (filled($data['equipment'] ?? null)) {
            $data['body'] = "Equipo: {$data['equipment']}\n\n{$data['body']}";
        }
        unset($data['equipment']);

        return $this->store($request, 'service', $data);
    }

    private function store(Request $request, string $type, array $data): RedirectResponse
    {
        // Honeypot: los bots completan el campo oculto; les respondemos "ok" sin guardar nada.
        if ($request->filled('website')) {
            return back()->with('sent', true);
        }

        $message = Message::create($data + ['type' => $type, 'ip' => $request->ip(), 'locale' => app()->getLocale()]);

        try {
            Mail::to(config('tnl.notify.'.$type))->locale('es')->send(new NewMessage($message));
        } catch (\Throwable $e) {
            // El mensaje queda guardado en /admin aunque falle el mail.
            Log::error("No se pudo enviar el mail del mensaje #{$message->id}", ['error' => $e->getMessage()]);
        }

        return back()->with('sent', true);
    }
}
