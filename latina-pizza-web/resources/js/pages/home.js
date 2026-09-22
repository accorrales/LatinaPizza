import Swiper from 'swiper/bundle';

function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function initRevealAnimations(root) {
    const elements = Array.from(root.querySelectorAll('.home-reveal'));
    if (!elements.length) return;

    // Number direct reveal children so CSS can stagger their entrance.
    elements.forEach(element => {
        element.querySelectorAll('[data-reveal-child]').forEach((child, index) => {
            child.style.setProperty('--stagger', String(index));
        });
    });

    if (prefersReducedMotion() || !('IntersectionObserver' in window)) {
        elements.forEach(element => {
            element.style.opacity = '1';
            element.style.transform = 'none';
            element.classList.add('is-revealed');
        });
        return;
    }

    elements.forEach(element => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(24px)';
    });

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;

            entry.target.animate(
                [
                    { opacity: 0, transform: 'translateY(24px)' },
                    { opacity: 1, transform: 'translateY(0)' },
                ],
                {
                    duration: 420,
                    easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
                    fill: 'forwards',
                },
            );

            entry.target.classList.add('is-revealed');
            observer.unobserve(entry.target);
        });
    }, { threshold: 0.16 });

    elements.forEach(element => observer.observe(element));
}

function initTiltCards(root) {
    if (prefersReducedMotion() || window.matchMedia('(hover: none)').matches) return;

    const cards = Array.from(root.querySelectorAll('.home-tilt'));
    if (!cards.length) return;

    const MAX = 6; // degrees

    cards.forEach(card => {
        const onMove = event => {
            const rect = card.getBoundingClientRect();
            const px = (event.clientX - rect.left) / rect.width;
            const py = (event.clientY - rect.top) / rect.height;
            card.style.setProperty('--tilt-y', `${(px - 0.5) * MAX * 2}deg`);
            card.style.setProperty('--tilt-x', `${(0.5 - py) * MAX * 2}deg`);
            card.style.setProperty('--glow-x', `${px * 100}%`);
            card.style.setProperty('--glow-y', `${py * 100}%`);
        };

        const reset = () => {
            card.style.setProperty('--tilt-x', '0deg');
            card.style.setProperty('--tilt-y', '0deg');
        };

        card.addEventListener('pointermove', onMove);
        card.addEventListener('pointerleave', reset);
    });
}

function initHeroParallax(root) {
    if (prefersReducedMotion() || window.matchMedia('(hover: none)').matches) return;

    const hero = root.querySelector('.home-hero-swiper');
    if (!hero) return;

    const layers = Array.from(hero.querySelectorAll('.home-parallax'));
    if (!layers.length) return;

    hero.addEventListener('pointermove', event => {
        const rect = hero.getBoundingClientRect();
        const dx = (event.clientX - rect.left) / rect.width - 0.5;
        const dy = (event.clientY - rect.top) / rect.height - 0.5;

        layers.forEach((layer, index) => {
            const depth = (index + 1) * 14;
            layer.style.setProperty('--px', `${dx * depth}px`);
            layer.style.setProperty('--py', `${dy * depth}px`);
        });
    });

    hero.addEventListener('pointerleave', () => {
        layers.forEach(layer => {
            layer.style.setProperty('--px', '0px');
            layer.style.setProperty('--py', '0px');
        });
    });
}

function initHeroCarousel(root) {
    const hero = root.querySelector('.home-hero-swiper');
    if (!hero) return;

    const reducedMotion = prefersReducedMotion();

    new Swiper(hero, {
        loop: hero.querySelectorAll('.swiper-slide').length > 1,
        speed: reducedMotion ? 0 : 650,
        grabCursor: true,
        keyboard: { enabled: true },
        autoplay: reducedMotion
            ? false
            : {
                delay: 5200,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },
        pagination: {
            el: hero.querySelector('.home-hero-pagination'),
            clickable: true,
        },
        navigation: {
            nextEl: hero.querySelector('.home-hero-next'),
            prevEl: hero.querySelector('.home-hero-prev'),
        },
    });
}

function initRail(root, selector, { autoplay = false } = {}) {
    const rail = root.querySelector(selector);
    if (!rail) return;

    const reducedMotion = prefersReducedMotion();

    new Swiper(rail, {
        slidesPerView: 'auto',
        spaceBetween: 16,
        freeMode: true,
        grabCursor: true,
        watchOverflow: true,
        speed: reducedMotion ? 0 : 520,
        autoplay: autoplay && !reducedMotion
            ? {
                delay: 2800,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            }
            : false,
        breakpoints: {
            640: { spaceBetween: 18 },
        },
    });
}

function initPrimaryCtaFeedback(root) {
    const button = root.querySelector('.home-primary-cta');
    if (!button || prefersReducedMotion()) return;

    button.addEventListener('pointerdown', () => {
        button.animate(
            [
                { transform: 'scale(1)' },
                { transform: 'scale(0.97)' },
            ],
            { duration: 110, fill: 'forwards' },
        );
    });

    const release = () => {
        button.animate(
            [
                { transform: 'scale(0.97)' },
                { transform: 'scale(1)' },
            ],
            {
                duration: 220,
                easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
                fill: 'forwards',
            },
        );
    };

    button.addEventListener('pointerup', release);
    button.addEventListener('pointercancel', release);
    button.addEventListener('pointerleave', release);

    // Subtle magnetic pull toward the cursor.
    if (window.matchMedia('(hover: none)').matches) return;

    button.style.transition = 'transform 200ms cubic-bezier(0.22, 1, 0.36, 1)';
    button.addEventListener('pointermove', event => {
        const rect = button.getBoundingClientRect();
        const dx = (event.clientX - rect.left - rect.width / 2) / rect.width;
        const dy = (event.clientY - rect.top - rect.height / 2) / rect.height;
        button.style.transform = `translate(${dx * 8}px, ${dy * 8}px)`;
    });
    button.addEventListener('pointerleave', () => {
        button.style.transform = 'translate(0, 0)';
    });
}

function initHashNavigation(root) {
    const scrollToHash = () => {
        if (window.location.hash !== '#promociones') return;

        const target = root.querySelector('.home-promos-swiper');
        if (!target) return;

        window.requestAnimationFrame(() => {
            target.scrollIntoView({
                behavior: prefersReducedMotion() ? 'auto' : 'smooth',
                block: 'center',
            });
        });
    };

    scrollToHash();
    window.addEventListener('hashchange', scrollToHash);
}

export function initHomePage() {
    const root = document.querySelector('[data-home-page]');
    if (!root) return;

    initHeroCarousel(root);
    initRail(root, '.home-promos-swiper', { autoplay: true });
    initRail(root, '.home-products-swiper');
    initRevealAnimations(root);
    initTiltCards(root);
    initHeroParallax(root);
    initPrimaryCtaFeedback(root);
    initHashNavigation(root);
}
