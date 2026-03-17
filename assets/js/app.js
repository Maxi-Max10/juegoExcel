document.addEventListener('DOMContentLoaded', () => {
    initAuthTabs();
    initLevelForm();
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