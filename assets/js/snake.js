(function () {
    'use strict';

    const dataNode = document.getElementById('snake-level-data');
    if (!dataNode) return;

    const level = JSON.parse(dataNode.textContent);
    const canvas = document.getElementById('snake-canvas');
    const ctx = canvas.getContext('2d');
    const overlay = document.getElementById('snake-overlay');
    const overlayContent = document.getElementById('snake-overlay-content');
    const startBtn = document.getElementById('snake-start-btn');
    const feedbackBox = document.getElementById('snake-feedback');
    const nextActions = document.getElementById('snake-next-actions');
    const livesNode = document.getElementById('snake-lives');
    const pointsNode = document.getElementById('snake-points');
    const optionEls = document.querySelectorAll('.snake-option');

    const GRID = 20;
    const FOOD_COLORS = ['#3B82F6', '#FACC15', '#EF4444', '#A855F7'];
    const BG = '#0F172A';
    const GRID_LINE = 'rgba(148,163,184,0.07)';
    const SNAKE_BODY = '#22C55E';
    const SNAKE_HEAD = '#4ADE80';
    const WALL_COLOR = '#334155';

    let cellSize = 0;
    let snake = [];
    let dir = { x: 1, y: 0 };
    let nextDir = { x: 1, y: 0 };
    let foods = [];
    let loopId = null;
    let gameActive = false;
    let lives = level.lives;
    let points = level.points;
    let submitting = false;

    /* -------- init -------- */

    function init() {
        resize();
        window.addEventListener('resize', () => { resize(); draw(); });
        bindKeys();
        bindTouch();
        bindSwipe();
        startBtn.addEventListener('click', startGame);
        draw();
    }

    function resize() {
        const wrapper = document.getElementById('snake-board');
        const maxW = Math.min(wrapper.clientWidth, 560);
        const size = Math.floor(maxW / GRID) * GRID;
        canvas.width = size;
        canvas.height = size;
        cellSize = size / GRID;
    }

    /* -------- game flow -------- */

    function startGame() {
        overlay.classList.add('is-hidden');
        resetSnake();
        placeFood();
        gameActive = true;
        loop();
    }

    function resetSnake() {
        const mid = Math.floor(GRID / 2);
        snake = [
            { x: mid, y: mid },
            { x: mid - 1, y: mid },
            { x: mid - 2, y: mid },
        ];
        dir = { x: 1, y: 0 };
        nextDir = { x: 1, y: 0 };
    }

    function placeFood() {
        foods = [];
        const used = new Set();
        snake.forEach(s => used.add(key(s.x, s.y)));

        level.answers.forEach((ans, i) => {
            let x, y, tries = 0;
            do {
                x = 2 + Math.floor(Math.random() * (GRID - 4));
                y = 2 + Math.floor(Math.random() * (GRID - 4));
                tries++;
            } while (used.has(key(x, y)) && tries < 200);

            used.add(key(x, y));
            for (let dx = -1; dx <= 1; dx++) {
                for (let dy = -1; dy <= 1; dy++) {
                    used.add(key(x + dx, y + dy));
                }
            }

            foods.push({ x, y, index: i, correct: ans.correct, label: String(i + 1) });
        });
    }

    function loop() {
        if (!gameActive) return;
        update();
        draw();
        loopId = setTimeout(loop, level.speed);
    }

    function stopLoop() {
        gameActive = false;
        if (loopId) { clearTimeout(loopId); loopId = null; }
    }

    /* -------- update -------- */

    function update() {
        dir = { ...nextDir };

        const head = { x: snake[0].x + dir.x, y: snake[0].y + dir.y };

        // Wall collision → wrap around
        if (head.x < 0) head.x = GRID - 1;
        if (head.x >= GRID) head.x = 0;
        if (head.y < 0) head.y = GRID - 1;
        if (head.y >= GRID) head.y = 0;

        // Self collision
        if (snake.some(s => s.x === head.x && s.y === head.y)) {
            handleSelfHit();
            return;
        }

        snake.unshift(head);

        // Check food
        const eaten = foods.findIndex(f => f.x === head.x && f.y === head.y);
        if (eaten !== -1) {
            const food = foods[eaten];
            if (food.correct) {
                handleCorrect(food);
            } else {
                handleWrong(food);
            }
            return;
        }

        snake.pop(); // no food → don't grow
    }

    /* -------- collision handlers -------- */

    function handleSelfHit() {
        stopLoop();
        flashFeedback('error', 'Te mordiste a ti misma. Reintentando…');
        playTone('error');
        shakeCanvas();
        setTimeout(() => {
            clearFeedback();
            resetSnake();
            placeFood();
            gameActive = true;
            loop();
        }, 1200);
    }

    async function handleCorrect(food) {
        stopLoop();
        // grow snake visually — add extra segments
        for (let i = 0; i < 3; i++) snake.push({ ...snake[snake.length - 1] });
        draw();
        highlightOption(food.index, true);
        playTone('success');
        celebrate();
        flashFeedback('success', '¡Correcto! Nivel superado.');

        if (!submitting) {
            submitting = true;
            await submitAnswer(level.answers[food.index].text);
            submitting = false;
        }
        nextActions.style.display = '';
    }

    async function handleWrong(food) {
        stopLoop();
        highlightOption(food.index, false);
        playTone('error');
        shakeCanvas();
        flashFeedback('error', 'Respuesta incorrecta. Pierdes una vida.');

        if (!submitting) {
            submitting = true;
            await submitAnswer(level.answers[food.index].text);
            submitting = false;
        }

        if (lives <= 0) {
            flashFeedback('error', 'Sin vidas. Vuelve al mapa para recuperarte.');
            setTimeout(() => { window.location.href = 'dashboard.php'; }, 2000);
            return;
        }

        setTimeout(() => {
            clearFeedback();
            clearOptionHighlights();
            resetSnake();
            placeFood();
            gameActive = true;
            loop();
        }, 1500);
    }

    /* -------- server -------- */

    async function submitAnswer(formula) {
        const body = new FormData();
        body.append('csrf_token', level.csrfToken);
        body.append('level_id', String(level.levelId));
        body.append('formula', formula);

        try {
            const res = await fetch('submit_level.php', { method: 'POST', body });
            const data = await res.json();
            if (data.lives !== undefined) {
                lives = data.lives;
                livesNode.innerHTML = '<i class="fa-solid fa-heart"></i> ' + lives;
            }
            if (data.points !== undefined) {
                points = data.points;
                pointsNode.innerHTML = '<i class="fa-solid fa-star"></i> ' + points;
            }
        } catch (_) { /* silently continue */ }
    }

    /* -------- drawing -------- */

    function draw() {
        const w = canvas.width;
        const h = canvas.height;

        // Background
        ctx.fillStyle = BG;
        ctx.fillRect(0, 0, w, h);

        // Grid lines
        ctx.strokeStyle = GRID_LINE;
        ctx.lineWidth = 1;
        for (let i = 0; i <= GRID; i++) {
            const pos = i * cellSize;
            ctx.beginPath(); ctx.moveTo(pos, 0); ctx.lineTo(pos, h); ctx.stroke();
            ctx.beginPath(); ctx.moveTo(0, pos); ctx.lineTo(w, pos); ctx.stroke();
        }

        // Food items
        foods.forEach(f => {
            const cx = f.x * cellSize + cellSize / 2;
            const cy = f.y * cellSize + cellSize / 2;
            const r = cellSize * 0.42;

            ctx.fillStyle = FOOD_COLORS[f.index % FOOD_COLORS.length];
            ctx.shadowColor = FOOD_COLORS[f.index % FOOD_COLORS.length];
            ctx.shadowBlur = 10;
            ctx.beginPath();
            ctx.arc(cx, cy, r, 0, Math.PI * 2);
            ctx.fill();
            ctx.shadowBlur = 0;

            // Number label
            ctx.fillStyle = '#FFF';
            ctx.font = `bold ${Math.round(cellSize * 0.55)}px Manrope, sans-serif`;
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(f.label, cx, cy + 1);
        });

        // Snake
        snake.forEach((seg, i) => {
            const x = seg.x * cellSize;
            const y = seg.y * cellSize;
            const pad = 1;

            ctx.fillStyle = i === 0 ? SNAKE_HEAD : SNAKE_BODY;
            const radius = cellSize * 0.15;
            roundRect(ctx, x + pad, y + pad, cellSize - pad * 2, cellSize - pad * 2, radius);
            ctx.fill();

            // Eyes on head
            if (i === 0) {
                ctx.fillStyle = '#0F172A';
                const eyeR = cellSize * 0.08;
                let ex1, ey1, ex2, ey2;
                const cx = x + cellSize / 2;
                const cy = y + cellSize / 2;
                const off = cellSize * 0.18;
                if (dir.x === 1) { ex1 = cx + off; ey1 = cy - off; ex2 = cx + off; ey2 = cy + off; }
                else if (dir.x === -1) { ex1 = cx - off; ey1 = cy - off; ex2 = cx - off; ey2 = cy + off; }
                else if (dir.y === -1) { ex1 = cx - off; ey1 = cy - off; ex2 = cx + off; ey2 = cy - off; }
                else { ex1 = cx - off; ey1 = cy + off; ex2 = cx + off; ey2 = cy + off; }
                ctx.beginPath(); ctx.arc(ex1, ey1, eyeR, 0, Math.PI * 2); ctx.fill();
                ctx.beginPath(); ctx.arc(ex2, ey2, eyeR, 0, Math.PI * 2); ctx.fill();
            }
        });
    }

    function roundRect(c, x, y, w, h, r) {
        c.beginPath();
        c.moveTo(x + r, y);
        c.lineTo(x + w - r, y);
        c.quadraticCurveTo(x + w, y, x + w, y + r);
        c.lineTo(x + w, y + h - r);
        c.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
        c.lineTo(x + r, y + h);
        c.quadraticCurveTo(x, y + h, x, y + h - r);
        c.lineTo(x, y + r);
        c.quadraticCurveTo(x, y, x + r, y);
        c.closePath();
    }

    /* -------- controls -------- */

    function bindKeys() {
        document.addEventListener('keydown', e => {
            if (!gameActive) return;
            switch (e.key) {
                case 'ArrowUp':    case 'w': case 'W': if (dir.y !== 1)  nextDir = { x: 0, y: -1 }; e.preventDefault(); break;
                case 'ArrowDown':  case 's': case 'S': if (dir.y !== -1) nextDir = { x: 0, y: 1 };  e.preventDefault(); break;
                case 'ArrowLeft':  case 'a': case 'A': if (dir.x !== 1)  nextDir = { x: -1, y: 0 }; e.preventDefault(); break;
                case 'ArrowRight': case 'd': case 'D': if (dir.x !== -1) nextDir = { x: 1, y: 0 };  e.preventDefault(); break;
            }
        });
    }

    function bindTouch() {
        document.querySelectorAll('.snake-btn').forEach(btn => {
            const handler = e => {
                e.preventDefault();
                if (!gameActive) return;
                const d = btn.dataset.dir;
                if (d === 'up'    && dir.y !== 1)  nextDir = { x: 0, y: -1 };
                if (d === 'down'  && dir.y !== -1) nextDir = { x: 0, y: 1 };
                if (d === 'left'  && dir.x !== 1)  nextDir = { x: -1, y: 0 };
                if (d === 'right' && dir.x !== -1) nextDir = { x: 1, y: 0 };
            };
            btn.addEventListener('touchstart', handler, { passive: false });
            btn.addEventListener('click', handler);
        });
    }

    function bindSwipe() {
        let sx = 0, sy = 0;
        canvas.addEventListener('touchstart', e => {
            const t = e.touches[0];
            sx = t.clientX; sy = t.clientY;
        }, { passive: true });

        canvas.addEventListener('touchend', e => {
            if (!gameActive) return;
            const t = e.changedTouches[0];
            const dx = t.clientX - sx;
            const dy = t.clientY - sy;
            const absDx = Math.abs(dx);
            const absDy = Math.abs(dy);
            if (Math.max(absDx, absDy) < 20) return;

            if (absDx > absDy) {
                if (dx > 0 && dir.x !== -1) nextDir = { x: 1, y: 0 };
                else if (dx < 0 && dir.x !== 1) nextDir = { x: -1, y: 0 };
            } else {
                if (dy > 0 && dir.y !== -1) nextDir = { x: 0, y: 1 };
                else if (dy < 0 && dir.y !== 1) nextDir = { x: 0, y: -1 };
            }
        }, { passive: true });
    }

    /* -------- UI helpers -------- */

    function flashFeedback(type, msg) {
        feedbackBox.className = 'snake-feedback is-visible is-' + type;
        feedbackBox.textContent = msg;
    }

    function clearFeedback() {
        feedbackBox.className = 'snake-feedback';
        feedbackBox.textContent = '';
    }

    function highlightOption(idx, correct) {
        optionEls.forEach(el => el.classList.remove('is-correct', 'is-wrong'));
        const target = document.querySelector(`.snake-option[data-index="${idx}"]`);
        if (target) target.classList.add(correct ? 'is-correct' : 'is-wrong');
    }

    function clearOptionHighlights() {
        optionEls.forEach(el => el.classList.remove('is-correct', 'is-wrong'));
    }

    function shakeCanvas() {
        canvas.classList.add('shake');
        setTimeout(() => canvas.classList.remove('shake'), 500);
    }

    function playTone(type) {
        const AC = window.AudioContext || window.webkitAudioContext;
        if (!AC) return;
        const c = new AC();
        const o = c.createOscillator();
        const g = c.createGain();
        o.connect(g); g.connect(c.destination);
        o.type = type === 'success' ? 'triangle' : 'sawtooth';
        o.frequency.setValueAtTime(type === 'success' ? 660 : 180, c.currentTime);
        o.frequency.exponentialRampToValueAtTime(type === 'success' ? 990 : 110, c.currentTime + 0.22);
        g.gain.setValueAtTime(0.0001, c.currentTime);
        g.gain.exponentialRampToValueAtTime(0.22, c.currentTime + 0.02);
        g.gain.exponentialRampToValueAtTime(0.0001, c.currentTime + 0.3);
        o.start(c.currentTime); o.stop(c.currentTime + 0.31);
        o.addEventListener('ended', () => c.close().catch(() => {}));
    }

    function celebrate() {
        const burst = document.createElement('div');
        burst.className = 'confetti-burst';
        document.body.appendChild(burst);
        const colors = ['#fbbf24','#34d399','#60a5fa','#f472b6','#a78bfa','#fb923c','#e879f9','#facc15'];
        const count = 50;
        for (let i = 0; i < count; i++) {
            const p = document.createElement('span');
            p.className = 'confetti-piece';
            const isCircle = Math.random() > 0.65;
            const size = 6 + Math.random() * 10;
            p.style.setProperty('--x', Math.random() * 100 + '%');
            p.style.setProperty('--delay', Math.random() * 0.5 + 's');
            p.style.setProperty('--rotate', Math.random() * 360 + 'deg');
            p.style.setProperty('--clr', colors[Math.floor(Math.random() * colors.length)]);
            p.style.setProperty('--w', (isCircle ? size : size * 0.6) + 'px');
            p.style.setProperty('--h', (isCircle ? size : size * 1.4) + 'px');
            p.style.setProperty('--br', isCircle ? '50%' : (2 + Math.random() * 3) + 'px');
            p.style.setProperty('--dur', (1.8 + Math.random() * 1.2) + 's');
            p.style.setProperty('--fall', (70 + Math.random() * 30) + 'vh');
            p.style.setProperty('--spin', (500 + Math.random() * 400) + 'deg');
            p.style.setProperty('--drift', (-35 + Math.random() * 70) + 'px');
            p.style.setProperty('--sway', (0.8 + Math.random() * 1.2) + 's');
            burst.appendChild(p);
        }
        setTimeout(() => burst.remove(), 3500);
    }

    function key(x, y) { return x + ',' + y; }

    /* -------- boot -------- */
    init();
})();
