// resources/js/finance-charts.js
import Chart from 'chart.js/auto';

const palette = ['#6366f1', '#f43f5e', '#10b981', '#f59e0b', '#0ea5e9',
                 '#8b5cf6', '#ec4899', '#14b8a6', '#84cc16', '#64748b'];
const rp = (v) => 'Rp ' + Number(v).toLocaleString('id-ID');

document.addEventListener('alpine:init', () => {
    window.Alpine.data('financeCharts', (initial) => {
        // chart disimpan di closure, bukan di state Alpine (proxy Alpine bikin Chart.js error)
        let bar = null, donut = null, data = initial;

        return {
            kind: 'bar',

            init() { this.draw(); },
            update(c) { data = c; this.draw(); },
            setKind(k) { this.kind = k; this.draw(); },

            draw() {
                this.drawBar();
                this.drawDonut();
            },

            drawBar() {
                bar?.destroy();
                const isBar = this.kind === 'bar';
                const ds = (label, values, color, dashed = false) => ({
                    label, data: values, borderColor: color,
                    backgroundColor: isBar ? color : color + '22',
                    borderDash: dashed ? [5, 4] : [], borderWidth: 2, tension: 0.3,
                    borderRadius: isBar ? 4 : 0, fill: false,
                });

                const sets = [
                    ds('Masuk', data.income, '#10b981'),
                    ds('Keluar', data.expense, '#f43f5e'),
                ];
                if (data.cmp) {
                    sets.push(ds('Masuk (pembanding)', data.cmp.income, '#10b98166', true));
                    sets.push(ds('Keluar (pembanding)', data.cmp.expense, '#f43f5e66', true));
                }

                bar = new Chart(this.$refs.bar, {
                    type: this.kind,
                    data: { labels: data.labels, datasets: sets },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true } },
                            tooltip: { callbacks: { label: (c) => `${c.dataset.label}: ${rp(c.parsed.y)}` } },
                        },
                        scales: {
                            y: { beginAtZero: true, ticks: { callback: (v) => v >= 1e6 ? (v / 1e6) + ' jt' : v >= 1e3 ? (v / 1e3) + ' rb' : v } },
                            x: { grid: { display: false } },
                        },
                    },
                });
            },

            drawDonut() {
                donut?.destroy();
                const d = data.donut;
                if (!d.values.length) { return; }   // kosong: kanvas dibiarkan polos
                donut = new Chart(this.$refs.donut, {
                    type: 'doughnut',
                    data: { labels: d.labels, datasets: [{ data: d.values, backgroundColor: palette, borderWidth: 2, borderColor: '#fff' }] },
                    options: {
                        responsive: true, maintainAspectRatio: false, cutout: '62%',
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true } },
                            tooltip: { callbacks: { label: (c) => `${c.label}: ${rp(c.parsed)}` } },
                        },
                    },
                });
            },
        };
    });
});