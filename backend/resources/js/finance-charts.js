// resources/js/finance-charts.js
import Chart from 'chart.js/auto';

// Palet premium: violet = Masuk, gold = Keluar, sisanya netral (ink/slate).
const cols = { income: '#7c3aed', expense: '#f59e0b' };
// Donut = satu hue sesuai tipe (Masuk/Keluar), gelap -> terang = terbesar -> terkecil.
// Kedua ramp lolos uji ordinal (satu hue, lightness monoton, ujung terang masih terlihat).
const ramps = {
    income: ['#2e1065', '#4c1d95', '#6d28d9', '#8b5cf6', '#a78bfa'],
    expense: ['#5c3300', '#8a4b00', '#b26200', '#d97706', '#f59e0b'],
};
const OTHER = '#cbd5e1';
const MAX_SLICES = 5;   // > 5 kategori: 4 teratas + "Lainnya"

const ink = { strong: '#0f172a', soft: '#475569', muted: '#94a3b8', line: '#e2e8f0' };
const rp = (v) => 'Rp ' + Number(v).toLocaleString('id-ID');
const compact = (v) => {
    const n = Math.abs(v);
    const f = (x) => x.toLocaleString('id-ID', { maximumFractionDigits: 1 });
    return n >= 1e9 ? f(v / 1e9) + ' M' : n >= 1e6 ? f(v / 1e6) + ' jt' : n >= 1e3 ? f(v / 1e3) + ' rb' : String(v);
};
const reduceMotion = () => window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

// Render minimal 2x (HD) walau layar 1x, supaya garis & teks tetap tajam.
const HD = () => Math.max(window.devicePixelRatio || 1, 2);

const tooltipStyle = {
    backgroundColor: 'rgba(15, 23, 42, 0.94)',
    titleColor: '#f8fafc',
    bodyColor: '#e2e8f0',
    titleFont: { weight: '600', size: 12 },
    bodyFont: { size: 12 },
    padding: 12,
    cornerRadius: 10,
    boxPadding: 6,
    usePointStyle: true,
    caretSize: 0,
};

const legendStyle = {
    position: 'bottom',
    labels: { boxWidth: 8, boxHeight: 8, usePointStyle: true, pointStyle: 'circle', padding: 16, color: ink.soft, font: { size: 12 } },
};

// Gradient area: warna di garis, memudar ke transparan di bawah.
const areaGradient = (hex) => (context) => {
    const { ctx, chartArea } = context.chart;
    if (!chartArea) return hex + '00';   // chart belum selesai di-layout
    const g = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
    g.addColorStop(0, hex + '55');
    g.addColorStop(0.55, hex + '14');
    g.addColorStop(1, hex + '00');
    return g;
};

// Penanda titik dibuat jarang: maks ~7 titik dengan jarak sama + titik terakhir.
const isMarker = (i, n) => {
    if (n <= 8) return true;
    const k = Math.ceil(n / 7);
    return i % k === 0 || i === n - 1;
};

/* ---------- Line: animasi "mengular" dari kiri ke kanan ---------- */

// Cari y pada kurva bezier (pakai control point Chart.js) untuk posisi x tertentu.
const yAt = (pts, x) => {
    for (let i = 0; i < pts.length - 1; i++) {
        const a = pts[i], b = pts[i + 1];
        if (x < a.x || x > b.x) continue;
        const p = [a.x, a.cp2x ?? a.x, b.cp1x ?? b.x, b.x];
        const q = [a.y, a.cp2y ?? a.y, b.cp1y ?? b.y, b.y];
        const bz = (v, t) => { const u = 1 - t; return u * u * u * v[0] + 3 * u * u * t * v[1] + 3 * u * t * t * v[2] + t * t * t * v[3]; };
        let lo = 0, hi = 1, t = 0.5;
        for (let k = 0; k < 24; k++) {          // binary search t agar bz(p, t) ≈ x
            t = (lo + hi) / 2;
            bz(p, t) < x ? (lo = t) : (hi = t);
        }
        return bz(q, t);
    }
    return null;
};

const revealX = (chart) => {
    const a = chart.chartArea;
    return a.left - 12 + (a.right - a.left + 24) * chart.$reveal;
};

const snakePlugin = {
    id: 'snakeReveal',
    beforeDatasetsDraw(chart) {
        if ((chart.$reveal ?? 1) >= 1) return;
        const { ctx, chartArea: a } = chart;
        ctx.save();
        ctx.beginPath();
        ctx.rect(0, 0, revealX(chart), a.bottom + 12);
        ctx.clip();
    },
    afterDatasetsDraw(chart) {
        if ((chart.$reveal ?? 1) >= 1) return;
        const { ctx } = chart;
        ctx.restore();

        // "kepala ular": titik bercahaya di ujung garis yang sedang tumbuh
        const x = revealX(chart);
        chart.data.datasets.forEach((ds, i) => {
            if (!chart.isDatasetVisible(i) || ds.borderDash?.length) return;
            const y = yAt(chart.getDatasetMeta(i).data, x);
            if (y == null) return;
            ctx.save();
            ctx.fillStyle = ds.borderColor + '2e';
            ctx.beginPath(); ctx.arc(x, y, 10, 0, Math.PI * 2); ctx.fill();
            ctx.fillStyle = '#fff';
            ctx.strokeStyle = ds.borderColor;
            ctx.lineWidth = 2.5;
            ctx.beginPath(); ctx.arc(x, y, 4.5, 0, Math.PI * 2); ctx.fill(); ctx.stroke();
            ctx.restore();
        });
    },
};

// Garis bantu vertikal saat hover
const crosshairPlugin = {
    id: 'crosshair',
    beforeDatasetsDraw(chart) {
        const active = chart.tooltip?.getActiveElements?.() ?? [];
        if (!active.length) return;
        const { ctx, chartArea: a } = chart;
        const x = active[0].element.x;
        ctx.save();
        ctx.strokeStyle = ink.line;
        ctx.lineWidth = 1;
        ctx.setLineDash([4, 4]);
        ctx.beginPath(); ctx.moveTo(x, a.top); ctx.lineTo(x, a.bottom); ctx.stroke();
        ctx.restore();
    },
};

const snake = (chart, duration = 3000) => {
    cancelAnimationFrame(chart.$raf);
    if (reduceMotion()) { chart.$reveal = 1; chart.draw(); return; }
    const ease = (t) => (t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2);
    const start = performance.now();
    chart.$reveal = 0;
    const step = () => {
        const t = Math.max(0, Math.min((performance.now() - start) / duration, 1));
        chart.$reveal = ease(t);
        chart.draw();
        if (t < 1) chart.$raf = requestAnimationFrame(step);
    };
    chart.$raf = requestAnimationFrame(step);
};

/* ---------- Donut: teks tengah (total / kategori yang di-hover) ---------- */

const centerPlugin = {
    id: 'donutCenter',
    afterDatasetsDraw(chart) {
        const arc = chart.getDatasetMeta(0).data[0];
        if (!arc) return;
        const { ctx } = chart;
        const values = chart.data.datasets[0].data;
        const total = values.reduce((s, v) => s + v, 0);
        const active = chart.getActiveElements()[0];

        const label = active ? chart.data.labels[active.index] : 'Total';
        const value = active ? values[active.index] : total;
        const sub = active && total ? Math.round(value / total * 100) + '%' : values.length + ' kategori';

        const r = arc.innerRadius;
        const fit = (txt, weight, size) => {      // kecilkan font kalau teks kepanjangan
            let s = size;
            do { ctx.font = `${weight} ${s}px ${Chart.defaults.font.family}`; s--; }
            while (ctx.measureText(txt).width > r * 1.6 && s > 9);
        };

        ctx.save();
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        const { x, y } = arc;
        fit(label, '500', 12); ctx.fillStyle = ink.muted; ctx.fillText(label, x, y - r * 0.34);
        fit('Rp ' + compact(value), '700', 20); ctx.fillStyle = ink.strong; ctx.fillText('Rp ' + compact(value), x, y + 1);
        fit(sub, '500', 11); ctx.fillStyle = ink.soft; ctx.fillText(sub, x, y + r * 0.34);
        ctx.restore();
    },
};

// Urutkan terbesar dulu; lebih dari 5 kategori -> 4 teratas + "Lainnya" (abu-abu)
const foldSlices = ({ type, labels, values }) => {
    const ramp = ramps[type] ?? ramps.expense;
    const rows = labels.map((l, i) => [l, values[i]]).sort((a, b) => b[1] - a[1]);
    if (rows.length > MAX_SLICES) {
        const rest = rows.splice(MAX_SLICES - 1).reduce((s, r) => s + r[1], 0);
        rows.push(['Lainnya', rest]);
    }
    const named = rows.length - (rows.at(-1)?.[0] === 'Lainnya' && labels.length > MAX_SLICES ? 1 : 0);
    // sebar langkah warna supaya 2-3 kategori pun kontrasnya tetap jelas
    const step = (i) => ramp[named <= 1 ? 2 : Math.round(i * (ramp.length - 1) / (named - 1))];
    return {
        labels: rows.map((r) => r[0]),
        values: rows.map((r) => r[1]),
        colors: rows.map((_, i) => (i < named ? step(i) : OTHER)),
    };
};

document.addEventListener('alpine:init', () => {
    Chart.defaults.font.family = getComputedStyle(document.body).fontFamily || Chart.defaults.font.family;
    Chart.defaults.color = ink.muted;

    window.Alpine.data('financeCharts', (initial) => {
        // chart disimpan di closure, bukan di state Alpine (proxy Alpine bikin Chart.js error)
        let line = null, donut = null, data = initial;

        return {
            init() { this.draw(); },
            update(c) { data = c; this.draw(); },

            draw() {
                this.drawLine();
                this.drawDonut();
            },

            drawLine() {
                const sets = [
                    this.ds('Masuk', data.income, cols.income),
                    this.ds('Keluar', data.expense, cols.expense),
                ];
                if (data.cmp) {
                    sets.push(this.ds('Masuk (pembanding)', data.cmp.income, cols.income, true));
                    sets.push(this.ds('Keluar (pembanding)', data.cmp.expense, cols.expense, true));
                }

                // Chart sudah ada: ganti data lalu ulang animasi mengular
                if (line) {
                    line.data.labels = data.labels;
                    line.data.datasets = sets;
                    line.$reveal = 0;
                    line.update('none');
                    snake(line);
                    return;
                }

                line = new Chart(this.$refs.bar, {
                    type: 'line',
                    data: { labels: data.labels, datasets: sets },
                    plugins: [crosshairPlugin, snakePlugin],
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        devicePixelRatio: HD(),
                        animation: false,                 // digantikan animasi mengular
                        layout: { padding: { top: 12, right: 14, left: 4 } },
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: {
                                ...legendStyle,
                                labels: {
                                    ...legendStyle.labels,
                                    generateLabels: (chart) =>
                                        Chart.defaults.plugins.legend.labels.generateLabels(chart)
                                            .map((l) => ({ ...l, fillStyle: l.strokeStyle, lineWidth: 0, lineDash: [], fontColor: ink.soft })),
                                },
                            },
                            tooltip: {
                                ...tooltipStyle,
                                callbacks: { label: (c) => ` ${c.dataset.label}: ${rp(c.parsed.y)}` },
                            },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grace: '8%',
                                grid: { display: false },
                                border: { display: false },
                                ticks: { color: ink.muted, padding: 10, maxTicksLimit: 5, font: { size: 11 }, callback: (v) => compact(v) },
                            },
                            x: {
                                grid: { display: false },
                                border: { display: false },
                                ticks: { color: ink.muted, padding: 8, autoSkip: true, autoSkipPadding: 24, maxTicksLimit: 7, maxRotation: 0, font: { size: 11 } },
                            },
                        },
                    },
                });
                line.$reveal = 0;
                snake(line);
            },

            ds(label, values, color, dashed = false) {
                return {
                    label,
                    data: values,
                    borderColor: dashed ? color + '88' : color,
                    backgroundColor: dashed ? 'transparent' : areaGradient(color),
                    fill: !dashed,
                    borderDash: dashed ? [5, 5] : [],
                    borderWidth: dashed ? 1.5 : 2.5,
                    borderCapStyle: 'round',
                    borderJoinStyle: 'round',
                    tension: 0.4,
                    cubicInterpolationMode: 'monotone',   // mulus tapi tidak "nyungsep" di bawah 0
                    pointRadius: (c) => (!dashed && isMarker(c.dataIndex, c.chart.data.labels.length) ? 4 : 0),
                    pointHoverRadius: 6,
                    pointHitRadius: 14,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: color,
                    pointBorderWidth: 2,
                    pointHoverBackgroundColor: color,
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 3,
                };
            },

            drawDonut() {
                if (!data.donut.values.length) {
                    donut?.destroy();
                    donut = null;
                    return;
                }
                const d = foldSlices(data.donut);

                if (donut) {
                    donut.data.labels = d.labels;
                    donut.data.datasets[0].data = d.values;
                    donut.data.datasets[0].backgroundColor = d.colors;
                    donut.update();
                    return;
                }

                const pct = (chart, i) => {
                    const vals = chart.data.datasets[0].data;
                    const sum = vals.reduce((s, v) => s + v, 0);
                    return sum ? Math.round(vals[i] / sum * 100) : 0;
                };

                donut = new Chart(this.$refs.donut, {
                    type: 'doughnut',
                    data: {
                        labels: d.labels,
                        datasets: [{
                            data: d.values,
                            backgroundColor: d.colors,
                            borderWidth: 0,
                            borderRadius: 6,
                            spacing: 3,
                            hoverOffset: 8,
                        }],
                    },
                    plugins: [centerPlugin],
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        devicePixelRatio: HD(),
                        cutout: '74%',
                        layout: { padding: 10 },
                        animation: reduceMotion() ? false : { duration: 1200, easing: 'easeOutQuart', animateRotate: true, animateScale: true },
                        plugins: {
                            legend: {
                                ...legendStyle,
                                labels: {
                                    ...legendStyle.labels,
                                    padding: 12,
                                    // label + persen, supaya identitas tidak hanya dari warna
                                    generateLabels: (chart) =>
                                        Chart.overrides.doughnut.plugins.legend.labels.generateLabels(chart)
                                            .map((l) => ({ ...l, text: `${l.text} ${pct(chart, l.index)}%`, strokeStyle: l.fillStyle, fontColor: ink.soft })),
                                },
                            },
                            tooltip: {
                                ...tooltipStyle,
                                callbacks: { label: (c) => ` ${c.label}: ${rp(c.parsed)} (${pct(c.chart, c.dataIndex)}%)` },
                            },
                        },
                    },
                });
            },
        };
    });
});
