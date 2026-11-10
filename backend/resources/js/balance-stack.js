document.addEventListener('alpine:init', () => {
    window.Alpine.data('balanceStack', ({ n, peek, maxBack }) => {
        const v = Math.min(n - 1, maxBack);

        return {
            order: Array.from({ length: n }, (_, i) => i),   // indeks 0 = kartu paling depan
            show: false,                                     // saldo tersensor secara default
            dx: 0,
            dragging: false,
            pid: null,
            sx: 0,
            sy: 0,

            next() { this.order.push(this.order.shift()); },
            prev() { this.order.unshift(this.order.pop()); },
            goTo(i) { for (let k = 0; k < n && this.order[0] !== i; k++) this.next(); },

            // ketuk strip kartu di belakang: bawa ke depan
            pick(i) { if (this.order.indexOf(i) > 0) this.goTo(i); },

            cardStyle(i) {
                const p = this.order.indexOf(i);
                const lvl = Math.min(p, maxBack);
                let opacity = p > maxBack ? 0 : 1;
                let transform = 'none';
                let transition = '';

                if (p === 0 && this.dragging) {
                    transform = `translateX(${this.dx}px) rotate(${this.dx / 28}deg)`;
                    opacity = 1 - Math.min(Math.abs(this.dx) / 420, 0.4);
                    transition = ';transition:none';
                }

                // tinggi kartu = tinggi tumpukan dikurangi ruang strip kartu belakang
                return `top:${(v - lvl) * peek}px;height:calc(100% - ${v * peek}px);`
                     + `left:${lvl * 8}px;right:${lvl * 8}px;`
                     + `opacity:${opacity};z-index:${n - p};transform:${transform};`
                     + `pointer-events:${p > maxBack ? 'none' : 'auto'};`
                     + `filter:drop-shadow(0 8px 10px rgba(15,23,42,0.18))${transition}`;
            },

            down(e) {
                if (n < 2) return;
                if (e.target.closest('[data-nosnap]')) return;          // tombol di dalam kartu
                if (e.pointerType === 'mouse' && e.button !== 0) return;
                this.pid = e.pointerId; this.sx = e.clientX; this.sy = e.clientY; this.dx = 0;
            },

            move(e) {
                if (this.pid !== e.pointerId) return;
                const dx = e.clientX - this.sx, dy = e.clientY - this.sy;

                if (!this.dragging && Math.abs(dx) > 8 && Math.abs(dx) > Math.abs(dy)) {
                    this.dragging = true;
                    // pointer baru "ditangkap" saat benar-benar menggeser, supaya ketukan biasa tetap sampai ke kartu
                    try { this.$refs.stage.setPointerCapture(e.pointerId); } catch (_) {}
                }
                if (this.dragging) this.dx = dx;
            },

            up(e) {
                if (this.pid !== e.pointerId) return;
                const dx = e.clientX - this.sx;
                const was = this.dragging;
                this.pid = null; this.dragging = false; this.dx = 0;

                if (was && Math.abs(dx) > 60) dx < 0 ? this.next() : this.prev();
            },

            cancel() { this.pid = null; this.dragging = false; this.dx = 0; },
        };
    });
});