document.addEventListener('DOMContentLoaded', () => {
    document.documentElement.classList.add('js-ready');
    initNavToggle();
    initAuthTabs();
    initLevelForm();
    initStudyChat();
    initMotion();
    initLevelCardTilt();
    initScrollHints();
    initDashboardRouteToggle();
    initHeroParticles();
    initHeroParallax();
    initCounterAnimation();
    initMagneticButtons();
    initScrollHideIndicator();
});

function initNavToggle() {
    const toggle = document.querySelector('[data-nav-toggle]');
    const nav = document.getElementById('main-nav');

    if (!toggle || !nav) {
        return;
    }

    const closeNav = () => {
        toggle.classList.remove('is-open');
        nav.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('nav-open');
    };

    toggle.addEventListener('click', () => {
        const isOpen = toggle.classList.toggle('is-open');
        nav.classList.toggle('is-open', isOpen);
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        document.body.classList.toggle('nav-open', isOpen);
    });

    nav.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            closeNav();
        });
    });

    document.addEventListener('click', (event) => {
        if (!toggle.contains(event.target) && !nav.contains(event.target)) {
            closeNav();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeNav();
        }
    });
}

function initAuthTabs() {
    const tabs = document.querySelectorAll('.auth-tab');
    const panels = document.querySelectorAll('.auth-panel');

    if (!tabs.length || !panels.length) {
        return;
    }

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.authTarget;

            tabs.forEach((candidate) => candidate.classList.remove('is-active'));
            panels.forEach((panel) => panel.classList.remove('is-active'));

            tab.classList.add('is-active');
            document.getElementById(target)?.classList.add('is-active');
        });
    });
}

function initLevelForm() {
    const form = document.getElementById('level-form');

    if (!form) {
        return;
    }

    const feedback = document.getElementById('level-feedback');
    const nextActions = document.getElementById('next-actions');
    const pointsNode = document.getElementById('player-points');
    const livesNode = document.getElementById('player-lives');
    const progressFill = document.getElementById('level-progress-fill');

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const button = form.querySelector('button[type="submit"]');
        const formData = new FormData(form);
        const formula = String(formData.get('formula') || '').trim();

        if (!formula) {
            setFeedback(feedback, 'error', 'Escribe una fórmula antes de validar.');
            return;
        }

        if (button) {
            button.disabled = true;
            button.textContent = 'Validando...';
        }

        try {
            const response = await fetch('submit_level.php', {
                method: 'POST',
                body: formData,
            });

            const payload = await response.json();

            if (!response.ok || !payload.success) {
                throw new Error(payload.message || 'No se pudo validar la fórmula.');
            }

            const toneType = payload.correct ? 'success' : 'error';
            playTone(toneType);

            if (pointsNode) {
                pointsNode.textContent = String(payload.points);
            }

            if (livesNode) {
                livesNode.textContent = payload.vip ? '∞' : String(payload.lives);
            }

            if (progressFill) {
                progressFill.style.width = `${payload.progressPercent}%`;
            }

            if (payload.correct) {
                setFeedback(feedback, 'success', payload.message);
                nextActions?.classList.add('is-visible');
                celebrate();
            } else {
                const expected = payload.expected ? ` Fórmula esperada: ${payload.expected}` : '';
                setFeedback(feedback, 'error', `${payload.message}${expected}`);
            }
        } catch (error) {
            setFeedback(feedback, 'error', error.message || 'Error inesperado al validar.');
        } finally {
            if (button) {
                button.disabled = false;
                button.textContent = 'Validar fórmula';
            }
        }
    });
}

function initStudyChat() {
    const widget = document.querySelector('[data-study-chat]');
    const panel = document.querySelector('[data-chat-panel]');
    const toggle = document.querySelector('[data-chat-toggle]');
    const close = document.querySelector('[data-chat-close]');
    const form = document.getElementById('study-chat-form');
    const thread = document.getElementById('study-chat-thread');

    if (!widget || !panel || !toggle || !close || !form || !thread) {
        return;
    }

    const textarea = form.querySelector('textarea[name="message"]');
    const button = form.querySelector('button[type="submit"]');
    const csrfToken = form.querySelector('input[name="csrf_token"]')?.value || '';
    const levelId = form.dataset.levelId || '';
    const history = [];

    const syncOpenState = () => {
        const open = widget.hasAttribute('open');
        widget.classList.toggle('is-open', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        panel.setAttribute('aria-hidden', open ? 'false' : 'true');

        if (open) {
            window.setTimeout(() => textarea?.focus(), 120);
        }
    };

    syncOpenState();
    widget.addEventListener('toggle', syncOpenState);

    close.addEventListener('click', () => {
        widget.removeAttribute('open');
        syncOpenState();
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const message = String(textarea?.value || '').trim();
        if (!message) {
            return;
        }

        widget.setAttribute('open', 'open');
        syncOpenState();

        appendChatMessage(thread, 'user', message);
        history.push({ role: 'user', content: message });
        if (textarea) {
            textarea.value = '';
        }

        if (button) {
            button.disabled = true;
            button.textContent = 'Pensando...';
        }

        try {
            const response = await fetch('study_chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    csrf_token: csrfToken,
                    level_id: levelId,
                    message,
                    history,
                }),
            });

            const payload = await response.json();

            if (!response.ok || !payload.success) {
                throw new Error(payload.message || 'No se pudo obtener ayuda del asistente.');
            }

            appendChatMessage(thread, 'assistant', payload.reply);
            history.push({ role: 'assistant', content: payload.reply });
            while (history.length > 6) {
                history.shift();
            }
        } catch (error) {
            appendChatMessage(thread, 'assistant', error.message || 'No se pudo obtener ayuda del asistente.');
        } finally {
            if (button) {
                button.disabled = false;
                button.textContent = 'Preguntar al asistente';
            }
        }
    });
}

function appendChatMessage(thread, role, content) {
    const item = document.createElement('article');
    item.className = `chat-message chat-message--${role}`;

    const title = document.createElement('strong');
    title.textContent = role === 'user' ? 'Tú' : 'Asistente';

    const body = document.createElement('p');
    body.textContent = content;

    item.appendChild(title);
    item.appendChild(body);
    thread.appendChild(item);
    thread.scrollTop = thread.scrollHeight;
}

function setFeedback(node, type, message) {
    if (!node) {
        return;
    }

    node.className = `feedback-box is-visible is-${type}`;
    node.textContent = message;
}

function playTone(type) {
    const AudioContextClass = window.AudioContext || window.webkitAudioContext;

    if (!AudioContextClass) {
        return;
    }

    const context = new AudioContextClass();
    const oscillator = context.createOscillator();
    const gainNode = context.createGain();

    oscillator.connect(gainNode);
    gainNode.connect(context.destination);

    oscillator.type = type === 'success' ? 'triangle' : 'sawtooth';
    oscillator.frequency.setValueAtTime(type === 'success' ? 660 : 180, context.currentTime);
    oscillator.frequency.exponentialRampToValueAtTime(type === 'success' ? 990 : 110, context.currentTime + 0.22);

    gainNode.gain.setValueAtTime(0.0001, context.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.22, context.currentTime + 0.02);
    gainNode.gain.exponentialRampToValueAtTime(0.0001, context.currentTime + 0.3);

    oscillator.start(context.currentTime);
    oscillator.stop(context.currentTime + 0.31);

    oscillator.addEventListener('ended', () => {
        context.close().catch(() => {});
    });
}

function celebrate() {
    const burst = document.createElement('div');
    burst.className = 'confetti-burst';
    document.body.appendChild(burst);

    const colors = ['#fbbf24','#34d399','#60a5fa','#f472b6','#a78bfa','#fb923c','#e879f9','#facc15'];
    const count = 50;

    for (let i = 0; i < count; i += 1) {
        const piece = document.createElement('span');
        piece.className = 'confetti-piece';
        const isCircle = Math.random() > 0.65;
        const size = 6 + Math.random() * 10;
        piece.style.setProperty('--x', `${Math.random() * 100}%`);
        piece.style.setProperty('--delay', `${Math.random() * 0.5}s`);
        piece.style.setProperty('--rotate', `${Math.random() * 360}deg`);
        piece.style.setProperty('--clr', colors[Math.floor(Math.random() * colors.length)]);
        piece.style.setProperty('--w', `${isCircle ? size : size * 0.6}px`);
        piece.style.setProperty('--h', `${isCircle ? size : size * 1.4}px`);
        piece.style.setProperty('--br', isCircle ? '50%' : `${2 + Math.random() * 3}px`);
        piece.style.setProperty('--dur', `${1.8 + Math.random() * 1.2}s`);
        piece.style.setProperty('--fall', `${70 + Math.random() * 30}vh`);
        piece.style.setProperty('--spin', `${500 + Math.random() * 400}deg`);
        piece.style.setProperty('--drift', `${-35 + Math.random() * 70}px`);
        piece.style.setProperty('--sway', `${0.8 + Math.random() * 1.2}s`);
        burst.appendChild(piece);
    }

    window.setTimeout(() => {
        burst.remove();
    }, 3500);
}

function initMotion() {
    if (!window.gsap) {
        return;
    }

    if (window.ScrollTrigger) {
        window.gsap.registerPlugin(window.ScrollTrigger);
    }

    const revealBlocks = document.querySelectorAll('[data-reveal]');
    revealBlocks.forEach((element, index) => {
        const config = {
            opacity: 0,
            y: 28,
            duration: 0.8,
            ease: 'power3.out',
            delay: index < 2 ? index * 0.08 : 0,
        };

        if (window.ScrollTrigger) {
            window.gsap.from(element, {
                ...config,
                scrollTrigger: {
                    trigger: element,
                    start: 'top 88%',
                    once: true,
                },
            });
            return;
        }

        window.gsap.from(element, config);
    });

    document.querySelectorAll('[data-stagger-group]').forEach((group) => {
        const items = group.querySelectorAll('[data-reveal-item]');
        if (!items.length) {
            return;
        }

        if (window.ScrollTrigger) {
            window.gsap.from(items, {
                opacity: 0,
                y: 22,
                duration: 0.7,
                stagger: 0.1,
                ease: 'power2.out',
                scrollTrigger: {
                    trigger: group,
                    start: 'top 88%',
                    once: true,
                },
            });
            return;
        }

        window.gsap.from(items, {
            opacity: 0,
            y: 22,
            duration: 0.7,
            stagger: 0.1,
            ease: 'power2.out',
        });
    });

    const orbits = document.querySelectorAll('.hero-orbit');
    orbits.forEach((orbit, index) => {
        window.gsap.to(orbit, {
            y: index % 2 === 0 ? -10 : 12,
            x: index === 1 ? 8 : -6,
            duration: 2.4 + index * 0.4,
            repeat: -1,
            yoyo: true,
            ease: 'sine.inOut',
        });
    });
}

function initLevelCardTilt() {
    if (window.matchMedia('(pointer: coarse)').matches) {
        return;
    }

    document.querySelectorAll('[data-level-card]').forEach((card) => {
        card.addEventListener('mousemove', (event) => {
            const bounds = card.getBoundingClientRect();
            const px = (event.clientX - bounds.left) / bounds.width;
            const py = (event.clientY - bounds.top) / bounds.height;
            const rotateY = (px - 0.5) * 7;
            const rotateX = (0.5 - py) * 7;

            card.style.transform = `perspective(900px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-4px)`;
        });

        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
        });
    });
}

function initScrollHints() {
    document.querySelectorAll('.excel-grid-wrapper').forEach((wrapper) => {
        const update = () => {
            const isMobile = window.matchMedia('(max-width: 640px)').matches;
            const hasOverflow = !isMobile && wrapper.scrollWidth > wrapper.clientWidth + 8;
            wrapper.classList.toggle('has-overflow', hasOverflow);
        };

        update();
        window.addEventListener('resize', update, { passive: true });
    });
}

function initDashboardRouteToggle() {
    const button = document.querySelector('[data-route-toggle]');
    const viewport = document.querySelector('[data-route-viewport]');

    if (!button || !viewport) {
        return;
    }

    button.addEventListener('click', () => {
        const collapsed = viewport.classList.toggle('is-collapsed');
        button.textContent = collapsed
            ? button.dataset.labelExpand || 'Ver ruta completa'
            : button.dataset.labelCollapse || 'Ver menos';
    });
}

/* ═══════════════════════════════════════════════════
   EFECTOS VISUALES ADICIONALES
   ═══════════════════════════════════════════════════ */

function initHeroParticles() {
    const container = document.querySelector('.hero-particles');
    if (!container) return;

    const types = ['dot', 'ring', 'cross'];
    const colors = [
        'rgba(51, 196, 129, 0.5)',
        'rgba(59, 130, 246, 0.4)',
        'rgba(250, 204, 21, 0.4)',
        'rgba(249, 115, 22, 0.35)',
        'rgba(139, 92, 246, 0.35)',
    ];

    const count = 22;
    for (let i = 0; i < count; i++) {
        const el = document.createElement('div');
        const type = types[i % types.length];
        el.className = `hero-particle hero-particle--${type}`;

        const size = type === 'dot' ? (3 + Math.random() * 5) : (10 + Math.random() * 14);
        el.style.setProperty('--size', size + 'px');
        el.style.setProperty('--clr', colors[Math.floor(Math.random() * colors.length)]);
        el.style.setProperty('--dur', (6 + Math.random() * 8) + 's');
        el.style.setProperty('--delay', (Math.random() * 6) + 's');
        el.style.setProperty('--dx', (Math.random() * 60 - 30) + 'px');
        el.style.setProperty('--dy', (-40 - Math.random() * 80) + 'px');
        el.style.setProperty('--peak-opacity', (0.3 + Math.random() * 0.5).toFixed(2));
        el.style.left = (Math.random() * 100) + '%';
        el.style.top = (Math.random() * 100) + '%';

        container.appendChild(el);
    }
}

function initHeroParallax() {
    const hero = document.querySelector('.hero--enhanced');
    if (!hero || window.matchMedia('(pointer: coarse)').matches) return;

    const copy = hero.querySelector('.hero__copy');
    const stage = hero.querySelector('.hero-stage');
    const orbits = hero.querySelectorAll('.hero-orbit');

    hero.addEventListener('mousemove', (e) => {
        const rect = hero.getBoundingClientRect();
        const px = (e.clientX - rect.left) / rect.width - 0.5;
        const py = (e.clientY - rect.top) / rect.height - 0.5;

        if (copy) {
            copy.style.transform = `translateX(${px * -6}px) translateY(${py * -6}px)`;
        }
        if (stage) {
            stage.style.transform = `translateX(${px * 10}px) translateY(${py * 8}px)`;
        }

        orbits.forEach((orb, i) => {
            const factor = (i + 1) * 8;
            orb.style.transform = `translateX(${px * factor}px) translateY(${py * factor}px)`;
        });
    });

    hero.addEventListener('mouseleave', () => {
        [copy, stage].forEach((el) => {
            if (el) el.style.transform = '';
        });
        orbits.forEach((orb) => {
            orb.style.transform = '';
        });
    });
}

function initCounterAnimation() {
    const pills = document.querySelectorAll('.metric-pill strong');
    if (!pills.length) return;

    const animateValue = (el) => {
        const text = el.textContent.trim();
        const match = text.match(/^(\d+)(.*)$/);
        if (!match) return;

        const target = parseInt(match[1], 10);
        const suffix = match[2];
        const duration = 1400;
        const start = performance.now();
        el.textContent = '0' + suffix;

        const tick = (now) => {
            const elapsed = now - start;
            const progress = Math.min(elapsed / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = Math.round(target * eased);
            el.textContent = current + suffix;
            if (progress < 1) requestAnimationFrame(tick);
        };

        requestAnimationFrame(tick);
    };

    if (window.IntersectionObserver) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    animateValue(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        pills.forEach((pill) => observer.observe(pill));
    } else {
        pills.forEach(animateValue);
    }
}

function initMagneticButtons() {
    if (window.matchMedia('(pointer: coarse)').matches) return;

    document.querySelectorAll('.hero__actions .button').forEach((btn) => {
        btn.addEventListener('mousemove', (e) => {
            const rect = btn.getBoundingClientRect();
            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;
            btn.style.transform = `translateY(-2px) translateX(${x * 0.15}px) translateY(${y * 0.15}px)`;
        });

        btn.addEventListener('mouseleave', () => {
            btn.style.transform = '';
        });
    });
}

function initScrollHideIndicator() {
    const indicator = document.querySelector('.scroll-indicator');
    if (!indicator) return;

    let hidden = false;
    const onScroll = () => {
        if (!hidden && window.scrollY > 80) {
            hidden = true;
            indicator.style.transition = 'opacity 0.4s ease';
            indicator.style.opacity = '0';
            window.removeEventListener('scroll', onScroll);
        }
    };

    window.addEventListener('scroll', onScroll, { passive: true });
}