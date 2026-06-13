<?php

namespace Database\Seeders;

use App\Models\Promise;
use App\Models\Setting;
use App\Models\Stat;
use Illuminate\Database\Seeder;

/**
 * Default About-page content (from the Aurora design), in all four locales.
 * Promises/stats seed only when their tables are empty; settings only when
 * the key is missing — admin edits are never overwritten.
 */
class AboutContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPromises();
        $this->seedStats();
        $this->seedSettings();
    }

    private function seedPromises(): void
    {
        if (Promise::query()->exists()) {
            return;
        }

        $promises = [
            [
                'icon' => 'shield',
                'sort_order' => 1,
                'title' => [
                    'en' => 'Safety first',
                    'fr' => 'La sécurité avant tout',
                    'es' => 'La seguridad primero',
                    'ar' => 'السلامة أولاً',
                ],
                'text' => [
                    'en' => 'Only JCI/ISO-accredited hospitals and surgeons with verified credentials.',
                    'fr' => 'Uniquement des hôpitaux accrédités JCI/ISO et des chirurgiens aux références vérifiées.',
                    'es' => 'Solo hospitales acreditados por JCI/ISO y cirujanos con credenciales verificadas.',
                    'ar' => 'مستشفيات معتمدة من JCI/ISO فقط وجرّاحون ذوو اعتمادات موثقة.',
                ],
            ],
            [
                'icon' => 'clock',
                'sort_order' => 2,
                'title' => [
                    'en' => 'Total transparency',
                    'fr' => 'Transparence totale',
                    'es' => 'Transparencia total',
                    'ar' => 'شفافية كاملة',
                ],
                'text' => [
                    'en' => 'All-inclusive prices up front. No hidden fees, no surprises on arrival.',
                    'fr' => 'Des prix tout compris annoncés dès le départ. Pas de frais cachés, pas de surprises à l\'arrivée.',
                    'es' => 'Precios todo incluido por adelantado. Sin tarifas ocultas ni sorpresas al llegar.',
                    'ar' => 'أسعار شاملة معلنة مسبقاً. لا رسوم خفية ولا مفاجآت عند الوصول.',
                ],
            ],
            [
                'icon' => 'chat',
                'sort_order' => 3,
                'title' => [
                    'en' => 'Your language',
                    'fr' => 'Votre langue',
                    'es' => 'Tu idioma',
                    'ar' => 'بلغتك',
                ],
                'text' => [
                    'en' => 'One coordinator, fluent in your language, from first message to recovery.',
                    'fr' => 'Un seul coordinateur, parlant votre langue, du premier message jusqu\'au rétablissement.',
                    'es' => 'Un coordinador, fluido en tu idioma, desde el primer mensaje hasta la recuperación.',
                    'ar' => 'منسق واحد يتحدث لغتك بطلاقة، من أول رسالة وحتى التعافي.',
                ],
            ],
        ];

        foreach ($promises as $promise) {
            Promise::create($promise + ['is_published' => true]);
        }
    }

    private function seedStats(): void
    {
        if (Stat::query()->exists()) {
            return;
        }

        $stats = [
            ['value' => '15k+', 'sort_order' => 1, 'label' => ['en' => 'Patients treated', 'fr' => 'Patients traités', 'es' => 'Pacientes tratados', 'ar' => 'مريض عولج']],
            ['value' => '10', 'sort_order' => 2, 'label' => ['en' => 'Years in service', 'fr' => 'Années de service', 'es' => 'Años de servicio', 'ar' => 'سنوات من الخدمة']],
            ['value' => '12', 'sort_order' => 3, 'label' => ['en' => 'Partner hospitals', 'fr' => 'Hôpitaux partenaires', 'es' => 'Hospitales asociados', 'ar' => 'مستشفى شريك']],
            ['value' => '40+', 'sort_order' => 4, 'label' => ['en' => 'Countries served', 'fr' => 'Pays desservis', 'es' => 'Países atendidos', 'ar' => 'دولة نخدمها']],
        ];

        foreach ($stats as $stat) {
            Stat::create($stat + ['is_published' => true]);
        }
    }

    private function seedSettings(): void
    {
        $defaults = [
            'about.heading' => [
                'en' => 'Care you can trust, from your first message',
                'fr' => 'Des soins de confiance, dès votre premier message',
                'es' => 'Atención de confianza, desde tu primer mensaje',
                'ar' => 'رعاية تثق بها، من أول رسالة',
            ],
            'about.text' => [
                'en' => "We're an Istanbul-based team of medical coordinators, multilingual patient advisors and partner surgeons — built to make world-class treatment in Turkey simple, safe and transparent for international patients.",
                'fr' => "Nous sommes une équipe basée à Istanbul, composée de coordinateurs médicaux, de conseillers patients multilingues et de chirurgiens partenaires — pour rendre les soins de classe mondiale en Turquie simples, sûrs et transparents pour les patients internationaux.",
                'es' => 'Somos un equipo con sede en Estambul de coordinadores médicos, asesores de pacientes multilingües y cirujanos asociados — creado para hacer que el tratamiento de clase mundial en Turquía sea simple, seguro y transparente para pacientes internacionales.',
                'ar' => 'نحن فريق مقره إسطنبول من المنسقين الطبيين ومستشاري المرضى متعددي اللغات والجرّاحين الشركاء — هدفنا جعل العلاج العالمي في تركيا بسيطاً وآمناً وشفافاً للمرضى الدوليين.',
            ],
            'about.images' => [],
            'about.story_title' => [
                'en' => 'A decade of bringing patients to Istanbul',
                'fr' => "Une décennie à accueillir des patients à Istanbul",
                'es' => 'Una década trayendo pacientes a Estambul',
                'ar' => 'عقد من استقبال المرضى في إسطنبول',
            ],
            'about.story_text' => [
                'en' => "TurkeyMed started with a simple frustration: medical tourism was full of brokers and unclear pricing, and patients were left to navigate a foreign healthcare system alone. We set out to fix that — pairing every patient with one accountable coordinator who handles everything end to end.\n\nToday we work only with accredited hospitals and vetted surgeons, publish transparent all-inclusive pricing, and support patients in 15 languages — before, during and long after their procedure.",
                'fr' => "TurkeyMed est né d'une frustration simple : le tourisme médical était plein d'intermédiaires et de prix opaques, et les patients devaient naviguer seuls dans un système de santé étranger. Nous avons voulu changer cela — en associant chaque patient à un coordinateur responsable qui gère tout de bout en bout.\n\nAujourd'hui, nous travaillons uniquement avec des hôpitaux accrédités et des chirurgiens vérifiés, publions des prix tout compris transparents et accompagnons les patients en 15 langues — avant, pendant et longtemps après leur intervention.",
                'es' => "TurkeyMed nació de una frustración simple: el turismo médico estaba lleno de intermediarios y precios poco claros, y los pacientes debían navegar solos por un sistema de salud extranjero. Nos propusimos arreglarlo — asignando a cada paciente un coordinador responsable que gestiona todo de principio a fin.\n\nHoy trabajamos solo con hospitales acreditados y cirujanos verificados, publicamos precios todo incluido transparentes y apoyamos a los pacientes en 15 idiomas — antes, durante y mucho después de su procedimiento.",
                'ar' => "بدأت TurkeyMed من إحباط بسيط: كانت السياحة العلاجية مليئة بالوسطاء والأسعار الغامضة، وكان المرضى يواجهون نظاماً صحياً أجنبياً وحدهم. قررنا إصلاح ذلك — بإسناد كل مريض إلى منسق واحد مسؤول يتولى كل شيء من البداية إلى النهاية.\n\nاليوم نعمل فقط مع مستشفيات معتمدة وجرّاحين موثوقين، وننشر أسعاراً شاملة وشفافة، وندعم المرضى بـ15 لغة — قبل الإجراء وأثناءه وبعده بوقت طويل.",
            ],
        ];

        foreach ($defaults as $key => $value) {
            if (Setting::query()->where('key', $key)->doesntExist()) {
                Setting::set($key, $value);
            }
        }
    }
}
