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

/*
 * Les helpers ci-dessous sont des fonctions globales, et non des Alpine.data() :
 * ce module peut se charger après le démarrage d'Alpine (Livewire le lance).
 * Ils sont appelés avec « ?. » : sans eux, la page reste correcte.
 */

/**
 * Compteur animé : x-init="window.educoeurCountUp?.($el, 42)".
 * Le serveur affiche déjà la valeur finale ; le nombre défile de 0 jusqu'à elle.
 */
window.educoeurCountUp = (el, target) => {
    target = Number(target) || 0;

    if (prefersReducedMotion() || target === 0) {
        return;
    }

    // Séparateur de milliers : espace simple, comme Format::money()
    const format = (value) => String(Math.round(value)).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    const duration = 900;
    const start = performance.now();
    const step = (now) => {
        const progress = Math.min((now - start) / duration, 1);
        el.textContent = format(target * (1 - Math.pow(1 - progress, 3)));
        if (progress < 1) {
            requestAnimationFrame(step);
        }
    };
    requestAnimationFrame(step);
};

/**
 * État d'une séance du jour : « soon » (avec le temps restant), « live » ou « done ».
 * Utilisé par x-data="{ state: 'soon', label: '', … }" + x-init="window.educoeurLessonClock?.($data, début, fin)".
 */
window.educoeurLessonClock = (data, startsAt, endsAt) => {
    const tick = () => {
        const now = Date.now() / 1000;
        const minutes = Math.ceil((startsAt - now) / 60);

        data.state = now >= endsAt ? 'done' : now >= startsAt ? 'live' : 'soon';
        data.label = minutes >= 60 ? `${Math.floor(minutes / 60)} h ${String(minutes % 60).padStart(2, '0')}` : `${minutes} min`;
    };

    tick();
    const timer = setInterval(tick, 30000);
    document.addEventListener('livewire:navigating', () => clearInterval(timer), { once: true });
};

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

/*
 * PWA : enregistrement du service worker et installation de l'application.
 */
try {
    // Langue de la page hors connexion (public/offline.html)
    localStorage.setItem('educoeur-locale', document.documentElement.lang);
} catch (error) {
    // Stockage indisponible (navigation privée) : la page hors connexion restera en français
}

if ('serviceWorker' in navigator && window.isSecureContext) {
    window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
}

/**
 * Bouton « Installer l'application » : le navigateur propose l'installation via
 * l'événement beforeinstallprompt (Chrome, Edge, Android). Sur iPhone, passer par « Partager ».
 */
window.educoeurInstall = { prompt: null };

window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    window.educoeurInstall.prompt = event;
    window.dispatchEvent(new CustomEvent('educoeur:installable'));
});

window.addEventListener('appinstalled', () => {
    window.educoeurInstall.prompt = null;
    window.dispatchEvent(new CustomEvent('educoeur:installed'));
});

window.educoeurInstallApp = async () => {
    const prompt = window.educoeurInstall.prompt;

    if (!prompt) {
        return;
    }

    prompt.prompt();
    await prompt.userChoice;
    window.educoeurInstall.prompt = null;
    window.dispatchEvent(new CustomEvent('educoeur:installed'));
};
