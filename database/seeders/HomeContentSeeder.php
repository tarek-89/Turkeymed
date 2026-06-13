<?php

namespace Database\Seeders;

use App\Models\ProcessStep;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\TreatmentCard;
use Illuminate\Database\Seeder;

/**
 * Default homepage content (from the Aurora design). Component tables seed
 * only when empty; settings only when the key is missing — admin edits are
 * never overwritten.
 */
class HomeContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSettings();
        $this->seedTreatments();
        $this->seedTestimonials();
        $this->seedSteps();
    }

    private function seedSettings(): void
    {
        $defaults = [
            'home.hero_badge' => ['en' => 'Istanbul · 15,000+ patients treated', 'ar' => 'إسطنبول · أكثر من 15,000 مريض'],
            'home.hero_title' => ['en' => 'World-class care,', 'fr' => 'Des soins de classe mondiale,', 'es' => 'Atención de clase mundial,', 'ar' => 'رعاية عالمية المستوى،'],
            'home.hero_title_accent' => ['en' => 'handled for you.', 'fr' => 'gérés pour vous.', 'es' => 'gestionada por ti.', 'ar' => 'ننجزها من أجلك.'],
            'home.hero_lead' => [
                'en' => 'Accredited surgeons, transparent pricing and a coordinator who speaks your language — start to finish.',
                'ar' => 'جرّاحون معتمدون وأسعار شفافة ومنسق يتحدث لغتك — من البداية إلى النهاية.',
            ],
            'home.hero_images' => [],
            'home.hero_stat_value' => '98%',
            'home.hero_stat_label' => ['en' => 'graft survival', 'fr' => 'survie des greffons', 'es' => 'supervivencia del injerto', 'ar' => 'نسبة بقاء الطعوم'],
            'home.cta_title' => ['en' => 'Start with a free consultation', 'ar' => 'ابدأ باستشارة مجانية'],
            'home.cta_text' => [
                'en' => 'No obligation. A medical coordinator replies within 24 hours — in your language.',
                'ar' => 'بدون أي التزام. يرد منسق طبي خلال 24 ساعة — وبلغتك.',
            ],
        ];

        foreach ($defaults as $key => $value) {
            if (Setting::query()->where('key', $key)->doesntExist()) {
                Setting::set($key, $value);
            }
        }
    }

    private function seedTreatments(): void
    {
        if (TreatmentCard::query()->exists()) {
            return;
        }

        TreatmentCard::create([
            'variant' => 'feature', 'icon' => 'star', 'sort_order' => 1, 'url' => '/category/hair-transplant-surgery',
            'title' => ['en' => 'Hair Transplant', 'ar' => 'زراعة الشعر'],
            'description' => ['en' => 'FUE & DHI techniques for natural, permanent results.', 'ar' => 'تقنيات FUE وDHI لنتائج طبيعية دائمة.'],
            'badge' => ['en' => 'Most popular', 'ar' => 'الأكثر طلباً'],
            'footnote' => ['en' => 'From €1,500', 'ar' => 'ابتداءً من €1,500'],
        ]);
        TreatmentCard::create([
            'variant' => 'default', 'icon' => 'heart', 'sort_order' => 2, 'url' => '/category/dental-clinic',
            'title' => ['en' => 'Dental', 'ar' => 'الأسنان'],
            'description' => ['en' => 'Implants, veneers, Hollywood Smile', 'ar' => 'زراعة، فينير، ابتسامة هوليوود'],
        ]);
        TreatmentCard::create([
            'variant' => 'default', 'icon' => 'check', 'sort_order' => 3, 'url' => '/category/eye-surgery',
            'title' => ['en' => 'Eye Surgery', 'ar' => 'جراحة العيون'],
            'description' => ['en' => 'LASIK, lens, eye colour change', 'ar' => 'الليزك، العدسات، تغيير لون العين'],
        ]);
        TreatmentCard::create([
            'variant' => 'default', 'icon' => 'shield', 'sort_order' => 4, 'url' => '/category/services',
            'title' => ['en' => 'Packages', 'ar' => 'الباقات'],
            'description' => ['en' => 'Tailored multi-treatment plans', 'ar' => 'خطط علاجية متعددة مخصصة'],
        ]);
        TreatmentCard::create([
            'variant' => 'cta', 'sort_order' => 5, 'url' => null,
            'title' => ['en' => 'Not sure?', 'ar' => 'غير متأكد؟'],
            'description' => ['en' => "Tell us your goals — we'll recommend a plan.", 'ar' => 'أخبرنا بأهدافك وسنوصي بخطة.'],
            'footnote' => ['en' => 'Free consultation', 'ar' => 'استشارة مجانية'],
        ]);
    }

    private function seedTestimonials(): void
    {
        if (Testimonial::query()->exists()) {
            return;
        }

        Testimonial::create([
            'sort_order' => 1, 'rating' => 5, 'author_name' => 'Markus W.',
            'quote' => ['en' => 'Messaged me in German at every step — pickup, hotel, clinic. Completely natural result.'],
            'author_meta' => ['en' => 'FUE · Germany'],
        ]);
        Testimonial::create([
            'sort_order' => 2, 'rating' => 5, 'author_name' => 'Sophie L.', 'is_featured' => true,
            'quote' => ['en' => 'My Hollywood Smile cost a third of the UK quote. London-trained surgeon. Five stars.'],
            'author_meta' => ['en' => 'Dental · UK'],
        ]);
        Testimonial::create([
            'sort_order' => 3, 'rating' => 5, 'author_name' => 'Ahmed K.',
            'quote' => ['en' => 'Everything arranged in Arabic, every request respected. After LASIK I can see clearly.', 'ar' => 'رُتّب كل شيء بالعربية واحتُرم كل طلب. بعد الليزك أصبحت أرى بوضوح.'],
            'author_meta' => ['en' => 'LASIK · Saudi Arabia', 'ar' => 'ليزك · السعودية'],
        ]);
    }

    private function seedSteps(): void
    {
        if (ProcessStep::query()->exists()) {
            return;
        }

        $steps = [
            [
                'sort_order' => 1,
                'title' => ['en' => 'Free consultation', 'ar' => 'استشارة مجانية'],
                'description' => ['en' => 'Send your photos and goals; your coordinator returns a tailored plan and an all-inclusive quote within 24 hours.', 'ar' => 'أرسل صورك وأهدافك؛ يعيد إليك المنسق خطة مخصصة وعرضاً شاملاً خلال 24 ساعة.'],
            ],
            [
                'sort_order' => 2,
                'title' => ['en' => 'Travel & arrival', 'ar' => 'السفر والوصول'],
                'description' => ['en' => 'We book your hotel and VIP transfers; your coordinator meets you on arrival and stays with you throughout.', 'ar' => 'نحجز فندقك وتنقلاتك VIP؛ يستقبلك المنسق عند وصولك ويبقى معك طوال الرحلة.'],
            ],
            [
                'sort_order' => 3,
                'title' => ['en' => 'Your procedure', 'ar' => 'إجراؤك'],
                'description' => ['en' => 'Treatment at an accredited hospital with your chosen surgeon, in comfortable, modern facilities.', 'ar' => 'العلاج في مستشفى معتمد مع الجرّاح الذي اخترته، في منشآت حديثة ومريحة.'],
            ],
            [
                'sort_order' => 4,
                'title' => ['en' => 'Recovery & aftercare', 'ar' => 'التعافي والمتابعة'],
                'description' => ['en' => 'Follow-up checks before you fly home, plus lifetime aftercare support in your language.', 'ar' => 'فحوصات متابعة قبل عودتك، بالإضافة إلى دعم رعاية مدى الحياة بلغتك.'],
            ],
        ];

        foreach ($steps as $step) {
            ProcessStep::create($step + ['is_published' => true]);
        }
    }
}
