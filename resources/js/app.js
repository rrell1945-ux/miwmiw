import Alpine from 'alpinejs';
import Swal from 'sweetalert2';
import AOS from 'aos';

window.Alpine = Alpine;
window.Swal = Swal;

Alpine.data('bloomApp', () => ({
    theme: localStorage.getItem('mimiw-theme') ?? 'light',
    init() {
        this.applyTheme();

        window.addEventListener('bloom:theme-changed', (e) => {
            this.theme = e.detail?.theme || this.theme;
            this.applyTheme();
        });
    },
    toggleTheme() {
        this.theme = this.theme === 'light' ? 'dark' : 'light';
        localStorage.setItem('mimiw-theme', this.theme);
        this.applyTheme();
        window.dispatchEvent(new CustomEvent('bloom:theme-changed', { detail: { theme: this.theme } }));
    },
    applyTheme() {
        const isDark = this.theme === 'dark';
        document.documentElement.classList.toggle('dark', isDark);
        document.documentElement.setAttribute('data-color-scheme', isDark ? 'dark' : 'light');
    },
}));

/**
 * Small count-up animation for numeric stat values (e.g. "15 hari").
 * Only runs when the value starts with a plain number so dates and
 * phase labels are left untouched.
 */
Alpine.data('bloomCountUp', (raw) => ({
    raw: String(raw ?? '—'),
    display: String(raw ?? '—'),
    init() {
        const match = this.raw.match(/^(\d+)(.*)$/);
        if (!match) return;

        const target = parseInt(match[1], 10);
        const suffix = match[2] ?? '';
        const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (prefersReduced) return;

        const start = performance.now();
        const duration = 900;
        const easeOut = (t) => 1 - Math.pow(1 - t, 3);

        const step = (now) => {
            const t = Math.min((now - start) / duration, 1);
            this.display = Math.round(target * easeOut(t)) + suffix;
            if (t < 1) requestAnimationFrame(step);
        };

        requestAnimationFrame(step);
    },
}));

const toastOptions = (type) => ({
    icon: type,
    timer: type === 'error' ? 2600 : 2200,
    showConfirmButton: false,
    position: 'top-end',
    toast: true,
    timerProgressBar: true,
    background: document.documentElement.classList.contains('dark') ? '#1f2937' : (window.innerWidth < 640 ? '#ffffff' : 'rgba(255,255,255,0.92)'),
    color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#374151',
});

window.bloomToast = {
    success(message) {
        Swal.fire({ ...toastOptions('success'), title: message });
    },
    error(message) {
        Swal.fire({
            ...toastOptions('error'),
            title: message || 'Terjadi kesalahan',
            background: '#ffffff',
        });
    },
    confirm({ title, text, confirmText = 'Ya, lanjutkan', onConfirm }) {
        Swal.fire({
            title,
            text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EC4899',
            cancelButtonColor: '#9CA3AF',
            confirmButtonText: confirmText,
            cancelButtonText: 'Batal',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) onConfirm();
        });
    },
};

document.addEventListener('DOMContentLoaded', () => {
    if (typeof AOS !== 'undefined' && window.matchMedia('(prefers-reduced-motion: no-preference)').matches) {
        AOS.init({
            duration: 400,
            easing: 'ease-out-cubic',
            once: true,
            offset: 30,
        });
    }
});

Alpine.start();
