import './bootstrap';

import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';
import collapse from '@alpinejs/collapse';

window.Alpine = Alpine;

Alpine.plugin(intersect);
Alpine.plugin(collapse);

// ── Alpine Store: Cart ────────────────────────────────────────────────────
Alpine.store('cart', {
    /** Array item: { id, name, price, qty, thumbnail, options } */
    items: JSON.parse(localStorage.getItem('fd_cart') ?? '[]'),

    get count() {
        return this.items.reduce((sum, item) => sum + item.qty, 0);
    },

    get subtotal() {
        return this.items.reduce((sum, item) => sum + (item.price * item.qty), 0);
    },

    get formattedSubtotal() {
        return 'Rp ' + this.subtotal.toLocaleString('id-ID');
    },

    add(product) {
        const existing = this.items.find(i => i.id === product.id && JSON.stringify(i.options) === JSON.stringify(product.options));
        if (existing) {
            existing.qty += product.qty ?? 1;
        } else {
            this.items.push({ ...product, qty: product.qty ?? 1 });
        }
        this._persist();
        this._toast(`${product.name} ditambahkan ke keranjang`);
    },

    remove(index) {
        this.items.splice(index, 1);
        this._persist();
    },

    updateQty(index, qty) {
        if (qty < 1) {
            this.remove(index);
            return;
        }
        this.items[index].qty = qty;
        this._persist();
    },

    clear() {
        this.items = [];
        this._persist();
    },

    _persist() {
        localStorage.setItem('fd_cart', JSON.stringify(this.items));
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
