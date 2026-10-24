@extends('layouts.site')

@section('title', __('Términos y condiciones'))
@section('description', __('Condiciones de uso del sitio y de compra en la tienda online de TNL Computer: precios, pagos, envíos, garantía y responsabilidad.'))

@section('content')
    <x-page-header :eyebrow="__('Legal')" :title="__('Términos y condiciones')">
        {{ __('Las reglas para usar el sitio y comprar en nuestra tienda.') }}
    </x-page-header>

    <div class="container-x max-w-3xl space-y-12 py-16 md:py-20">
        <section id="uso">
            <h2 class="text-2xl font-bold">{{ __('Uso del sitio') }}</h2>
            <div class="prose-tnl mt-4 text-zinc-600 dark:text-zinc-400">
                <p>{{ __('Este sitio es de TNL Computer, de la Ciudad de Buenos Aires, Argentina. Al usarlo o comprar en la tienda aceptás estos términos. Si no estás de acuerdo con ellos, te pedimos que no uses el sitio.') }}</p>
                <p>{{ __('Te comprometés a dar datos verdaderos al hacer un pedido y a no usar el sitio para fines ilegales ni para dañarlo o sobrecargarlo.') }}</p>
            </div>
        </section>

        <section id="precios">
            <h2 class="text-2xl font-bold">{{ __('Precios y stock') }}</h2>
            <div class="prose-tnl mt-4 text-zinc-600 dark:text-zinc-400">
                <p>{{ __('Los precios están en pesos argentinos. Pueden cambiar sin previo aviso, pero el precio que vale es el que figura cuando confirmás el pedido.') }}</p>
                <p>{{ __('El stock se actualiza con frecuencia pero puede no estar al día. Si un producto de tu pedido no está disponible, te avisamos y te ofrecemos otro equivalente o te devolvemos el dinero. Ante un error evidente de precio o de publicación, podemos cancelar el pedido y reintegrar lo pagado.') }}</p>
                <p>{{ __('Las fotos son ilustrativas; las características de cada producto son las que informa el fabricante.') }}</p>
            </div>
        </section>

        <section id="compras">
            <h2 class="text-2xl font-bold">{{ __('Compras y pagos') }}</h2>
            <div class="prose-tnl mt-4 text-zinc-600 dark:text-zinc-400">
                <p>{{ __('El pedido queda confirmado cuando se acredita el pago. Los pagos online se hacen a través de Mercado Pago, con los medios y cuotas que ofrezca en ese momento.') }}</p>
            </div>
        </section>

        <section id="envios">
            <h2 class="text-2xl font-bold">{{ __('Envíos, devoluciones y garantía') }}</h2>
            <div class="prose-tnl mt-4 text-zinc-600 dark:text-zinc-400">
                <p>
                    {{ __('Los costos y plazos de envío, el derecho de arrepentimiento de 10 días, los cambios y la garantía están explicados en') }}
                    <a href="{{ lroute('policies') }}" class="font-semibold text-brand-600 hover:underline dark:text-brand-400">{{ __('Envíos, cambios y devoluciones') }}</a>.
                </p>
            </div>
        </section>

        <section id="responsabilidad">
            <h2 class="text-2xl font-bold">{{ __('Propiedad intelectual y responsabilidad') }}</h2>
            <div class="prose-tnl mt-4 text-zinc-600 dark:text-zinc-400">
                <p>{{ __('Los textos, diseños y el logo del sitio son de TNL Computer; las marcas y fotos de productos pertenecen a sus fabricantes. No se pueden copiar ni usar con fines comerciales sin autorización.') }}</p>
                <p>{{ __('Hacemos lo posible para que el sitio funcione siempre y sin errores, pero no podemos garantizarlo. Nada de estos términos limita los derechos que te da la Ley 24.240 de Defensa del Consumidor.') }}</p>
                <p>{{ __('El uso de tus datos personales está explicado en la') }}
                    <a href="{{ lroute('privacy') }}" class="font-semibold text-brand-600 hover:underline dark:text-brand-400">{{ __('Política de privacidad') }}</a>.
                </p>
            </div>
        </section>

        <section id="cambios">
            <h2 class="text-2xl font-bold">{{ __('Cambios y ley aplicable') }}</h2>
            <div class="prose-tnl mt-4 text-zinc-600 dark:text-zinc-400">
                <p>{{ __('Podemos actualizar estos términos; los cambios rigen desde que se publican y no afectan a los pedidos ya confirmados. Se aplican las leyes de la República Argentina.') }}</p>
            </div>
        </section>

        <section class="card p-6">
            <h2 class="font-sans text-base font-semibold">{{ __('¿Tenés alguna duda?') }}</h2>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">{{ __('Escribinos por WhatsApp al :phone o a :email y te respondemos a la brevedad.', ['phone' => config('tnl.whatsapp_label'), 'email' => config('tnl.email')]) }}</p>
            <p class="mt-2 text-xs text-zinc-500">{{ __('Última actualización: :date', ['date' => '23/10/2026']) }}</p>
        </section>
    </div>
@endsection
