<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Models\Setting;
use App\Models\SocialLink;
use App\Support\Locale;
use Illuminate\View\View;

class ContactController extends Controller
{
    /** Contact page, default language: /contact */
    public function show(): View
    {
        return $this->render(Locale::DEFAULT);
    }

    /** Localized contact page: /{locale}/contact */
    public function showLocalized(string $locale): View
    {
        abort_unless(Locale::isSupported($locale), 404);

        return $this->render($locale);
    }

    private function render(string $language): View
    {
        // Group published offices by their default-locale country (stable key),
        // preserving sort order within each group.
        $offices = Office::published()
            ->orderBy('sort_order')
            ->get()
            ->groupBy(fn (Office $office): string => $office->translate('country', Locale::DEFAULT) ?? '');

        return view('contact.show', [
            'language' => $language,
            'heroEyebrow' => Setting::translated('contact.hero_eyebrow', $language),
            'heroTitle' => Setting::translated('contact.hero_title', $language),
            'heroText' => Setting::translated('contact.hero_text', $language),
            'methodWhatsappDesc' => Setting::translated('contact.method_whatsapp_desc', $language),
            'methodPhoneDesc' => Setting::translated('contact.method_phone_desc', $language),
            'methodEmailDesc' => Setting::translated('contact.method_email_desc', $language),
            'hours' => Setting::translated('contact.hours', $language),
            'formEmbed' => Setting::get('contact.form_embed'),
            'mapEmbed' => Setting::get('contact.map_embed'),
            'officeGroups' => $offices,
            'socialLinks' => SocialLink::published()->orderBy('sort_order')->get(),
        ]);
    }
}
