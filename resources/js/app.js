import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

// Cart drawer helpers (progressive enhancement; forms still work without JS)
document.addEventListener('click', (e) => {
    const open = e.target.closest('[data-open-cart]');
    const close = e.target.closest('[data-close-cart]');
    const drawer = document.getElementById('cart-drawer');
    if (!drawer) return;
    if (open) {
        drawer.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    if (close || e.target === drawer) {
        drawer.classList.add('hidden');
        document.body.style.overflow = '';
    }
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const drawer = document.getElementById('cart-drawer');
        if (drawer) {
            drawer.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }
});
