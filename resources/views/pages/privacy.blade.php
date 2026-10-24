@extends('layouts.site')

@section('title', __('Política de privacidad'))
@section('description', __('Qué datos personales recolecta TNL Computer, para qué los usa, con quién los comparte y cómo ejercer tus derechos de acceso, rectificación y supresión.'))

@section('content')
    <x-page-header :eyebrow="__('Legal')" :title="__('Política de privacidad')">
        {{ __('Cómo cuidamos los datos que nos dejás cuando comprás o nos escribís.') }}
    </x-page-header>

    <div class="container-x max-w-3xl space-y-12 py-16 md:py-20">
        <section id="datos">
            <h2 class="text-2xl font-bold">{{ __('Qué datos recolectamos') }}</h2>
            <div class="prose-tnl mt-4 text-zinc-600 dark:text-zinc-400">
                <p>{{ __('Cuando hacés un pedido: nombre, email, teléfono, DNI, dirección de envío y los productos que compraste. Cuando nos escribís por un formulario: nombre, email, teléfono y tu mensaje. Si dejás una reseña: el nombre con el que la firmás y tu comentario.') }}</p>
                <p>{{ __('También registramos la dirección IP desde la que se hace un pedido o una reseña, para prevenir fraudes. No guardamos datos de tarjetas: los pagos los procesa Mercado Pago en su propio sitio.') }}</p>
            </div>
        </section>

        <section id="uso">
            <h2 class="text-2xl font-bold">{{ __('Para qué los usamos') }}</h2>
            <div class="prose-tnl mt-4 text-zinc-600 dark:text-zinc-400">
                <p>{{ __('Para preparar y enviar tu pedido, avisarte el estado de la compra, atender garantías y devoluciones y responder tus consultas. No vendemos ni alquilamos tus datos, y no te mandamos publicidad por email sin tu consentimiento.') }}</p>
            </div>
        </section>

        <section id="terceros">
            <h2 class="text-2xl font-bold">{{ __('Con quién los compartimos') }}</h2>
            <div class="prose-tnl mt-4 text-zinc-600 dark:text-zinc-400">
                <p>{{ __('Solo con quienes necesitamos para cumplir con tu compra: Mercado Pago (para cobrar), la empresa de envíos (nombre, teléfono y dirección de entrega) y nuestro proveedor de hosting, donde se guarda el sitio. También podemos tener que informarlos si lo pide una autoridad competente.') }}</p>
                <p>{{ __('Usamos Google Analytics para saber cuántas personas visitan el sitio y qué páginas ven. Google usa cookies para eso; podés bloquearlas desde la configuración de tu navegador sin que el sitio deje de funcionar.') }}</p>
                <p>{{ __('Publicamos nuestros productos en nuestras propias cuentas de redes sociales (Facebook, Instagram, Threads y TikTok) mediante una aplicación propia. Esa aplicación solo publica contenido nuestro y no accede a datos de otros usuarios de esas plataformas.') }}</p>
            </div>
        </section>

        <section id="cookies">
            <h2 class="text-2xl font-bold">{{ __('Cookies') }}</h2>
            <div class="prose-tnl mt-4 text-zinc-600 dark:text-zinc-400">
                <p>{{ __('El sitio usa una cookie de sesión para recordar tu carrito, y guarda en tu navegador la preferencia de tema claro u oscuro. Además están las cookies de Google Analytics mencionadas arriba.') }}</p>
            </div>
        </section>

        <section id="derechos">
            <h2 class="text-2xl font-bold">{{ __('Tus derechos') }}</h2>
            <div class="prose-tnl mt-4 text-zinc-600 dark:text-zinc-400">
                <p>{{ __('Podés pedirnos en cualquier momento ver, corregir o borrar tus datos escribiendo a :email. Los datos de compras los conservamos el tiempo que exigen las normas fiscales y de defensa del consumidor.', ['email' => config('tnl.email')]) }}</p>
                <p>{{ __('El titular de los datos personales tiene la facultad de ejercer el derecho de acceso a los mismos en forma gratuita a intervalos no inferiores a seis meses, salvo que se acredite un interés legítimo al efecto conforme lo establecido en el artículo 14, inciso 3 de la Ley N° 25.326. La Agencia de Acceso a la Información Pública, en su carácter de órgano de control de la Ley N° 25.326, tiene la atribución de atender las denuncias y reclamos que interpongan quienes resulten afectados en sus derechos por incumplimiento de las normas vigentes en materia de protección de datos personales.') }}</p>
            </div>
        </section>

        <section class="card p-6">
            <h2 class="font-sans text-base font-semibold">{{ __('¿Dudas sobre tus datos?') }}</h2>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">{{ __('Escribinos por WhatsApp al :phone o a :email y te respondemos a la brevedad.', ['phone' => config('tnl.whatsapp_label'), 'email' => config('tnl.email')]) }}</p>
            <p class="mt-2 text-xs text-zinc-500">{{ __('Última actualización: :date', ['date' => '23/10/2026']) }}</p>
        </section>
    </div>
@endsection
