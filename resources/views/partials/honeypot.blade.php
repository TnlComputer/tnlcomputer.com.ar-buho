{{-- Trampas para bots: campo invisible que una persona nunca completa y hora de carga del formulario (ver App\Support\FormGuard). --}}
<div class="absolute -left-[9999px]" aria-hidden="true">
    <label>{{ __('No completar') }} <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
</div>
<input type="hidden" name="{{ \App\Support\FormGuard::FIELD }}" value="{{ \App\Support\FormGuard::stamp() }}">
