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
    const board = document.getElementById('snake-board');
    const scorePopup = document.getElementById('sg-score-popup');

    const GRID = 20;
    const FOOD_COLORS = ['#22C55E', '#FACC15', '#EF4444', '#A855F7'];
    const FOOD_GLOW   = ['rgba(34,197,94,', 'rgba(250,204,21,', 'rgba(239,68,68,', 'rgba(168,85,247,'];
    const BG = '#080e1c';
    const GRID_LINE = 'rgba(148,163,184,0.025)';
    const GRID_DOT = 'rgba(148,163,184,0.06)';
    const SNAKE_COLORS = {
        head: '#4ADE80',
        headDark: '#22C55E',
        body: '#22C55E',
        bodyDark: '#16A34A',
        tail: '#15803d',
        glow: 'rgba(74,222,128,0.4)',
    };

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
    let frameCount = 0;
    let trail = [];
    let particles = [];
    let bgStars = [];
    let time = 0;

    function resize() {
        const board = document.getElementById('snake-board');
        const size = Math.min(board.clientWidth, board.clientHeight) || 400;
        canvas.width = size;
        canvas.height = size;
        cellSize = size / GRID;
    }

    /* -------- init -------- */

    // -------- vidas/timer --------
    function showLifeTimer() {
        overlayContent.innerHTML = '<h2>Sin vidas</h2><div id="life-timer-msg"><i class="fa-solid fa-clock"></i> Consultando tiempo restante...</div><a class="button button--primary" href="dashboard.php">Volver al mapa</a>';
        fetchLifeStatus();
    }

    function fetchLifeStatus() {
        fetch('api_life_status.php')
            .then(res => res.json())
            .then(data => {
                if (data.lives > 0) {
                    window.location.reload();
                    return;
                }
                if (typeof data.nextLifeIn === 'number') {
                    startLifeCountdown(data.nextLifeIn);
                } else {
                    document.getElementById('life-timer-msg').innerHTML = '<i class="fa-solid fa-clock"></i> Esperando regeneración...';
                }
            })
            .catch(() => {
                document.getElementById('life-timer-msg').innerHTML = 'No se pudo consultar el servidor.';
            });
    }

    let timerInterval = null;
    function startLifeCountdown(seconds) {
        updateTimerMsg(seconds);
        if (timerInterval) clearInterval(timerInterval);
        timerInterval = setInterval(() => {
            seconds--;
            if (seconds <= 0) {
                clearInterval(timerInterval);
                fetchLifeStatus();
            } else {
                updateTimerMsg(seconds);
            }
        }, 1000);
    }

    function updateTimerMsg(secs) {
        const min = Math.floor(secs / 60);
        const s = secs % 60;
        document.getElementById('life-timer-msg').innerHTML = `<i class="fa-solid fa-clock"></i> Próxima vida en <strong>${min}:${s.toString().padStart(2, '0')}</strong>`;
    }

    // Generate background stars
    function generateStars() {
        bgStars = [];
        for (let i = 0; i < 40; i++) {
            bgStars.push({
                x: Math.random(),
                y: Math.random(),
                size: 0.5 + Math.random() * 1.5,
                speed: 0.2 + Math.random() * 0.5,
                phase: Math.random() * Math.PI * 2,
            });
        }
    }

    function init() {
        resize();
        generateStars();
        window.addEventListener('resize', () => { resize(); draw(); });
        bindKeys();
        bindTouch();
        bindSwipe();
        if (lives <= 0) {
            overlay.classList.remove('is-hidden');
            startBtn.style.display = 'none';
            showLifeTimer();
            return;
        }
        startBtn.addEventListener('click', startGame);
        draw();
    }

    function startGame() {
        overlay.classList.add('is-hidden');
        board.classList.add('sg-active');
        resetSnake();
        placeFood();
        gameActive = true;
        frameCount = 0;
        trail = [];
        particles = [];
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

        // Add trail from tail before moving
        const tail = snake[snake.length - 1];
        trail.push({ x: tail.x, y: tail.y, life: 10, maxLife: 10 });

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
        flashFeedback('error', '<i class="fa-solid fa-rotate"></i> Te mordiste. Reintentando…');
        playTone('error');
        shakeCanvas();
        // Spawn death particles
        spawnParticlesAt(snake[0].x, snake[0].y, '#ef4444', 15);
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
        spawnParticlesAt(food.x, food.y, '#4ade80', 25);
        showScorePopup(food, '+' + level.reward);
        canvas.classList.add('sg-glow-correct');
        setTimeout(() => canvas.classList.remove('sg-glow-correct'), 1200);
        flashFeedback('success', '<i class="fa-solid fa-check-circle"></i> ¡Correcto! Nivel superado.');
        pulseHudStat(pointsNode);

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
        canvas.classList.add('sg-flash-wrong');
        setTimeout(() => canvas.classList.remove('sg-flash-wrong'), 500);
        spawnParticlesAt(food.x, food.y, '#ef4444', 20);
        showScorePopup(food, '-1 ❤️');
        flashFeedback('error', '<i class="fa-solid fa-xmark"></i> Respuesta incorrecta. Pierdes una vida.');
        pulseHudStat(livesNode);

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

    /* -------- particles -------- */

    function spawnParticlesAt(gx, gy, color, count) {
        const cx = (gx + 0.5) / GRID;
        const cy = (gy + 0.5) / GRID;
        for (let i = 0; i < count; i++) {
            const angle = Math.random() * Math.PI * 2;
            const speed = 1 + Math.random() * 3;
            particles.push({
                x: cx, y: cy,
                vx: Math.cos(angle) * speed * 0.003,
                vy: Math.sin(angle) * speed * 0.003,
                life: 30 + Math.random() * 20,
                maxLife: 50,
                size: 2 + Math.random() * 4,
                color: color,
            });
        }
    }

    function updateParticles() {
        particles.forEach(p => {
            p.x += p.vx;
            p.y += p.vy;
            p.vx *= 0.96;
            p.vy *= 0.96;
            p.life--;
        });
        particles = particles.filter(p => p.life > 0);
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
        frameCount++;
        time += 0.016;

        updateParticles();

        // Background with subtle gradient
        const bgGrad = ctx.createRadialGradient(w / 2, h / 2, 0, w / 2, h / 2, w * 0.75);
        bgGrad.addColorStop(0, '#0c1424');
        bgGrad.addColorStop(1, BG);
        ctx.fillStyle = bgGrad;
        ctx.fillRect(0, 0, w, h);

        // Background stars
        bgStars.forEach(star => {
            const alpha = 0.15 + Math.sin(time * star.speed + star.phase) * 0.12;
            ctx.fillStyle = `rgba(148,163,184,${Math.max(0.03, alpha)})`;
            ctx.beginPath();
            ctx.arc(star.x * w, star.y * h, star.size, 0, Math.PI * 2);
            ctx.fill();
        });

        // Grid dots at intersections
        ctx.fillStyle = GRID_DOT;
        for (let gx = 1; gx < GRID; gx++) {
            for (let gy = 1; gy < GRID; gy++) {
                ctx.beginPath();
                ctx.arc(gx * cellSize, gy * cellSize, 0.8, 0, Math.PI * 2);
                ctx.fill();
            }
        }

        // Subtle grid lines
        ctx.strokeStyle = GRID_LINE;
        ctx.lineWidth = 0.5;
        for (let gx = 1; gx < GRID; gx++) {
            ctx.beginPath();
            ctx.moveTo(gx * cellSize, 0);
            ctx.lineTo(gx * cellSize, h);
            ctx.stroke();
        }
        for (let gy = 1; gy < GRID; gy++) {
            ctx.beginPath();
            ctx.moveTo(0, gy * cellSize);
            ctx.lineTo(w, gy * cellSize);
            ctx.stroke();
        }

        // Trail / ghost
        trail.forEach(t => {
            const alpha = (t.life / t.maxLife) * 0.2;
            const size = (t.life / t.maxLife) * 0.85 + 0.15;
            ctx.fillStyle = `rgba(34,197,94,${alpha})`;
            const pad = cellSize * (1 - size) / 2 + 1;
            const segW = cellSize * size - 2;
            roundRect(ctx, t.x * cellSize + pad, t.y * cellSize + pad, segW, segW, cellSize * 0.12);
            ctx.fill();
            t.life--;
        });
        trail = trail.filter(t => t.life > 0);

        // Food items with orbiting rings & glow
        foods.forEach(f => {
            const cx = f.x * cellSize + cellSize / 2;
            const cy = f.y * cellSize + cellSize / 2;
            const pulse = 1 + Math.sin(frameCount * 0.06 + f.index * 1.7) * 0.1;
            const r = cellSize * 0.4 * pulse;
            const colorIdx = f.index % FOOD_COLORS.length;
            const color = FOOD_COLORS[colorIdx];
            const glowBase = FOOD_GLOW[colorIdx];

            // Orbiting ring
            ctx.save();
            ctx.translate(cx, cy);
            ctx.rotate(time * 1.5 + f.index * 1.2);
            ctx.strokeStyle = glowBase + '0.15)';
            ctx.lineWidth = 1.5;
            ctx.beginPath();
            ctx.ellipse(0, 0, r * 1.6, r * 1.1, 0, 0, Math.PI * 2);
            ctx.stroke();
            ctx.restore();

            // Outer glow (larger, softer)
            const glowGrad = ctx.createRadialGradient(cx, cy, r * 0.3, cx, cy, r * 2.2);
            glowGrad.addColorStop(0, glowBase + '0.15)');
            glowGrad.addColorStop(0.5, glowBase + '0.05)');
            glowGrad.addColorStop(1, glowBase + '0)');
            ctx.fillStyle = glowGrad;
            ctx.beginPath();
            ctx.arc(cx, cy, r * 2.2, 0, Math.PI * 2);
            ctx.fill();

            // Main orb
            ctx.shadowColor = color;
            ctx.shadowBlur = 18;
            const orbGrad = ctx.createRadialGradient(cx - r * 0.3, cy - r * 0.3, 0, cx, cy, r);
            orbGrad.addColorStop(0, lightenColor(color, 30));
            orbGrad.addColorStop(0.7, color);
            orbGrad.addColorStop(1, darkenColor(color, 20));
            ctx.fillStyle = orbGrad;
            ctx.beginPath();
            ctx.arc(cx, cy, r, 0, Math.PI * 2);
            ctx.fill();
            ctx.shadowBlur = 0;

            // Inner highlight (specular)
            const specGrad = ctx.createRadialGradient(cx - r * 0.25, cy - r * 0.35, 0, cx, cy, r * 0.8);
            specGrad.addColorStop(0, 'rgba(255,255,255,0.45)');
            specGrad.addColorStop(0.4, 'rgba(255,255,255,0.1)');
            specGrad.addColorStop(1, 'rgba(255,255,255,0)');
            ctx.fillStyle = specGrad;
            ctx.beginPath();
            ctx.arc(cx, cy, r, 0, Math.PI * 2);
            ctx.fill();

            // Number label with shadow
            ctx.fillStyle = '#FFF';
            ctx.font = `bold ${Math.round(cellSize * 0.5)}px 'Syne', Manrope, sans-serif`;
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.shadowColor = 'rgba(0,0,0,0.4)';
            ctx.shadowBlur = 4;
            ctx.shadowOffsetY = 1;
            ctx.fillText(f.label, cx, cy + 1);
            ctx.shadowColor = 'transparent';
            ctx.shadowBlur = 0;
            ctx.shadowOffsetY = 0;
        });

        // Snake body — connected smooth segments
        drawSnake();

        // Particles
        particles.forEach(p => {
            const alpha = (p.life / p.maxLife);
            const px = p.x * w;
            const py = p.y * h;
            ctx.globalAlpha = alpha;
            ctx.fillStyle = p.color;
            ctx.shadowColor = p.color;
            ctx.shadowBlur = 6;
            ctx.beginPath();
            ctx.arc(px, py, p.size * alpha, 0, Math.PI * 2);
            ctx.fill();
            ctx.shadowBlur = 0;
        });
        ctx.globalAlpha = 1;

        // Border vignette
        const vGrad = ctx.createRadialGradient(w / 2, h / 2, w * 0.3, w / 2, h / 2, w * 0.72);
        vGrad.addColorStop(0, 'rgba(0,0,0,0)');
        vGrad.addColorStop(1, 'rgba(0,0,0,0.3)');
        ctx.fillStyle = vGrad;
        ctx.fillRect(0, 0, w, h);

        // Edge glow lines
        const edgeAlpha = 0.03 + Math.sin(time * 0.8) * 0.015;
        ctx.strokeStyle = `rgba(34,197,94,${edgeAlpha})`;
        ctx.lineWidth = 2;
        ctx.strokeRect(1, 1, w - 2, h - 2);
    }

    function drawSnake() {
        if (snake.length === 0) return;

        const len = snake.length;

        // Head glow on ground
        const hx = snake[0].x * cellSize + cellSize / 2;
        const hy = snake[0].y * cellSize + cellSize / 2;
        const glowRad = cellSize * 1.8;
        const headGlow = ctx.createRadialGradient(hx, hy, 0, hx, hy, glowRad);
        headGlow.addColorStop(0, 'rgba(74,222,128,0.08)');
        headGlow.addColorStop(1, 'rgba(74,222,128,0)');
        ctx.fillStyle = headGlow;
        ctx.beginPath();
        ctx.arc(hx, hy, glowRad, 0, Math.PI * 2);
        ctx.fill();

        // Draw segments from tail to head
        for (let i = len - 1; i >= 0; i--) {
            const seg = snake[i];
            const x = seg.x * cellSize;
            const y = seg.y * cellSize;
            const pad = 1;
            const segW = cellSize - pad * 2;

            // Color gradient along body
            const t = len > 1 ? i / (len - 1) : 0;
            const bodyT = 1 - t; // 0=head, 1=tail

            let fillColor;
            if (i === 0) {
                fillColor = SNAKE_COLORS.head;
            } else {
                const r = Math.round(lerp(34, 21, bodyT));
                const g = Math.round(lerp(197, 128, bodyT));
                const b = Math.round(lerp(94, 61, bodyT));
                fillColor = `rgb(${r},${g},${b})`;
            }

            // Segment glow for head
            if (i === 0) {
                ctx.shadowColor = SNAKE_COLORS.glow;
                ctx.shadowBlur = 14;
            }

            // Main segment with rounded gradient
            const segGrad = ctx.createLinearGradient(x, y, x + cellSize, y + cellSize);
            segGrad.addColorStop(0, fillColor);
            segGrad.addColorStop(1, darkenColor(fillColor, 15));
            ctx.fillStyle = segGrad;

            const radius = i === 0 ? cellSize * 0.25 : cellSize * 0.18;
            roundRect(ctx, x + pad, y + pad, segW, segW, radius);
            ctx.fill();
            ctx.shadowBlur = 0;

            // Segment connector — fill gap between adjacent segments
            if (i < len - 1) {
                const next = snake[i + 1];
                const dx = seg.x - next.x;
                const dy = seg.y - next.y;
                // Only connect adjacent (not wrapped) segments
                if (Math.abs(dx) <= 1 && Math.abs(dy) <= 1) {
                    ctx.fillStyle = fillColor;
                    if (dx !== 0) {
                        const connX = Math.min(seg.x, next.x) * cellSize + cellSize - pad;
                        ctx.fillRect(connX, y + pad + 2, pad * 2 + 1, segW - 4);
                    }
                    if (dy !== 0) {
                        const connY = Math.min(seg.y, next.y) * cellSize + cellSize - pad;
                        ctx.fillRect(x + pad + 2, connY, segW - 4, pad * 2 + 1);
                    }
                }
            }

            // Head specular highlight
            if (i === 0) {
                const hGrad = ctx.createLinearGradient(x, y, x + cellSize * 0.6, y + cellSize * 0.6);
                hGrad.addColorStop(0, 'rgba(255,255,255,0.22)');
                hGrad.addColorStop(1, 'rgba(255,255,255,0)');
                ctx.fillStyle = hGrad;
                roundRect(ctx, x + pad, y + pad, segW, segW, radius);
                ctx.fill();
            }

            // Body segment shine
            if (i > 0 && i < 5) {
                const shine = 0.08 * (1 - i / 5);
                ctx.fillStyle = `rgba(255,255,255,${shine})`;
                roundRect(ctx, x + pad, y + pad, segW * 0.6, segW * 0.3, 2);
                ctx.fill();
            }
        }

        // Eyes on head
        drawEyes();
    }

    function drawEyes() {
        const head = snake[0];
        const x = head.x * cellSize;
        const y = head.y * cellSize;
        const cx = x + cellSize / 2;
        const cy = y + cellSize / 2;
        const off = cellSize * 0.2;
        const eyeR = cellSize * 0.1;
        let ex1, ey1, ex2, ey2;
        if (dir.x === 1) { ex1 = cx + off; ey1 = cy - off; ex2 = cx + off; ey2 = cy + off; }
        else if (dir.x === -1) { ex1 = cx - off; ey1 = cy - off; ex2 = cx - off; ey2 = cy + off; }
        else if (dir.y === -1) { ex1 = cx - off; ey1 = cy - off; ex2 = cx + off; ey2 = cy - off; }
        else { ex1 = cx - off; ey1 = cy + off; ex2 = cx + off; ey2 = cy + off; }

        // Eye whites with subtle glow
        ctx.shadowColor = 'rgba(255,255,255,0.3)';
        ctx.shadowBlur = 4;
        ctx.fillStyle = '#fff';
        ctx.beginPath(); ctx.arc(ex1, ey1, eyeR, 0, Math.PI * 2); ctx.fill();
        ctx.beginPath(); ctx.arc(ex2, ey2, eyeR, 0, Math.PI * 2); ctx.fill();
        ctx.shadowBlur = 0;

        // Pupils
        ctx.fillStyle = '#0a0f1e';
        const pupR = eyeR * 0.55;
        ctx.beginPath(); ctx.arc(ex1 + dir.x * 1.5, ey1 + dir.y * 1.5, pupR, 0, Math.PI * 2); ctx.fill();
        ctx.beginPath(); ctx.arc(ex2 + dir.x * 1.5, ey2 + dir.y * 1.5, pupR, 0, Math.PI * 2); ctx.fill();

        // Tiny pupil highlight
        ctx.fillStyle = 'rgba(255,255,255,0.7)';
        const hlR = pupR * 0.4;
        ctx.beginPath(); ctx.arc(ex1 + dir.x * 0.5 - 0.5, ey1 + dir.y * 0.5 - 0.5, hlR, 0, Math.PI * 2); ctx.fill();
        ctx.beginPath(); ctx.arc(ex2 + dir.x * 0.5 - 0.5, ey2 + dir.y * 0.5 - 0.5, hlR, 0, Math.PI * 2); ctx.fill();
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

    /* -------- color helpers -------- */

    function lerp(a, b, t) { return a + (b - a) * t; }

    function lightenColor(hex, pct) {
        const rgb = hexToRgb(hex);
        if (!rgb) return hex;
        return `rgb(${Math.min(255, rgb.r + pct)},${Math.min(255, rgb.g + pct)},${Math.min(255, rgb.b + pct)})`;
    }

    function darkenColor(color, pct) {
        const rgb = parseColor(color);
        if (!rgb) return color;
        return `rgb(${Math.max(0, rgb.r - pct)},${Math.max(0, rgb.g - pct)},${Math.max(0, rgb.b - pct)})`;
    }

    function hexToRgb(hex) {
        const m = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return m ? { r: parseInt(m[1], 16), g: parseInt(m[2], 16), b: parseInt(m[3], 16) } : null;
    }

    function parseColor(c) {
        if (c.startsWith('#')) return hexToRgb(c);
        const m = c.match(/rgb\((\d+),\s*(\d+),\s*(\d+)\)/);
        return m ? { r: +m[1], g: +m[2], b: +m[3] } : null;
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
        feedbackBox.innerHTML = msg;
    }

    function clearFeedback() {
        feedbackBox.className = 'snake-feedback';
        feedbackBox.innerHTML = '';
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

        if (type === 'success') {
            // Victory chord — two notes
            [660, 880].forEach((freq, i) => {
                const o = c.createOscillator();
                const g = c.createGain();
                o.connect(g); g.connect(c.destination);
                o.type = 'triangle';
                o.frequency.setValueAtTime(freq, c.currentTime + i * 0.12);
                o.frequency.exponentialRampToValueAtTime(freq * 1.2, c.currentTime + i * 0.12 + 0.15);
                g.gain.setValueAtTime(0.0001, c.currentTime);
                g.gain.exponentialRampToValueAtTime(0.18, c.currentTime + i * 0.12 + 0.02);
                g.gain.exponentialRampToValueAtTime(0.0001, c.currentTime + i * 0.12 + 0.35);
                o.start(c.currentTime + i * 0.12);
                o.stop(c.currentTime + i * 0.12 + 0.36);
                o.addEventListener('ended', () => { if (i === 1) c.close().catch(() => {}); });
            });
        } else {
            const o = c.createOscillator();
            const g = c.createGain();
            o.connect(g); g.connect(c.destination);
            o.type = 'sawtooth';
            o.frequency.setValueAtTime(200, c.currentTime);
            o.frequency.exponentialRampToValueAtTime(80, c.currentTime + 0.25);
            g.gain.setValueAtTime(0.0001, c.currentTime);
            g.gain.exponentialRampToValueAtTime(0.18, c.currentTime + 0.02);
            g.gain.exponentialRampToValueAtTime(0.0001, c.currentTime + 0.32);
            o.start(c.currentTime); o.stop(c.currentTime + 0.33);
            o.addEventListener('ended', () => c.close().catch(() => {}));
        }
    }

    function celebrate() {
        const burst = document.createElement('div');
        burst.className = 'confetti-burst';
        document.body.appendChild(burst);
        const colors = ['#fbbf24','#34d399','#60a5fa','#f472b6','#a78bfa','#fb923c','#e879f9','#facc15','#22d3ee'];
        const count = 65;
        for (let i = 0; i < count; i++) {
            const p = document.createElement('span');
            p.className = 'confetti-piece';
            const isCircle = Math.random() > 0.6;
            const size = 5 + Math.random() * 12;
            p.style.setProperty('--x', Math.random() * 100 + '%');
            p.style.setProperty('--delay', Math.random() * 0.6 + 's');
            p.style.setProperty('--rotate', Math.random() * 360 + 'deg');
            p.style.setProperty('--clr', colors[Math.floor(Math.random() * colors.length)]);
            p.style.setProperty('--w', (isCircle ? size : size * 0.5) + 'px');
            p.style.setProperty('--h', (isCircle ? size : size * 1.5) + 'px');
            p.style.setProperty('--br', isCircle ? '50%' : (2 + Math.random() * 3) + 'px');
            p.style.setProperty('--dur', (1.6 + Math.random() * 1.4) + 's');
            p.style.setProperty('--fall', (65 + Math.random() * 35) + 'vh');
            p.style.setProperty('--spin', (500 + Math.random() * 500) + 'deg');
            p.style.setProperty('--drift', (-40 + Math.random() * 80) + 'px');
            p.style.setProperty('--sway', (0.7 + Math.random() * 1.3) + 's');
            burst.appendChild(p);
        }
        setTimeout(() => burst.remove(), 4000);
    }

    function key(x, y) { return x + ',' + y; }

    /* -------- score popup -------- */
    function showScorePopup(food, text) {
        if (!scorePopup) return;
        const el = document.createElement('span');
        el.className = 'sg-score-fly';
        el.textContent = text;
        const pxX = (food.x / GRID) * 100;
        const pxY = (food.y / GRID) * 100;
        el.style.left = pxX + '%';
        el.style.top = pxY + '%';
        if (text.startsWith('-')) { el.style.color = '#f87171'; el.style.textShadow = '0 0 16px rgba(248,113,113,0.6)'; }
        scorePopup.appendChild(el);
        setTimeout(() => el.remove(), 1500);
    }

    function pulseHudStat(node) {
        if (!node) return;
        node.classList.remove('pulse');
        void node.offsetWidth;
        node.classList.add('pulse');
        setTimeout(() => node.classList.remove('pulse'), 600);
    }

    /* -------- ambient particles -------- */
    function spawnAmbientParticles() {
        const container = document.getElementById('sg-particles');
        if (!container) return;
        const colors = ['rgba(59,130,246,0.35)', 'rgba(34,197,94,0.25)', 'rgba(250,204,21,0.25)', 'rgba(168,85,247,0.25)', 'rgba(99,102,241,0.2)'];
        for (let i = 0; i < 25; i++) {
            const span = document.createElement('span');
            const size = 2 + Math.random() * 5;
            const dur = 12 + Math.random() * 20;
            const delay = Math.random() * 15;
            const left = Math.random() * 100;
            const bottom = -(Math.random() * 20);
            span.style.cssText = `width:${size}px;height:${size}px;left:${left}%;bottom:${bottom}%;background:${colors[i % colors.length]};animation-duration:${dur}s;animation-delay:${delay}s;`;
            container.appendChild(span);
        }
    }

    /* -------- boot -------- */
    spawnAmbientParticles();
    init();
})();
