<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Site identity & contact details
    |--------------------------------------------------------------------------
    |
    | Central, client-editable settings used by the header, footer, WhatsApp
    | button and contact methods. Override the sensitive values in the .env
    | file rather than hardcoding them here.
    |
    */

    'brand' => env('APP_NAME', 'TurkeyMed'),

    // Primary phone number in international format (used for tel: links).
    'phone' => env('SITE_PHONE', '+902120000000'),

    // WhatsApp number in international format, digits only (used for wa.me links).
    'whatsapp' => env('SITE_WHATSAPP', '905550000000'),

    // Public contact email address.
    'email' => env('SITE_EMAIL', 'hello@turkeymed.net'),

];
