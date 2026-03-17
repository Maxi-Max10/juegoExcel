document.addEventListener('DOMContentLoaded', () => {
    document.documentElement.classList.add('js-ready');
    initAuthTabs();
    initLevelForm();
    initStudyChat();
    initMotion();
    initLevelCardTilt();
    initScrollHints();
    initDashboardRouteToggle();
});

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
                livesNode.textContent = String(payload.lives);
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

    for (let i = 0; i < 22; i += 1) {
        const piece = document.createElement('span');
        piece.className = 'confetti-piece';
        piece.style.setProperty('--x', `${Math.random() * 100}%`);
        piece.style.setProperty('--delay', `${Math.random() * 0.25}s`);
        piece.style.setProperty('--rotate', `${Math.random() * 360}deg`);
        burst.appendChild(piece);
    }

    window.setTimeout(() => {
        burst.remove();
    }, 1400);
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
            const hasOverflow = wrapper.scrollWidth > wrapper.clientWidth + 8;
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