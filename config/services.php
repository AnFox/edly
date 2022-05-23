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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AMAZON_SES_REGION', 'us-east-1'),
    ],

    'cloud-payments' => [
        'public-key' => env('CLOUD_PAYMENTS_PUBLIC_KEY'),
        'private-key' => env('CLOUD_PAYMENTS_PRIVATE_KEY'),
    ],

    // Heads Up! Fallback settings are for the ATOL test environment only
    'atol-online' => [
        'sale_point' => env('ATOL_SALE_POINT', 'https://v4.online.atol.ru'),
        'inn' => env('ATOL_INN', '5544332219'),
        'login' => env('ATOL_LOGIN', 'v4-online-atol-ru'),
        'password' => env('ATOL_PASSWORD', 'iGFFuihss'),
        'group' => env('ATOL_GROUP', 'v4-online-atol-ru_4179'),
    ],

];
