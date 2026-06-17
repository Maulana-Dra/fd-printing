import './bootstrap';

import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';
import collapse from '@alpinejs/collapse';

window.Alpine = Alpine;

Alpine.plugin(intersect);
Alpine.plugin(collapse);

// ── Alpine Store: Cart ────────────────────────────────────────────────────
Alpine.store('cart', {
    items: [],

    get count() {
        return this.items.length;
    },

    get subtotal() {
        return this.items.reduce((sum, item) => sum + ((parseFloat(item.unit_price) || 0) * (parseInt(item.quantity) || 0)), 0);
    },

    get formattedSubtotal() {
        return 'Rp ' + this.subtotal.toLocaleString('id-ID');
    },

    add(product) {
        const existing = this.items.find(i => i.product_id === product.product_id && JSON.stringify(i.selected_options) === JSON.stringify(product.selected_options));
        if (existing) {
            existing.quantity = (parseInt(existing.quantity) || 0) + (parseInt(product.quantity) || 1);
        } else {
            this.items.push(product);
        }
        this._toast(`${product.product_name || product.name} ditambahkan ke keranjang`);
    },

    async remove(itemId) {
        try {
            const res = await fetch(`/cart/${itemId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-HTTP-Method-Override': 'DELETE',
                }
            });
            if (res.ok) {
                const data = await res.json();
                this.items = data.items || [];
                // If we are on the cart page, reload to sync the page view
                if (window.location.pathname === '/cart') {
                    window.location.reload();
                }
            }
        } catch (e) {
            console.error('Failed to remove item:', e);
        }
    },

    async updateQty(itemId, qty) {
        if (qty < 1) {
            await this.remove(itemId);
            return;
        }
        try {
            const res = await fetch(`/cart/${itemId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-HTTP-Method-Override': 'PATCH',
                },
                body: JSON.stringify({ quantity: qty })
            });
            if (res.ok) {
                const data = await res.json();
                this.items = data.items || [];
                // If we are on the cart page, reload to sync the page view
                if (window.location.pathname === '/cart') {
                    window.location.reload();
                }
            }
        } catch (e) {
            console.error('Failed to update qty:', e);
        }
    },

    clear() {
        this.items = [];
    },

    _toast(message) {
        Alpine.store('toast').show(message, 'success');
    },
});

// ── Alpine Store: Toast Notifications ─────────────────────────────────────
Alpine.store('toast', {
    messages: [],
    _id: 0,

    show(message, type = 'info', duration = 3500) {
        const id = ++this._id;
        this.messages.push({ id, message, type });
        setTimeout(() => this.dismiss(id), duration);
    },

    dismiss(id) {
        const idx = this.messages.findIndex(m => m.id === id);
        if (idx !== -1) this.messages.splice(idx, 1);
    },
});

// ── Alpine Store: UI State ─────────────────────────────────────────────────
Alpine.store('ui', {
    mobileMenuOpen:  false,
    cartDrawerOpen:  false,
    searchFocused:   false,
    scrolled:        false,

    openCart()       { this.cartDrawerOpen = true; },
    closeCart()      { this.cartDrawerOpen = false; },
    toggleCart()     { this.cartDrawerOpen = !this.cartDrawerOpen; },
    openMobileMenu() { this.mobileMenuOpen = true; },
    closeMobileMenu(){ this.mobileMenuOpen = false; },
    toggleMenu()     { this.mobileMenuOpen = !this.mobileMenuOpen; },
});

// ── Scroll Listener ───────────────────────────────────────────────────────
window.addEventListener('scroll', () => {
    Alpine.store('ui').scrolled = window.scrollY > 10;
}, { passive: true });

// ── Format helpers (available globally) ──────────────────────────────────
window.formatRupiah = (amount) => {
    return 'Rp ' + Number(amount).toLocaleString('id-ID');
};

Alpine.start();
