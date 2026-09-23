/* ============================================================
   Graft calculator (x-cost.calculator)
   The page is fully server-rendered; this script only reacts to taps.
   All numbers and labels come from the JSON config in the component.
   ============================================================ */

const reducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function formatNumber(value, locale) {
    return new Intl.NumberFormat(locale === 'ar' ? 'ar-u-nu-latn' : locale).format(value);
}

function formatMoney(value, locale, currency) {
    return new Intl.NumberFormat(locale === 'ar' ? 'ar-u-nu-latn' : locale, {
        style: 'currency',
        currency,
        maximumFractionDigits: 0,
    }).format(value);
}

/** Replace :placeholders in a Laravel-style translation string. */
function tpl(text, replacements) {
    return Object.entries(replacements).reduce(
        (out, [key, value]) => out.replaceAll(`:${key}`, value).replaceAll(`{${key}}`, value),
        text || '',
    );
}

function countUp(el, to) {
    const from = Number(el.dataset.value || 0);
    el.dataset.value = to;

    if (reducedMotion() || from === to) {
        el.textContent = formatNumber(to, el.dataset.locale);
        return;
    }

    const start = performance.now();
    const step = (now) => {
        const p = Math.min(1, (now - start) / 450);
        const eased = 1 - Math.pow(1 - p, 3);
        el.textContent = formatNumber(Math.round(from + (to - from) * eased), el.dataset.locale);
        if (p < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
}

export function initGraftCalculator(root = document) {
    const calc = root.querySelector('[data-graft-calculator]');
    if (!calc) return;

    let config;
    try {
        config = JSON.parse(calc.querySelector('[data-calc-config]').textContent);
    } catch {
        return;
    }

    const { zones, strings, locale, currency } = config;
    const zoneByNumber = new Map(zones.map((z) => [z.number, z]));

    const q = (selector, scope = calc) => [...scope.querySelectorAll(selector)];
    const zoneButtons = q('.calc-zone-btn');
    const zoneShapes = q('[data-calc-zones] .calc-zone');
    const zoneNums = q('[data-zone-num]');
    const presets = q('[data-preset]');

    const out = {
        grafts: calc.querySelector('[data-calc-grafts]'),
        range: calc.querySelector('[data-calc-range]'),
        meter: calc.querySelector('[data-calc-meter]'),
        hairs: calc.querySelector('[data-calc-hairs]'),
        sessions: calc.querySelector('[data-calc-sessions]'),
        time: calc.querySelector('[data-calc-time]'),
        price: calc.querySelector('[data-calc-price]'),
        note: calc.querySelector('[data-calc-note]'),
        whatsapp: calc.querySelector('[data-calc-whatsapp]'),
        result: calc.querySelector('.calc-result'),
    };
    out.grafts.dataset.locale = locale;

    const bar = root.querySelector('[data-calc-bar]');
    const barGrafts = bar?.querySelector('[data-calc-bar-grafts]');
    const barSub = bar?.querySelector('[data-calc-bar-sub]');

    // Initial selection = what the server rendered as pressed.
    const selected = new Set(
        zoneButtons.filter((b) => b.getAttribute('aria-pressed') === 'true').map((b) => Number(b.dataset.zone)),
    );

    const durationFor = (grafts) => {
        for (const rule of config.durationRules) {
            if (rule.max_grafts === null || grafts < rule.max_grafts) return rule.label;
        }
        return '—';
    };

    const zoneCountLabel = (n) => tpl(n === 1 ? strings.zone : strings.zones, { count: formatNumber(n, locale) });

    function render() {
        let min = 0;
        let max = 0;
        selected.forEach((n) => {
            const z = zoneByNumber.get(n);
            if (z) {
                min += z.min;
                max += z.max;
            }
        });

        const mid = Math.round((min + max) / 2 / 50) * 50;
        const any = selected.size > 0;
        const two = mid > config.sessionCap;
        const priceFrom = two ? config.twoSessionPriceFrom : config.priceFrom;

        zoneButtons.forEach((b) => b.setAttribute('aria-pressed', String(selected.has(Number(b.dataset.zone)))));
        zoneShapes.forEach((s) => s.classList.toggle('is-on', selected.has(Number(s.dataset.zone))));
        zoneNums.forEach((g) => g.classList.toggle('is-on', selected.has(Number(g.dataset.zoneNum))));
        presets.forEach((c) => {
            const set = c.dataset.preset.split(',').map(Number);
            c.setAttribute('aria-pressed', String(set.length === selected.size && set.every((n) => selected.has(n))));
        });

        countUp(out.grafts, mid);
        out.range.textContent = any
            ? tpl(strings.range, { min: formatNumber(min, locale), max: formatNumber(max, locale), zones: zoneCountLabel(selected.size) })
            : strings.selectZone;
        out.meter.style.width = `${Math.min(100, (mid / config.meterMax) * 100)}%`;
        out.hairs.textContent = any
            ? tpl(strings.hairsApprox, { count: formatNumber(Math.round((mid * config.hairsPerGraft) / 50) * 50, locale) })
            : '0';
        out.sessions.textContent = !any ? '—' : two ? strings.twoSessions : strings.oneSession;
        out.time.textContent = any ? durationFor(mid) : '—';
        out.price.textContent = priceFrom ? tpl(strings.from, { price: formatMoney(priceFrom, locale, currency) }) : '—';

        if (out.note) {
            const note = two ? strings.noteTwoSessions || strings.noteSingle : strings.noteSingle;
            out.note.textContent = note || '';
            out.note.hidden = !note;
        }

        if (out.whatsapp) {
            const names = zones.filter((z) => selected.has(z.number)).map((z) => z.name).join(', ');
            const message = tpl(config.whatsappMessage, { grafts: formatNumber(mid, locale), zones: names });
            out.whatsapp.href = `https://wa.me/${config.whatsapp}?text=${encodeURIComponent(message)}`;
        }

        if (bar) {
            barGrafts.textContent = tpl(strings.mobileGrafts, { count: formatNumber(mid, locale) });
            barSub.textContent = zoneCountLabel(selected.size) + (priceFrom ? ` · ${tpl(strings.from, { price: formatMoney(priceFrom, locale, currency) })}` : '');
            updateBar();
        }
    }

    function updateBar() {
        if (!bar) return;
        const r = out.result.getBoundingClientRect();
        const away = r.top > window.innerHeight || r.bottom < 0;
        const show = selected.size > 0 && away;
        bar.classList.toggle('is-shown', show);
        bar.setAttribute('aria-hidden', String(!show));
        bar.toggleAttribute('inert', !show);
    }

    const toggle = (n) => {
        selected.has(n) ? selected.delete(n) : selected.add(n);
        render();
    };

    const hover = (n, on) => {
        zoneShapes.filter((s) => Number(s.dataset.zone) === n).forEach((s) => s.classList.toggle('is-hover', on));
        zoneButtons.filter((b) => Number(b.dataset.zone) === n).forEach((b) => b.classList.toggle('is-hover', on));
    };

    zoneButtons.forEach((b) => {
        const n = Number(b.dataset.zone);
        b.addEventListener('click', () => toggle(n));
        b.addEventListener('mouseenter', () => hover(n, true));
        b.addEventListener('mouseleave', () => hover(n, false));
    });

    zoneShapes.forEach((s) => {
        const n = Number(s.dataset.zone);
        s.addEventListener('click', () => toggle(n));
        s.addEventListener('mouseenter', () => hover(n, true));
        s.addEventListener('mouseleave', () => hover(n, false));
    });

    presets.forEach((c) =>
        c.addEventListener('click', () => {
            selected.clear();
            c.dataset.preset.split(',').map(Number).forEach((n) => selected.add(n));
            render();
        }),
    );

    calc.querySelector('[data-calc-reset]')?.addEventListener('click', () => {
        selected.clear();
        render();
    });

    window.addEventListener('scroll', updateBar, { passive: true });
    window.addEventListener('resize', updateBar, { passive: true });

    // Sync the derived text (WhatsApp link, bar) with the server-rendered state.
    render();
}
