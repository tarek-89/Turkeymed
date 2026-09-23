/* ============================================================
   Worldwide price comparison (x-cost.compare)
   Tabs swap precomputed figures from the JSON config; the server renders
   the default country, so nothing here is needed for the first paint.
   ============================================================ */

const escapeHtml = (text) =>
    String(text ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);

export function initCountryCompare(root = document) {
    const block = root.querySelector('[data-country-compare]');
    if (!block) return;

    let config;
    try {
        config = JSON.parse(block.querySelector('[data-cmp-config]').textContent);
    } catch {
        return;
    }

    const byId = new Map(config.countries.map((c) => [String(c.id), c]));
    const tabs = [...block.querySelectorAll('[role="tab"][data-country]')];
    const flags = block.querySelector('[data-cmp-flags]')?.content;
    const panel = block.querySelector('#cmp-panel');

    const out = {
        trBar: block.querySelector('[data-cmp-tr-bar]'),
        flag: block.querySelector('[data-cmp-flag]'),
        name: block.querySelector('[data-cmp-name]'),
        price: block.querySelector('[data-cmp-price]'),
        note: block.querySelector('[data-cmp-note]'),
        percent: block.querySelector('[data-cmp-percent]'),
        saveText: block.querySelector('[data-cmp-save-text]'),
    };

    function select(id, focus = false) {
        const c = byId.get(String(id));
        if (!c) return;

        tabs.forEach((tab) => {
            const on = tab.dataset.country === String(id);
            tab.setAttribute('aria-selected', String(on));
            tab.tabIndex = on ? 0 : -1;
            if (on && focus) tab.focus();
        });
        panel?.setAttribute('aria-labelledby', `cmp-tab-${id}`);

        out.trBar.style.width = `${c.bar}%`;
        out.name.textContent = c.name;
        out.price.textContent = c.price;
        out.note.textContent = c.note || '';
        out.percent.textContent = c.percent;
        out.saveText.innerHTML = escapeHtml(config.saveText)
            .replaceAll('{country}', `<b>${escapeHtml(c.sentenceName)}</b>`)
            .replaceAll('{amount}', `<b>${escapeHtml(c.amount)}</b>`);

        const flag = flags?.querySelector(`[data-flag-for="${id}"]`);
        if (flag) out.flag.innerHTML = flag.innerHTML;
    }

    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => select(tab.dataset.country));
        tab.addEventListener('keydown', (event) => {
            const rtl = document.documentElement.dir === 'rtl';
            const next = rtl ? 'ArrowLeft' : 'ArrowRight';
            const prev = rtl ? 'ArrowRight' : 'ArrowLeft';
            let target = null;
            if (event.key === next) target = tabs[(index + 1) % tabs.length];
            if (event.key === prev) target = tabs[(index - 1 + tabs.length) % tabs.length];
            if (event.key === 'Home') target = tabs[0];
            if (event.key === 'End') target = tabs[tabs.length - 1];
            if (target) {
                event.preventDefault();
                select(target.dataset.country, true);
            }
        });
    });
}
