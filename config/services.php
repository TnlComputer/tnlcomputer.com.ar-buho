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

];
