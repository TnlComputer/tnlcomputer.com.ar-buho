<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Mercado Pago Checkout Pro. Credenciales en mercadopago.com.ar/developers → Tus integraciones.
    'mercadopago' => [
        'access_token' => env('MP_ACCESS_TOKEN'),
        // Clave secreta de Webhooks (Tus integraciones → Webhooks). Con ella se valida la firma de cada aviso.
        'webhook_secret' => env('MP_WEBHOOK_SECRET'),
        'max_installments' => (int) env('MP_MAX_INSTALLMENTS', 12),
        // Medios que NO se ofrecen. Por defecto se excluye efectivo (Rapipago/Pago Fácil) porque demora la acreditación.
        'excluded_payment_types' => array_filter(explode(',', env('MP_EXCLUDED_PAYMENT_TYPES', 'ticket,atm'))),
        'statement_descriptor' => env('MP_STATEMENT_DESCRIPTOR', 'TNLCOMPUTER'),
        // Horas que el link de pago queda vigente.
        'expiration_hours' => (int) env('MP_EXPIRATION_HOURS', 48),
    ],

    // Publicación automática de productos en redes (php artisan tnl:redes). Pasos para sacar cada dato en .env.example.
    'social' => [
        // Horarios (hora de Buenos Aires) en que sale una publicación por día. Vacío = no se publica solo.
        'times' => array_filter(array_map('trim', explode(',', (string) env('SOCIAL_TIMES', '10:00,13:00,18:00,21:00')))),
        // Días que tiene que pasar para que un producto se vuelva a publicar.
        'repeat_days' => (int) env('SOCIAL_REPEAT_DAYS', 60),
        'graph_version' => env('META_GRAPH_VERSION', 'v23.0'),
        'facebook_page_id' => env('FACEBOOK_PAGE_ID'),
        'facebook_page_token' => env('FACEBOOK_PAGE_TOKEN'),
        // Token de larga duración de Instagram (60 días; el sitio lo renueva solo mientras se siga usando).
        'instagram_token' => env('INSTAGRAM_ACCESS_TOKEN'),
        'x_api_key' => env('X_API_KEY'),
        'x_api_secret' => env('X_API_SECRET'),
        'x_access_token' => env('X_ACCESS_TOKEN'),
        'x_access_secret' => env('X_ACCESS_TOKEN_SECRET'),
        // Token de larga duración de Threads (60 días; el sitio lo renueva solo mientras se siga usando).
        'threads_token' => env('THREADS_ACCESS_TOKEN'),
        'tiktok_client_key' => env('TIKTOK_CLIENT_KEY'),
        'tiktok_client_secret' => env('TIKTOK_CLIENT_SECRET'),
        'tiktok_refresh_token' => env('TIKTOK_REFRESH_TOKEN'),
        // Hasta que TikTok apruebe la app (auditoría), solo deja publicar en privado: SELF_ONLY.
        'tiktok_privacy' => env('TIKTOK_PRIVACY', 'PUBLIC_TO_EVERYONE'),
        // Dirección registrada en la app de TikTok a la que vuelve la autorización (tnl:tiktok-conectar).
        'tiktok_redirect_uri' => env('TIKTOK_REDIRECT_URI', 'https://www.tnlcomputer.com.ar/'),
    ],

];
