import Swiper from 'swiper/bundle';

function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function initRevealAnimations(root) {
    const elements = Array.from(root.querySelectorAll('.home-reveal'));
    if (!elements.length) return;

    if (prefersReducedMotion() || !('IntersectionObserver' in window)) {
        elements.forEach(element => {
            element.style.opacity = '1';
            element.style.transform = 'none';
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
                    duration: 360,
                    easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
                    fill: 'forwards',
                },
            );

            observer.unobserve(entry.target);
        });
    }, { threshold: 0.16 });

    elements.forEach(element => observer.observe(element));
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
}

export function initHomePage() {
    const root = document.querySelector('[data-home-page]');
    if (!root) return;

    initHeroCarousel(root);
    initRail(root, '.home-promos-swiper', { autoplay: true });
    initRail(root, '.home-products-swiper');
    initRevealAnimations(root);
    initPrimaryCtaFeedback(root);
}
