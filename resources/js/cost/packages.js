/* ============================================================
   Packages (x-cost.packages)
   Technique tabs show the matching price in every card; the switch hides
   feature rows that are identical across packages. All content is already
   in the HTML.
   ============================================================ */

export function initPackages(root = document) {
    const block = root.querySelector('[data-packages]');
    if (!block) return;

    const tabs = [...block.querySelectorAll('[role="tab"][data-technique]')];
    const panel = block.querySelector('#pkg-panel');
    const prices = [...block.querySelectorAll('[data-technique-price]')];

    function select(id, focus = false) {
        tabs.forEach((tab) => {
            const on = tab.dataset.technique === String(id);
            tab.setAttribute('aria-selected', String(on));
            tab.tabIndex = on ? 0 : -1;
            if (on && focus) tab.focus();
        });
        prices.forEach((price) => {
            price.hidden = price.dataset.techniquePrice !== String(id);
        });
        panel?.setAttribute('aria-labelledby', `pkg-tab-${id}`);
    }

    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => select(tab.dataset.technique));
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
                select(target.dataset.technique, true);
            }
        });
    });

    const diff = block.querySelector('[data-pkg-diff]');
    diff?.addEventListener('click', () => {
        const on = diff.getAttribute('aria-checked') !== 'true';
        diff.setAttribute('aria-checked', String(on));
        panel?.classList.toggle('is-diff-only', on);
    });
}
