/* ============================================================
   Recovery timeline (x-cost.timeline)
   Draws the growth curve from the stages in the JSON config and keeps the
   chart milestones and the stage cards in sync. The cards are already in
   the HTML; without this script the chart area is simply empty.
   ============================================================ */

const reducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/** Catmull-Rom style smoothing through the points. */
function smoothPath(points) {
    let d = `M${points[0][0]} ${points[0][1]}`;
    const t = 0.18;
    for (let i = 0; i < points.length - 1; i++) {
        const p0 = points[i - 1] || points[i];
        const p1 = points[i];
        const p2 = points[i + 1];
        const p3 = points[i + 2] || p2;
        d += `C${p1[0] + (p2[0] - p0[0]) * t} ${p1[1] + (p2[1] - p0[1]) * t} ${p2[0] - (p3[0] - p1[0]) * t} ${p2[1] - (p3[1] - p1[1]) * t} ${p2[0]} ${p2[1]}`;
    }
    return d;
}

const esc = (text) => String(text ?? '').replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[c]);

export function initRecoveryTimeline(root = document) {
    const block = root.querySelector('[data-recovery-timeline]');
    if (!block) return;

    let config;
    try {
        config = JSON.parse(block.querySelector('[data-tl-config]').textContent);
    } catch {
        return;
    }

    const svg = block.querySelector('[data-tl-svg]');
    const stages = [...block.querySelectorAll('[data-tl-stage]')];
    if (!svg || !stages.length || !config.stages.length) return;

    const MONTHS = config.months || 12;
    let active = 0;
    let drawn = false;

    // Curve = origin + stages + extra points, sorted by month.
    const curve = [[0, 0], ...config.stages.map((s) => [s.month, s.percent]), ...config.curvePoints.map((p) => [p.month, p.percent])]
        .sort((a, b) => a[0] - b[0]);

    function draw() {
        const W = svg.parentNode.clientWidth - (window.innerWidth < 900 ? 24 : 56);
        if (W <= 0) return;
        const mobile = W < 640;
        const H = mobile ? 210 : 280;
        const pl = mobile ? 30 : 44;
        const pr = mobile ? 22 : 40;
        const pt = 38;
        const pb = 58;
        const X = (m) => pl + (m / MONTHS) * (W - pl - pr);
        const Y = (p) => H - pb - (p / 100) * (H - pt - pb);

        svg.setAttribute('viewBox', `0 0 ${W} ${H}`);

        const pts = curve.map(([m, p]) => [X(m), Y(p)]);
        const d = smoothPath(pts);

        let out = `<defs>
            <linearGradient id="tlStroke" x1="0" x2="1"><stop offset="0" style="stop-color: var(--color-navy-700)"/><stop offset="1" style="stop-color: var(--color-cyan-400)"/></linearGradient>
            <linearGradient id="tlArea" x1="0" y1="0" x2="0" y2="1"><stop offset="0" style="stop-color: var(--color-cyan-400); stop-opacity: .3"/><stop offset="1" style="stop-color: var(--color-cyan-400); stop-opacity: 0"/></linearGradient>
            <clipPath id="tlClip"><rect data-tl-clip x="0" y="0" width="${drawn ? W : 0}" height="${H}"/></clipPath>
        </defs>`;

        [0, 50, 100].forEach((p) => {
            out += `<line class="tl-grid" x1="${pl}" x2="${W - pr}" y1="${Y(p)}" y2="${Y(p)}"/><text class="tl-ax" x="${pl - 6}" y="${Y(p) + 3}" text-anchor="end">${p}%</text>`;
        });

        config.phases.forEach((b) => {
            const x = X(b.from) + 1.5;
            const w = X(b.to) - X(b.from) - 3;
            const y = H - pb + 10;
            const label = mobile ? b.short : b.name;
            out += `<g class="tl-band tl-band-${esc(b.tone)}"><rect x="${x}" y="${y}" width="${w}" height="20" rx="6"/>${
                w > (mobile ? 50 : 120) ? `<text class="tl-band-t" x="${x + w / 2}" y="${y + 14}" text-anchor="middle" ${mobile ? 'style="font-size:8.5px"' : ''}>${esc(label)}</text>` : ''
            }</g>`;
        });

        const ticks = mobile ? [0, 3, 6, 9, 12].filter((m) => m <= MONTHS) : Array.from({ length: MONTHS + 1 }, (_, i) => i);
        ticks.forEach((m) => {
            out += `<text class="tl-ax" x="${X(m)}" y="${H - 10}" text-anchor="middle">${m === 0 ? esc(config.strings.day0) : m + esc(config.strings.monthShort)}</text>`;
        });

        out += `<g clip-path="url(#tlClip)"><path d="${d}L${X(MONTHS)} ${Y(0)}L${X(0)} ${Y(0)}Z" fill="url(#tlArea)"/><path class="tl-curve" d="${d}"/></g>`;

        config.stages.forEach((s, i) => {
            const x = X(s.month);
            const y = Y(s.percent);
            const label = mobile ? s.short : `${s.when} · ${s.percent === 100 ? '' : '~'}${s.percent}%`;
            const tw = mobile ? 34 : label.length * 6.4 + 18;
            const tx = Math.min(Math.max(x, pl + tw / 2 - 8), W - tw / 2 - 4);
            const ty = y - 30;
            out += `<g class="tl-node ${i === active ? 'is-on' : ''}" data-tl-node="${i}" tabindex="0" role="button" aria-label="${esc(s.when)}: ${esc(s.title)}">
                <line class="tl-guide" x1="${x}" x2="${x}" y1="${y + 10}" y2="${H - pb + 8}"/>
                <circle class="tl-h" cx="${x}" cy="${y}" r="17"/>
                <circle class="tl-o" cx="${x}" cy="${y}" r="${i === active ? 9 : 7}"/>
                <g class="tl-tag"><rect x="${tx - tw / 2}" y="${ty - 15}" width="${tw}" height="22" rx="11"/><text x="${tx}" y="${ty}">${esc(label)}</text></g>
                <rect x="${x - 22}" y="${y - 44}" width="44" height="66" fill="transparent"/>
            </g>`;
        });

        svg.innerHTML = out;

        svg.querySelectorAll('[data-tl-node]').forEach((node) => {
            const i = Number(node.dataset.tlNode);
            node.addEventListener('click', () => select(i));
            node.addEventListener('mouseenter', () => select(i));
            node.addEventListener('focus', () => select(i));
            node.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    select(i);
                }
            });
        });
    }

    function select(i) {
        active = i;
        svg.querySelectorAll('[data-tl-node]').forEach((node) => {
            const on = Number(node.dataset.tlNode) === i;
            node.classList.toggle('is-on', on);
            node.querySelector('.tl-o')?.setAttribute('r', on ? 9 : 7);
        });
        stages.forEach((stage) => {
            const on = Number(stage.dataset.tlStage) === i;
            stage.classList.toggle('is-on', on);
            stage.querySelector('button')?.setAttribute('aria-pressed', String(on));
        });
    }

    stages.forEach((stage) => {
        const i = Number(stage.dataset.tlStage);
        const button = stage.querySelector('button');
        button?.addEventListener('click', () => select(i));
        button?.addEventListener('focus', () => select(i));
        stage.addEventListener('mouseenter', () => {
            if (window.innerWidth >= 900) select(i);
        });
    });

    draw();
    select(0);

    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            draw();
            select(active);
        }, 120);
    });

    // Draw-in animation once the chart is in view (skipped with reduced motion).
    if ('IntersectionObserver' in window) {
        new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                drawn = true;
                const clip = svg.querySelector('[data-tl-clip]');
                const width = svg.viewBox.baseVal.width;
                if (clip) {
                    if (!reducedMotion()) clip.style.transition = 'width 1.6s cubic-bezier(.4,0,.2,1)';
                    clip.style.width = `${width}px`;
                }
                observer.disconnect();
            });
        }, { threshold: 0.35 }).observe(svg);

        // Phones: light the rail and select the stage as it scrolls into the middle.
        const stageObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-seen');
                if (window.innerWidth < 900) select(Number(entry.target.dataset.tlStage));
            });
        }, { rootMargin: '-45% 0px -45% 0px' });
        stages.forEach((stage) => stageObserver.observe(stage));
    } else {
        drawn = true;
        draw();
    }
}
