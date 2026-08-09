window.BloomPWA = {
    deferredPrompt: null,
    registration: null,

    init() {
        this.registerServiceWorker();
        this.handleInstallPrompt();
    },

    registerServiceWorker() {
        if (!('serviceWorker' in navigator)) return;

        window.addEventListener('load', () => {
            navigator.serviceWorker
                .register('/sw.js')
                .then((reg) => {
                    this.registration = reg;
                })
                .catch(() => {});
        });
    },

    handleInstallPrompt() {
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            this.deferredPrompt = e;
            window.dispatchEvent(new CustomEvent('bloom:installable'));
        });

        window.addEventListener('appinstalled', () => {
            this.deferredPrompt = null;
            if (window.bloomToast) {
                window.bloomToast.success('Mimiw berhasil dipasang');
            }
        });
    },

    promptInstall() {
        if (!this.deferredPrompt) return false;
        this.deferredPrompt.prompt();
        this.deferredPrompt.userChoice.then(() => {
            this.deferredPrompt = null;
        });
        return true;
    },

    isStandalone() {
        return (
            window.matchMedia('(display-mode: standalone)').matches ||
            window.navigator.standalone === true
        );
    },

    async requestPermission() {
        if (!('Notification' in window)) return false;
        if (Notification.permission === 'granted') return true;
        const permission = await Notification.requestPermission();
        return permission === 'granted';
    },

    notify(title, body, options = {}) {
        if (!('Notification' in window) || Notification.permission !== 'granted') return;
        if (this.isStandalone() || document.visibilityState === 'hidden') {
            this.registration?.showNotification(title, {
                body,
                icon: '/icons/icon-192.png',
                badge: '/icons/icon-192.png',
                vibrate: [100, 50, 100],
                ...options,
            });
        } else {
            const notification = new Notification(title, {
                body,
                icon: '/icons/icon-192.png',
                ...options,
            });
            setTimeout(() => notification.close(), 6000);
        }
    },
};

if (window.Swal) {
    window.bloomNotify = window.BloomPWA;
}

document.addEventListener('DOMContentLoaded', () => {
    window.BloomPWA.init();
});
