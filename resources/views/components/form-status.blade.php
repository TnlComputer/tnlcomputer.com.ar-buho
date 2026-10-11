@if (session('sent'))
    <div class="mb-6 flex gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-200" role="status">
        <x-icon name="check" class="mt-0.5 size-4 shrink-0" />
        <p><strong>{{ __('¡Gracias! Recibimos tu mensaje.') }}</strong> {{ __('Te respondemos a la brevedad.') }}</p>
    </div>
@endif
@if ($errors->any())
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-900 dark:border-red-900 dark:bg-red-950/50 dark:text-red-200" role="alert">
        {{ __('Revisá los campos marcados.') }}
    </div>
@endif
