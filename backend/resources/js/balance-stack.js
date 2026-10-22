document.addEventListener('alpine:init', () => {
    window.Alpine.data('balanceStack', ({ n, h, peek, step, maxBack }) => {
        const v = Math.min(n - 1, maxBack);

        return {
            order: Array.from({ length: n }, (_, i) => i),   // urutan kartu: indeks 0 = paling depan
            open: false,
            dx: 0,
            dragging: false,
            pid: null,
            sx: 0,
            sy: 0,

            height() { return this.open ? (n - 1) * step + h : h + v * peek; },

            next() { this.order.push(this.order.shift()); },
            prev() { this.order.unshift(this.order.pop()); },
            goTo(i) { for (let k = 0; k < n && this.order[0] !== i; k++) this.next(); },
            toggle() { if (n > 1) this.open = !this.open; },

            cardStyle(i) {
                const p = this.order.indexOf(i);

                if (this.open) {
                    return `top:${p * step}px;left:0;right:0;opacity:1;z-index:${p + 1};transform:none`;
                }

                const lvl = Math.min(p, maxBack);
                let opacity = p > maxBack ? 0 : 1;
                let transform = 'none';
                let transition = '';

                if (p === 0 && this.dragging) {
                    transform = `translateX(${this.dx}px) rotate(${this.dx / 24}deg)`;
                    opacity = 1 - Math.min(Math.abs(this.dx) / 400, 0.4);
                    transition = ';transition:none';
                }

                return `top:${(v - lvl) * peek}px;left:${lvl * 10}px;right:${lvl * 10}px;`
                     + `opacity:${opacity};z-index:${n - p};transform:${transform}${transition}`;
            },

            down(e) {
                if (n < 2 || (e.pointerType === 'mouse' && e.button !== 0)) return;
                this.pid = e.pointerId; this.sx = e.clientX; this.sy = e.clientY; this.dx = 0;
                try { e.currentTarget.setPointerCapture(e.pointerId); } catch (_) {}
            },

            move(e) {
                if (this.pid !== e.pointerId || this.open) return;
                const dx = e.clientX - this.sx, dy = e.clientY - this.sy;
                if (!this.dragging && Math.abs(dx) > 8 && Math.abs(dx) > Math.abs(dy)) this.dragging = true;
                if (this.dragging) this.dx = dx;
            },

            up(e) {
                if (this.pid !== e.pointerId) return;
                const dx = e.clientX - this.sx, dy = e.clientY - this.sy;
                const wasDragging = this.dragging;
                this.pid = null; this.dragging = false; this.dx = 0;

                if (Math.hypot(dx, dy) < 10) { this.toggle(); return; }   // ketukan biasa
                if (!this.open && wasDragging && Math.abs(dx) > 60) {
                    dx < 0 ? this.next() : this.prev();
                }
            },

            cancel() { this.pid = null; this.dragging = false; this.dx = 0; },
        };
    });
});