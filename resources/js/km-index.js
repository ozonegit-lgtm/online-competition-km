document.addEventListener('DOMContentLoaded', () => {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Fade-up cards gently when they enter the viewport.
    const revealItems = document.querySelectorAll('.km-reveal');

    if (reduceMotion || !('IntersectionObserver' in window)) {
        revealItems.forEach((item) => item.classList.add('is-visible'));
    } else {
        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;

                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -24px 0px',
        });

        revealItems.forEach((item, index) => {
            item.style.transitionDelay = `${Math.min((index % 4) * 35, 105)}ms`;
            revealObserver.observe(item);
        });
    }

    // Count-up is limited to podium scores only.
    const scoreItems = document.querySelectorAll('[data-count-score]');

    const animateScore = (element) => {
        if (element.dataset.counted === 'true') return;

        const target = Number.parseFloat(element.dataset.countScore);
        if (!Number.isFinite(target)) return;

        element.dataset.counted = 'true';

        if (reduceMotion) {
            element.textContent = target.toFixed(2);
            return;
        }

        const duration = 720;
        const startedAt = performance.now();

        const tick = (now) => {
            const progress = Math.min((now - startedAt) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);

            element.textContent = (target * eased).toFixed(2);

            if (progress < 1) {
                requestAnimationFrame(tick);
            } else {
                element.textContent = target.toFixed(2);
            }
        };

        element.textContent = '0.00';
        requestAnimationFrame(tick);
    };

    if (reduceMotion || !('IntersectionObserver' in window)) {
        scoreItems.forEach(animateScore);
    } else {
        const scoreObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;

                animateScore(entry.target);
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.55 });

        scoreItems.forEach((item) => scoreObserver.observe(item));
    }

    // Podium enters in a small 2 -> 1 -> 3 rhythm.
    document.querySelectorAll('[data-podium-stage]').forEach((stage) => {
        const podiumItems = [...stage.querySelectorAll('[data-podium-item]')];

        podiumItems.forEach((item) => {
            const rank = Number(item.dataset.rank);
            const delay = rank === 2 ? 0 : rank === 1 ? 120 : 240;
            item.style.setProperty('--podium-delay', `${delay}ms`);
        });

        const enterPodium = () => {
            podiumItems.forEach((item) => item.classList.add('is-entered'));
        };

        if (reduceMotion || !('IntersectionObserver' in window)) {
            enterPodium();
            return;
        }

        const podiumObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;

                enterPodium();
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.2 });

        podiumObserver.observe(stage);
    });

    // Remove skeleton when each image finishes loading.
    document.querySelectorAll('img[data-km-image]').forEach((image) => {
        const frame = image.closest('.km-image-frame');
        if (!frame) return;

        const finish = () => frame.classList.remove('is-loading');

        if (image.complete) {
            finish();
        } else {
            image.addEventListener('load', finish, { once: true });
            image.addEventListener('error', finish, { once: true });
        }
    });

    // Search button loading state.
    document.querySelectorAll('[data-search-form]').forEach((form) => {
        form.addEventListener('submit', () => {
            const button = form.querySelector('[data-search-button]');
            const spinner = form.querySelector('[data-search-spinner]');
            const label = form.querySelector('[data-search-label]');
            const icon = form.querySelector('[data-search-icon]');

            if (button) button.disabled = true;
            spinner?.classList.remove('hidden');
            icon?.classList.add('hidden');

            if (label && label.textContent.trim()) {
                label.textContent = 'กำลังค้นหา...';
                label.classList.remove('hidden');
            }
        });
    });

    // Sort dropdown with a small open/close animation.
    const trigger = document.getElementById('sort-trigger');
    const panel = document.getElementById('sort-panel');
    const chevron = document.getElementById('sort-chevron');
    const label = document.getElementById('sort-label');
    const hidden = document.getElementById('sort-value');

    const setSortOpen = (open) => {
        if (!trigger || !panel) return;

        panel.classList.toggle('is-open', open);
        chevron?.classList.toggle('rotate-180', open);
        trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
    };

    if (trigger && panel) {
        trigger.setAttribute('aria-expanded', 'false');

        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            setSortOpen(!panel.classList.contains('is-open'));
        });

        document.querySelectorAll('.sort-option').forEach((button) => {
            button.addEventListener('click', () => {
                const form = button.closest('form');
                if (!form || !hidden || !label) return;

                hidden.value = button.dataset.value;
                label.textContent = button.dataset.label;

                window.setTimeout(
                    () => form.submit(),
                    reduceMotion ? 0 : 90
                );
            });
        });

        document.addEventListener('click', (event) => {
            if (!trigger.contains(event.target) && !panel.contains(event.target)) {
                setSortOpen(false);
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                setSortOpen(false);
                trigger.focus();
            }
        });
    }
});
