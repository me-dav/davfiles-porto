// resources/js/finance-charts.js
import Chart from 'chart.js/auto';

const cols = { income: '#a855f7', expense: '#f59e0b' };
const palette = ['#ec4899', '#a855f7', '#f59e0b', '#38bdf8', '#10b981', '#6366f1', '#f43f5e', '#14b8a6', '#84cc16', '#64748b'];
const rp = (v) => 'Rp ' + Number(v).toLocaleString('id-ID');

// Gradient area: warna di garis, memudar ke transparan di bawah.
// Dibuat dari ukuran chart asli (bukan angka tetap) supaya selalu pas.
const areaGradient = (hex) => (context) => {
    const { chart } = context;
    const { ctx, chartArea } = chart;
    if (!chartArea) return hex + '00';   // chart belum selesai di-layout
    const g = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
    g.addColorStop(0, hex + '66');
    g.addColorStop(1, hex + '00');
    return g;
};

document.addEventListener('alpine:init', () => {
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
                    sets.push(this.ds('Masuk (pembanding)', data.cmp.income, cols.income + '88', true));
                    sets.push(this.ds('Keluar (pembanding)', data.cmp.expense, cols.expense + '88', true));
                }

                // Chart sudah ada: update datanya saja, supaya garis beranimasi bergeser/tumbuh
                if (line) {
                    line.data.labels = data.labels;
                    line.data.datasets = sets;
                    line.update();
                    return;
                }

                line = new Chart(this.$refs.bar, {
                    type: 'line',
                    data: { labels: data.labels, datasets: sets },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: { duration: 900, easing: 'easeOutQuart' },
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 8, usePointStyle: true } },
                            tooltip: { callbacks: { label: (c) => `${c.dataset.label}: ${rp(c.parsed.y)}` } },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { display: false },       // garis lurus horizontal dihapus
                                border: { display: false },
                                ticks: {
                                    color: '#94a3b8',
                                    callback: (v) => v >= 1e6 ? (v / 1e6) + ' jt' : v >= 1e3 ? (v / 1e3) + ' rb' : v,
                                },
                            },
                            x: {
                                grid: { display: false },       // garis lurus vertikal dihapus
                                border: { display: false },
                                ticks: { color: '#94a3b8' },
                            },
                        },
                    },
                });
            },

            ds(label, values, color, dashed = false) {
                return {
                    label,
                    data: values,
                    borderColor: color,
                    backgroundColor: dashed ? 'transparent' : areaGradient(color),
                    fill: !dashed,
                    borderDash: dashed ? [5, 4] : [],
                    borderWidth: 2.5,
                    tension: 0.4,
                    pointRadius: 2,            // bullet kecil
                    pointHoverRadius: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: color,
                    pointBorderWidth: 1.5,
                };
            },

            drawDonut() {
                const d = data.donut;

                if (!d.values.length) {
                    donut?.destroy();
                    donut = null;
                    return;
                }

                if (donut) {
                    donut.data.labels = d.labels;
                    donut.data.datasets[0].data = d.values;
                    donut.update();
                    return;
                }

                donut = new Chart(this.$refs.donut, {
                    type: 'doughnut',
                    data: {
                        labels: d.labels,
                        datasets: [{ data: d.values, backgroundColor: palette, borderWidth: 0 }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '72%',
                        animation: { duration: 900, easing: 'easeOutQuart' },
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 8, usePointStyle: true } },
                            tooltip: { callbacks: { label: (c) => `${c.label}: ${rp(c.parsed)}` } },
                        },
                    },
                });
            },
        };
    });
});