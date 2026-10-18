/*
 * Éducœur — JavaScript du site (Alpine.js est fourni par Livewire).
 */

const prefersReducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

document.addEventListener('alpine:init', () => {
    /**
     * x-reveal="délai en ms" : apparition douce quand l'élément entre à l'écran.
     * Sans effet si l'utilisateur a demandé à réduire les animations.
     */
    window.Alpine.directive('reveal', (el, { expression }, { cleanup }) => {
        if (prefersReducedMotion() || !('IntersectionObserver' in window)) {
            return;
        }

        el.classList.add('reveal');
        el.style.transitionDelay = `${Number(expression) || 0}ms`;

        const observer = new IntersectionObserver(
            (entries) => {
                if (entries.some((entry) => entry.isIntersecting)) {
                    el.classList.add('reveal-in');
                    observer.disconnect();
                }
            },
            { threshold: 0.12, rootMargin: '0px 0px -40px 0px' },
        );

        observer.observe(el);
        cleanup(() => observer.disconnect());
    });
});

/**
 * Carte OpenStreetMap (Leaflet), chargée à la demande pour garder la page rapide.
 */
window.educoeurMap = async (el, latitude, longitude, label) => {
    if (el.dataset.mapReady) {
        return;
    }
    el.dataset.mapReady = '1';

    const [{ default: L }] = await Promise.all([import('leaflet'), import('leaflet/dist/leaflet.css')]);

    const map = L.map(el, { scrollWheelZoom: false, attributionControl: true }).setView([latitude, longitude], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap',
    }).addTo(map);

    const icon = L.divIcon({
        className: '',
        html: '<span class="educoeur-pin"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 21s-7.5-4.6-9.6-9.2C.9 8.4 3 4.5 6.9 4.5c2.1 0 3.6 1.2 5.1 2.9 1.5-1.7 3-2.9 5.1-2.9 3.9 0 6 3.9 4.5 7.3C19.5 16.4 12 21 12 21Z"/></svg></span>',
        iconSize: [44, 44],
        iconAnchor: [22, 44],
    });

    L.marker([latitude, longitude], { icon, title: label }).addTo(map).bindPopup(`<strong>Éducœur</strong><br>${label}`);
};
