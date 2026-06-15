// Self-hosted fonts (replaces the external Google Fonts request). Fontsource
// ships each face with font-display: swap, so text paints immediately.
import '@fontsource/plus-jakarta-sans/400.css';
import '@fontsource/plus-jakarta-sans/500.css';
import '@fontsource/plus-jakarta-sans/600.css';
import '@fontsource/plus-jakarta-sans/700.css';
import '@fontsource/plus-jakarta-sans/800.css';
import '@fontsource/ibm-plex-sans-arabic/400.css';
import '@fontsource/ibm-plex-sans-arabic/500.css';
import '@fontsource/ibm-plex-sans-arabic/600.css';
import '@fontsource/ibm-plex-sans-arabic/700.css';
import '@fontsource/jetbrains-mono/400.css';
import '@fontsource/jetbrains-mono/500.css';

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

    /* ---- YouTube lite-embed: swap the poster facade for the real iframe ----
       Keeps YouTube's ~1 MB player off the initial load; it's injected only
       when the visitor presses play. */
    document.querySelectorAll('[data-youtube]').forEach((wrap) => {
        const button = wrap.querySelector('[data-youtube-play]');
        if (!button) return;

        button.addEventListener('click', () => {
            const src = wrap.getAttribute('data-youtube-src');
            if (!src) return;

            const iframe = document.createElement('iframe');
            iframe.src = src;
            iframe.title = wrap.getAttribute('data-youtube-title') || 'YouTube video';
            iframe.className = 'h-full w-full';
            iframe.allow =
                'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
            iframe.referrerPolicy = 'strict-origin-when-cross-origin';
            iframe.allowFullscreen = true;

            wrap.innerHTML = '';
            wrap.appendChild(iframe);
            iframe.focus();
        });
    });

    /* ---- Instagram: load embed.js only when the feed scrolls into view ----
       The feed sits below the fold, so its ~1 MB script + iframes never load on
       initial paint. rootMargin starts the fetch just before the user arrives,
       so it feels instant without costing the initial load. */
    const instagramSection = document.querySelector('[data-instagram-embed]');
    if (instagramSection) {
        // Instagram's embed.js injects iframes without a title, which fails the
        // frame-title accessibility audit. Watch the section and label any iframe
        // it creates as soon as it appears.
        const titleInstagramFrames = () => {
            instagramSection.querySelectorAll('iframe:not([title])').forEach((frame) => {
                frame.setAttribute('title', 'Instagram post');
            });
        };
        new MutationObserver(titleInstagramFrames).observe(instagramSection, {
            childList: true,
            subtree: true,
        });

        const loadInstagram = () => {
            if (window.instgrm) {
                window.instgrm.Embeds.process();
                return;
            }
            const script = document.createElement('script');
            script.async = true;
            script.src = 'https://www.instagram.com/embed.js';
            document.body.appendChild(script);
        };

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver(
                (entries, obs) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            loadInstagram();
                            obs.disconnect();
                        }
                    });
                },
                { rootMargin: '300px' },
            );
            observer.observe(instagramSection);
        } else {
            loadInstagram();
        }
    }

    /* ---- Newsletter stub: prevent submit until a route is wired ---- */
    document.querySelectorAll('form[data-newsletter]').forEach((form) => {
        form.addEventListener('submit', (e) => e.preventDefault());
    });

    /* ---- Lightbox: click a [data-lightbox] image to view it full-size ---- */
    const lightboxImages = document.querySelectorAll('[data-lightbox]');
    if (lightboxImages.length) {
        const overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 z-[200] hidden items-center justify-center bg-black/90 p-4';
        overlay.setAttribute('aria-hidden', 'true');
        overlay.innerHTML =
            '<button type="button" aria-label="Close" class="absolute end-4 top-4 grid h-11 w-11 place-items-center rounded-full bg-white/15 text-3xl leading-none text-white transition hover:bg-white/25">&times;</button>' +
            '<img alt="" class="max-h-[90vh] max-w-[92vw] rounded-lg object-contain shadow-2xl">';
        document.body.appendChild(overlay);
        const overlayImg = overlay.querySelector('img');

        const openLightbox = (src, alt) => {
            overlayImg.src = src;
            overlayImg.alt = alt || '';
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            document.body.style.overflow = 'hidden';
        };
        const closeLightbox = () => {
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
            document.body.style.overflow = '';
            overlayImg.removeAttribute('src');
        };

        lightboxImages.forEach((el) => {
            el.classList.add('cursor-zoom-in');
            el.addEventListener('click', () => openLightbox(el.currentSrc || el.src, el.alt));
        });
        // Click anywhere except the image (or press Esc) closes.
        overlay.addEventListener('click', (e) => {
            if (e.target !== overlayImg) closeLightbox();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && overlay.classList.contains('flex')) closeLightbox();
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSite);
} else {
    initSite();
}
