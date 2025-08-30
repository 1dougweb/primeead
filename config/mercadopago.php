<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mercado Pago Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for Mercado Pago integration.
    | All settings are stored in the database and can be managed via admin panel.
    |
    */

    'webhook' => [
        'url' => env('MP_WEBHOOK_URL', '/webhook/mercadopago'),
        'secret_header' => 'x-signature',
        'request_id_header' => 'x-request-id',
        'timeout' => 30,
        'retry_attempts' => 3,
    ],

    'api' => [
        'base_url' => env('MP_API_BASE_URL', 'https://api.mercadopago.com'),
        'version' => 'v1',
        'timeout' => 30,
    ],

    'webhook_events' => [
        'payment.updated',
        'order.updated',
        'subscription.updated',
        'subscription_preapproval.updated',
    ],

    'status_mapping' => [
        'payment' => [
            'approved' => 'paid',
            'pending' => 'pending',
            'rejected' => 'failed',
            'cancelled' => 'cancelled',
            'refunded' => 'refunded',
            'charged_back' => 'charged_back',
            'in_process' => 'pending',
            'authorized' => 'pending',
        ],
        'order' => [
            'approved' => 'paid',
            'pending' => 'pending',
            'waiting_payment' => 'pending',
            'action_required' => 'pending',
            'rejected' => 'failed',
            'cancelled' => 'cancelled',
            'refunded' => 'refunded',
            'failed' => 'failed',
            'expired' => 'expired',
        ],
    ],

    'logging' => [
        'webhook_received' => true,
        'webhook_processed' => true,
        'webhook_errors' => true,
        'api_calls' => true,
        'payment_updates' => true,
    ],
];
