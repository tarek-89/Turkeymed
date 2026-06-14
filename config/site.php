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

    // Optional allow-list (comma-separated) of emails permitted into the
    // Filament admin panel. Leave empty to allow any existing user.
    'admin_emails' => env('ADMIN_EMAILS', ''),

    /*
    |--------------------------------------------------------------------------
    | SEO: analytics, verification & social
    |--------------------------------------------------------------------------
    |
    | Set the GTM container and verification tokens in .env on production.
    | Leaving a value empty omits its tag entirely, so nothing renders (or
    | loads) in local/staging unless you opt in.
    |
    */

    // Master indexing switch. Set SITE_INDEXABLE=false on staging/subdomain
    // deployments so the whole site sends "noindex, nofollow" (header + meta)
    // and search engines never index it. Flip to true only on the live domain.
    'indexable' => env('SITE_INDEXABLE', true),

    // Google Tag Manager container id, e.g. "GTM-XXXXXX". Empty = no GTM.
    'gtm_id' => env('GTM_ID', ''),

    // Search engine ownership verification tokens (content of the meta tag).
    'google_site_verification' => env('GOOGLE_SITE_VERIFICATION', ''),
    'bing_site_verification' => env('BING_SITE_VERIFICATION', ''),

    // Default Open Graph / Twitter share image used when a page has no image
    // of its own. A branded 1200x630 asset shipped in /public.
    'og_image' => env('SITE_OG_IMAGE', '/og-default.png'),

];
