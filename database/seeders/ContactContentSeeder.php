<?php

namespace Database\Seeders;

use App\Models\Office;
use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Default Contact-page content (from the Aurora design), in all four locales.
 * Offices seed only when the table is empty; settings only when the key is
 * missing — admin edits are never overwritten.
 */
class ContactContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedOffices();
        $this->seedSettings();
    }

    private function seedOffices(): void
    {
        if (Office::query()->exists()) {
            return;
        }

        $turkiye = ['en' => 'Türkiye', 'fr' => 'Turquie', 'es' => 'Turquía', 'ar' => 'تركيا'];
        $uk = ['en' => 'United Kingdom', 'fr' => 'Royaume-Uni', 'es' => 'Reino Unido', 'ar' => 'المملكة المتحدة'];
        $uae = ['en' => 'U.A.E.', 'fr' => 'É.A.U.', 'es' => 'E.A.U.', 'ar' => 'الإمارات'];

        $offices = [
            [
                'country' => $turkiye,
                'sort_order' => 1,
                'name' => ['en' => 'Istanbul · Şişli', 'fr' => 'Istanbul · Şişli', 'es' => 'Estambul · Şişli', 'ar' => 'إسطنبول · شيشلي'],
                'address' => ['en' => "Büyükdere Cd. No: 000, Şişli\n34394 Istanbul, Türkiye", 'ar' => "شارع بيوكديري رقم 000، شيشلي\n34394 إسطنبول، تركيا"],
                'hours' => ['en' => 'Mon–Sat · 09:00–19:00', 'ar' => 'الإثنين–السبت · 09:00–19:00'],
                'badge' => ['en' => 'Headquarters', 'fr' => 'Siège', 'es' => 'Sede', 'ar' => 'المقر الرئيسي'],
                'phone' => '+90 212 000 00 00',
                'directions_url' => 'https://maps.google.com/',
            ],
            [
                'country' => $turkiye,
                'sort_order' => 2,
                'name' => ['en' => 'Istanbul · Ataşehir', 'ar' => 'إسطنبول · أتاشهير'],
                'address' => ['en' => "Atatürk Mah. No: 000, Ataşehir\n34758 Istanbul, Türkiye", 'ar' => "حي أتاتورك رقم 000، أتاشهير\n34758 إسطنبول، تركيا"],
                'hours' => ['en' => 'Mon–Sat · 09:00–19:00', 'ar' => 'الإثنين–السبت · 09:00–19:00'],
                'phone' => '+90 216 000 00 00',
                'directions_url' => 'https://maps.google.com/',
            ],
            [
                'country' => $uk,
                'sort_order' => 3,
                'name' => ['en' => 'London · Patient liaison', 'ar' => 'لندن · تنسيق المرضى'],
                'address' => ['en' => "000 Oxford Street, Mayfair\nLondon W1B 0AA, United Kingdom", 'ar' => "000 شارع أكسفورد، مايفير\nلندن W1B 0AA، المملكة المتحدة"],
                'hours' => ['en' => 'Mon–Fri · 09:00–17:00 (GMT)', 'ar' => 'الإثنين–الجمعة · 09:00–17:00 (GMT)'],
                'phone' => '+44 20 0000 0000',
                'directions_url' => 'https://maps.google.com/',
            ],
            [
                'country' => $uae,
                'sort_order' => 4,
                'name' => ['en' => 'Dubai · Patient liaison', 'ar' => 'دبي · تنسيق المرضى'],
                'address' => ['en' => "Sheikh Zayed Rd, Trade Centre\n000 Dubai, United Arab Emirates", 'ar' => "شارع الشيخ زايد، المركز التجاري\n000 دبي، الإمارات العربية المتحدة"],
                'hours' => ['en' => 'Sun–Thu · 09:00–18:00 (GMT+4)', 'ar' => 'الأحد–الخميس · 09:00–18:00 (GMT+4)'],
                'phone' => '+971 4 000 0000',
                'directions_url' => 'https://maps.google.com/',
            ],
        ];

        foreach ($offices as $office) {
            Office::create($office + ['is_published' => true]);
        }
    }

    private function seedSettings(): void
    {
        $defaults = [
            'contact.hero_eyebrow' => [
                'en' => "We're here to help",
                'fr' => 'Nous sommes là pour vous',
                'es' => 'Estamos aquí para ayudar',
                'ar' => 'نحن هنا لمساعدتك',
            ],
            'contact.hero_title' => [
                'en' => 'Talk to a coordinator today',
                'fr' => "Parlez à un coordinateur dès aujourd'hui",
                'es' => 'Habla con un coordinador hoy',
                'ar' => 'تحدث مع منسق اليوم',
            ],
            'contact.hero_text' => [
                'en' => 'Reach us however suits you — most messages get a reply within a couple of hours, in your language.',
                'fr' => 'Contactez-nous comme vous le souhaitez — la plupart des messages reçoivent une réponse en quelques heures, dans votre langue.',
                'es' => 'Contáctanos como prefieras — la mayoría de los mensajes reciben respuesta en un par de horas, en tu idioma.',
                'ar' => 'تواصل معنا بالطريقة التي تناسبك — معظم الرسائل تتلقى رداً خلال ساعات قليلة وبلغتك.',
            ],
            'contact.method_whatsapp_desc' => [
                'en' => 'Fastest — chat with us now',
                'fr' => 'Le plus rapide — discutez avec nous',
                'es' => 'Lo más rápido — chatea ahora',
                'ar' => 'الأسرع — تحدث معنا الآن',
            ],
            'contact.method_phone_desc' => [
                'en' => 'Mon–Sat, 09:00–19:00 (GMT+3)',
                'fr' => 'Lun–Sam, 09:00–19:00 (GMT+3)',
                'es' => 'Lun–Sáb, 09:00–19:00 (GMT+3)',
                'ar' => 'الإثنين–السبت، 09:00–19:00 (GMT+3)',
            ],
            'contact.method_email_desc' => [
                'en' => 'We reply within 24 hours',
                'fr' => 'Nous répondons sous 24 heures',
                'es' => 'Respondemos en 24 horas',
                'ar' => 'نرد خلال 24 ساعة',
            ],
            'contact.hours' => [
                'en' => "Mon – Fri | 09:00 – 19:00\nSaturday | 10:00 – 16:00\nSunday | Closed\nWhatsApp answered 7 days a week",
                'ar' => "الإثنين – الجمعة | 09:00 – 19:00\nالسبت | 10:00 – 16:00\nالأحد | مغلق\nنرد على واتساب طوال أيام الأسبوع",
            ],
            'contact.form_embed' => null,
            'contact.map_embed' => null,
        ];

        foreach ($defaults as $key => $value) {
            if (Setting::query()->where('key', $key)->doesntExist()) {
                Setting::set($key, $value);
            }
        }
    }
}
