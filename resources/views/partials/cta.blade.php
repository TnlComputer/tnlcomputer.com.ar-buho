<section class="container-x mt-20">
    <div class="relative isolate overflow-hidden rounded-[2rem] bg-ink-900 px-6 py-14 text-center sm:px-12 dark:bg-ink-800">
        <div class="absolute inset-0 -z-10 bg-[radial-gradient(50%_80%_at_50%_0%,color-mix(in_oklab,var(--color-brand-600)_45%,transparent),transparent)]"></div>
        <h2 class="mx-auto max-w-2xl text-3xl font-bold text-white sm:text-4xl">{{ __('¿Tenés un proyecto o un equipo que no anda?') }}</h2>
        <p class="mx-auto mt-4 max-w-xl text-zinc-300">{{ __('Contanos qué necesitás y te respondemos con una propuesta concreta.') }}</p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ whatsapp_url(__('Hola TNL Computer, quería hacer una consulta.')) }}" target="_blank" rel="noopener" class="btn btn-whatsapp"><x-icon name="whatsapp" /> WhatsApp</a>
            <a href="{{ lroute('contact') }}" class="btn border border-white/20 text-white hover:bg-white/10"><x-icon name="mail" /> {{ __('Formulario de contacto') }}</a>
        </div>
    </div>
</section>
