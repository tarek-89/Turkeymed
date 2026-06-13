import './bootstrap';

/* ============================================================
   TurkeyMed — Aurora interactions
   Vanilla JS, progressive enhancement, keyboard + SR friendly.
   ============================================================ */
function initSite() {
    /* ---- Mobile nav drawer ---- */
    const toggle = document.querySelector('[data-nav-toggle]');
    const drawer = document.querySelector('[data-drawer]');

    if (toggle && drawer) {
        const openDrawer = () => {
            drawer.classList.remove('hidden');
            toggle.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
            const firstLink = drawer.querySelector('[data-drawer-panel] a, [data-drawer-close]');
            firstLink?.focus();
        };

        const closeDrawer = () => {
            drawer.classList.add('hidden');
            toggle.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
            toggle.focus();
        };

        toggle.addEventListener('click', openDrawer);

        drawer.addEventListener('click', (e) => {
            if (e.target.closest('[data-drawer-close]')) {
                closeDrawer();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !drawer.classList.contains('hidden')) {
                closeDrawer();
            }
        });
    }

    /* ---- Language dropdown ---- */
    document.querySelectorAll('[data-lang]').forEach((lang) => {
        const btn = lang.querySelector('[data-lang-button]');
        const menu = lang.querySelector('[data-lang-menu]');
        if (!btn || !menu) return;

        const close = () => {
            menu.classList.add('hidden');
            btn.setAttribute('aria-expanded', 'false');
        };

        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = !menu.classList.contains('hidden');
            menu.classList.toggle('hidden');
            btn.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
        });

        document.addEventListener('click', (e) => {
            if (!lang.contains(e.target)) close();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !menu.classList.contains('hidden')) {
                close();
                btn.focus();
            }
        });
    });

    /* ---- Sticky header shadow on scroll ---- */
    const headerNav = document.querySelector('[data-site-header] nav');
    if (headerNav) {
        const onScroll = () => {
            headerNav.classList.toggle('shadow-lg', window.scrollY > 8);
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    /* ---- Before/after comparison sliders ---- */
    document.querySelectorAll('[data-ba]').forEach((ba) => {
        const range = ba.querySelector('[data-ba-range]');
        if (!range) return;

        const update = () => {
            ba.style.setProperty('--ba-pos', `${range.value}%`);
        };

        range.addEventListener('input', update);
        update();
    });

    /* ---- Scroll-snap carousels (patient results, etc.) ----
       Index-based navigation that loops around. Buttons may be repeated
       inside every slide, so all of them are wired up. Works in RTL:
       offsetLeft deltas are negative there, matching RTL scrollLeft. */
    document.querySelectorAll('[data-carousel]').forEach((carousel) => {
        const track = carousel.querySelector('[data-carousel-track]');
        if (!track) return;

        const slides = Array.from(track.children);
        if (slides.length < 2) return;

        let index = 0;

        const goTo = (i) => {
            index = (i + slides.length) % slides.length; // wraps: last → first, first → last
            track.scrollTo({
                left: slides[index].offsetLeft - slides[0].offsetLeft,
                behavior: 'smooth',
            });
        };

        // Keep the index in sync when the user swipes/scrolls manually.
        let scrollTimer;
        track.addEventListener(
            'scroll',
            () => {
                clearTimeout(scrollTimer);
                scrollTimer = setTimeout(() => {
                    const pos = Math.abs(track.scrollLeft);
                    let nearest = 0;
                    slides.forEach((slide, i) => {
                        const slidePos = Math.abs(slide.offsetLeft - slides[0].offsetLeft);
                        if (Math.abs(slidePos - pos) < Math.abs(Math.abs(slides[nearest].offsetLeft - slides[0].offsetLeft) - pos)) {
                            nearest = i;
                        }
                    });
                    index = nearest;
                }, 80);
            },
            { passive: true },
        );

        carousel.querySelectorAll('[data-carousel-prev]').forEach((button) => {
            button.addEventListener('click', () => goTo(index - 1));
        });

        carousel.querySelectorAll('[data-carousel-next]').forEach((button) => {
            button.addEventListener('click', () => goTo(index + 1));
        });
    });

    /* ---- Newsletter stub: prevent submit until a route is wired ---- */
    document.querySelectorAll('form[data-newsletter]').forEach((form) => {
        form.addEventListener('submit', (e) => e.preventDefault());
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSite);
} else {
    initSite();
}
