<x-layout.app
    :title="$heroTitle.' - '.config('site.brand')"
    :description="$heroText"
    :canonical="$language === \App\Support\Locale::DEFAULT ? route('contact') : route('contact.localized', $language)"
>
    @php
        $whatsapp = preg_replace('/\D/', '', (string) config('site.whatsapp'));
        $phone = (string) config('site.phone');
        $email = (string) config('site.email');
    @endphp

    {{-- Hero + contact methods --}}
    <x-ui.section :tight="true">
        <div class="max-w-2xl">
            <x-ui.eyebrow class="block">{{ $heroEyebrow }}</x-ui.eyebrow>
            <x-ui.heading level="h1" class="mt-3">{{ $heroTitle }}</x-ui.heading>
            <p class="lead measure mt-3.5">{{ $heroText }}</p>
        </div>

        <div class="mt-8 grid gap-6 md:grid-cols-3">
            <x-contact.method
                icon="whatsapp"
                :title="__('contact.method_whatsapp')"
                :description="$methodWhatsappDesc"
                :value="config('site.whatsapp')"
                :href="'https://wa.me/'.$whatsapp"
                :accent="true"
            />
            <x-contact.method
                icon="phone"
                :title="__('contact.method_call')"
                :description="$methodPhoneDesc"
                :value="$phone"
                :href="'tel:'.preg_replace('/[^0-9+]/', '', $phone)"
            />
            <x-contact.method
                icon="mail"
                :title="__('contact.method_email')"
                :description="$methodEmailDesc"
                :value="$email"
                :href="'mailto:'.$email"
            />
        </div>

        @if ($socialLinks->isNotEmpty())
            <div class="mt-8 flex flex-wrap items-center gap-4">
                <span class="text-sm font-bold text-ink">{{ __('contact.follow_us') }}</span>
                <x-ui.social-links :links="$socialLinks" />
            </div>
        @endif
    </x-ui.section>

    {{-- Message form (split card: form + portrait) and office hours --}}
    @if ($formEmbed || filled($hours))
        <x-ui.section :tight="true">
            <div @class(['grid items-start gap-8', 'lg:grid-cols-[minmax(0,1fr)_340px]' => ! $formImage])>
                @if ($formEmbed)
                    <x-contact.form-panel :embed="$formEmbed" :image="$formImage" />
                @endif

                @if (filled($hours))
                    @php
                        $hourRows = [];
                        $hourNotes = [];
                        foreach (preg_split('/\r?\n/', trim((string) $hours)) as $line) {
                            [$label, $value] = array_pad(array_map('trim', explode('|', $line, 2)), 2, null);
                            if ($value !== null) {
                                $hourRows[] = [$label, $value];
                            } elseif ($label !== '') {
                                $hourNotes[] = $label;
                            }
                        }
                    @endphp

                    @if ($formImage)
                        {{-- Full-width: one compact strip (days side by side) instead of stretched rows. --}}
                        <x-ui.card class="lg:flex lg:items-center lg:gap-8 lg:py-5">
                            <x-ui.eyebrow as="h3" class="mb-3 block lg:mb-0 lg:flex-none">{{ __('contact.method_call') }}</x-ui.eyebrow>

                            <dl class="divide-y divide-line text-sm lg:flex lg:flex-1 lg:divide-x lg:divide-y-0">
                                @foreach ($hourRows as [$label, $value])
                                    <div class="flex items-center justify-between gap-3 py-2.5 lg:flex-col lg:items-start lg:justify-center lg:gap-0.5 lg:px-6 lg:py-0 lg:first:ps-0">
                                        <dt class="text-muted">{{ $label }}</dt>
                                        <dd class="font-bold text-ink">{{ $value }}</dd>
                                    </div>
                                @endforeach
                            </dl>

                            @foreach ($hourNotes as $note)
                                <p class="flex items-center gap-2 py-2.5 text-sm text-muted lg:flex-none lg:py-0">
                                    <span class="h-2 w-2 flex-none rounded-full bg-success" aria-hidden="true"></span>
                                    {{ $note }}
                                </p>
                            @endforeach

                            <x-ui.whatsapp-button variant="accent" class="mt-4 max-lg:w-full lg:mt-0 lg:flex-none" />
                        </x-ui.card>
                    @else
                        <x-ui.card>
                            <x-ui.eyebrow as="h3" class="mb-3 block">{{ __('contact.method_call') }}</x-ui.eyebrow>
                            <dl class="divide-y divide-line text-sm">
                                @foreach ($hourRows as [$label, $value])
                                    <div class="flex items-center justify-between gap-3 py-2.5">
                                        <dt class="text-muted">{{ $label }}</dt>
                                        <dd class="font-bold text-ink">{{ $value }}</dd>
                                    </div>
                                @endforeach
                                @foreach ($hourNotes as $note)
                                    <p class="flex items-center gap-2 py-2.5 text-muted">
                                        <span class="h-2 w-2 flex-none rounded-full bg-success" aria-hidden="true"></span>
                                        {{ $note }}
                                    </p>
                                @endforeach
                            </dl>

                            <x-ui.whatsapp-button variant="accent" :block="true" class="mt-5" />
                        </x-ui.card>
                    @endif
                @endif
            </div>
        </x-ui.section>
    @endif

    {{-- Offices, grouped by country --}}
    @if ($officeGroups->isNotEmpty())
        <x-ui.section :tight="true">
            <x-ui.section-heading
                :eyebrow="__('contact.offices_eyebrow')"
                :title="__('contact.offices_title')"
                align="center"
            />

            <div @class(['grid gap-8', 'lg:grid-cols-2' => $mapEmbed])>
                <div class="space-y-10">
                    @foreach ($officeGroups as $offices)
                        <div>
                            <div class="mb-5 flex flex-wrap items-center gap-3">
                                <h3 class="text-xl font-bold text-ink">{{ $offices->first()->translate('country') }}</h3>
                                <x-ui.badge variant="outline">{{ trans_choice('contact.office_count', $offices->count(), ['count' => $offices->count()]) }}</x-ui.badge>
                            </div>

                            <div @class(['grid gap-6', 'md:grid-cols-2' => ! $mapEmbed])>
                                @foreach ($offices as $office)
                                    <x-contact.office-card :office="$office" />
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Map embed, beside the offices on desktop --}}
                @if ($mapEmbed)
                    <div class="lg:h-full">
                        <div class="contact-embed contact-embed-map h-full overflow-hidden rounded-2xl border border-line">{!! $mapEmbed !!}</div>
                    </div>
                @endif
            </div>
        </x-ui.section>
    @endif

    {{-- Map embed (only when there are no offices to sit beside) --}}
    @if ($mapEmbed && $officeGroups->isEmpty())
        <x-ui.section :tight="true">
            <div class="contact-embed overflow-hidden rounded-2xl border border-line">{!! $mapEmbed !!}</div>
        </x-ui.section>
    @endif
</x-layout.app>
