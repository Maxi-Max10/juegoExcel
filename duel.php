<?php
declare(strict_types=1);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

$userId = (int) current_user_id();
$duelId = (int) ($_GET['id'] ?? 0);

if ($duelId === 0) {
    header('Location: dashboard.php');
    exit;
}

$duel = fetch_duel($duelId);
if (!$duel || ((int)$duel['challenger_id'] !== $userId && (int)$duel['challenged_id'] !== $userId)) {
    header('Location: dashboard.php');
    exit;
}

if (!in_array($duel['status'], ['active', 'finished'], true)) {
    header('Location: dashboard.php');
    exit;
}

$amChallenger = (int)$duel['challenger_id'] === $userId;
$myName       = $amChallenger ? $duel['challenger_name'] : $duel['challenged_name'];
$rivalName    = $amChallenger ? $duel['challenged_name'] : $duel['challenger_name'];
$csrfToken    = csrf_token();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Duelo VS · <?= APP_NAME ?></title>
<link rel="stylesheet" href="assets/css/styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* ─── DUEL ARENA ─────────────────────────────────────────── */
:root{--duel-bg:#0d0f1a;--duel-card:#161929;--duel-border:#2a2f4a;--duel-blue:#4f8ef7;--duel-red:#f74f6e;--duel-gold:#f5c842;--duel-green:#42f575;}
body{background:var(--duel-bg);min-height:100vh;display:flex;flex-direction:column;align-items:center;padding:0;margin:0;font-family:'Inter',sans-serif;color:#e2e8f0;}

.duel-header{width:100%;max-width:780px;padding:1.2rem 1rem;display:flex;align-items:center;justify-content:space-between;gap:.5rem;}
.duel-header a{color:#94a3b8;text-decoration:none;font-size:.85rem;}
.duel-header a:hover{color:#e2e8f0;}

.duel-scoreboard{width:100%;max-width:780px;display:flex;align-items:center;justify-content:center;gap:1rem;padding:.5rem 1rem 1.5rem;}
.duel-player{flex:1;text-align:center;padding:1rem;background:var(--duel-card);border:1px solid var(--duel-border);border-radius:16px;transition:border-color .3s;}
.duel-player.is-me{border-color:var(--duel-blue);}
.duel-player__name{font-size:.9rem;font-weight:600;margin-bottom:.4rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.duel-player__score{font-size:2.2rem;font-weight:800;color:var(--duel-gold);}
.duel-vs{font-size:1.4rem;font-weight:900;color:#475569;letter-spacing:.05em;}

.duel-progress{width:100%;max-width:780px;padding:0 1rem .8rem;display:flex;gap:.4rem;}
.duel-round-pip{flex:1;height:6px;border-radius:3px;background:var(--duel-border);transition:background .4s;}
.duel-round-pip.win{background:var(--duel-green);}
.duel-round-pip.loss{background:var(--duel-red);}
.duel-round-pip.tie{background:#64748b;}
.duel-round-pip.current{background:var(--duel-blue);animation:pipPulse 1s ease-in-out infinite;}
@keyframes pipPulse{0%,100%{opacity:1}50%{opacity:.4}}

.duel-arena{width:100%;max-width:780px;padding:0 1rem 1rem;}

.duel-question-card{background:var(--duel-card);border:1px solid var(--duel-border);border-radius:20px;padding:1.5rem;}
.duel-question-meta{display:flex;gap:.6rem;margin-bottom:1rem;flex-wrap:wrap;}
.duel-badge{padding:.25rem .65rem;border-radius:999px;font-size:.72rem;font-weight:700;background:#1e2540;color:#94a3b8;letter-spacing:.04em;}
.duel-badge--cat{background:#1a2a4a;color:var(--duel-blue);}

.duel-timer-bar{height:5px;border-radius:3px;background:var(--duel-border);margin-bottom:1.2rem;overflow:hidden;}
.duel-timer-fill{height:100%;border-radius:3px;background:var(--duel-gold);transition:width 1s linear;transform-origin:left;}
.duel-timer-fill.urgent{background:var(--duel-red);}

.duel-consigna{font-size:1.05rem;line-height:1.6;margin-bottom:1.4rem;color:#e2e8f0;}

.duel-answers{display:grid;grid-template-columns:1fr 1fr;gap:.7rem;}
@media(max-width:480px){.duel-answers{grid-template-columns:1fr;}}
.duel-answer-btn{padding:.8rem 1rem;background:#1a1f36;border:2px solid var(--duel-border);border-radius:12px;color:#e2e8f0;font-family:inherit;font-size:.9rem;font-weight:600;cursor:pointer;text-align:left;transition:border-color .2s,background .2s;word-break:break-all;}
.duel-answer-btn:hover:not(:disabled){border-color:var(--duel-blue);background:#1e2a4a;}
.duel-answer-btn.selected{border-color:var(--duel-blue);}
.duel-answer-btn.correct{border-color:var(--duel-green);background:#0f2a1a;color:var(--duel-green);}
.duel-answer-btn.wrong{border-color:var(--duel-red);background:#2a0f1a;color:var(--duel-red);}
.duel-answer-btn:disabled{cursor:default;opacity:.8;}

.duel-waiting{text-align:center;padding:2rem;color:#64748b;font-size:.95rem;}
.duel-waiting i{font-size:2rem;margin-bottom:.7rem;display:block;animation:spin 1.5s linear infinite;}
@keyframes spin{to{transform:rotate(360deg)}}

.duel-result-screen{display:none;position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:999;align-items:center;justify-content:center;flex-direction:column;gap:1.5rem;padding:2rem;text-align:center;}
.duel-result-screen.show{display:flex;}
.duel-result-icon{font-size:4rem;}
.duel-result-title{font-size:2.2rem;font-weight:900;}
.duel-result-title.win{color:var(--duel-gold);}
.duel-result-title.tie{color:var(--duel-blue);}
.duel-result-title.loss{color:var(--duel-red);}
.duel-result-pts{font-size:1.1rem;color:#94a3b8;}
.duel-result-pts strong{color:#e2e8f0;font-size:1.3rem;}
.duel-result-back{padding:.8rem 2rem;background:var(--duel-blue);color:#fff;border:none;border-radius:10px;font-size:1rem;font-weight:700;cursor:pointer;text-decoration:none;display:inline-block;margin-top:.5rem;}
.duel-result-back:hover{background:#3a7ef0;}

.duel-round-history{margin-top:1.2rem;display:flex;flex-direction:column;gap:.5rem;}
.duel-round-row{display:flex;align-items:center;gap:.6rem;padding:.5rem .8rem;border-radius:10px;background:#1a1f36;font-size:.82rem;}
.duel-round-row i{width:16px;text-align:center;}
</style>
</head>
<body>

<div class="duel-header">
  <a href="dashboard.php"><i class="fa-solid fa-arrow-left"></i> Volver</a>
  <span style="font-size:.8rem;color:#475569;">Duelo #<?= $duelId ?></span>
</div>

<div class="duel-scoreboard">
  <div class="duel-player is-me" id="player-me">
    <div class="duel-player__name"><?= htmlspecialchars($myName) ?> <span style="font-size:.7rem;color:#4f8ef7;">(tú)</span></div>
    <div class="duel-player__score" id="score-me">0</div>
  </div>
  <div class="duel-vs">VS</div>
  <div class="duel-player" id="player-rival">
    <div class="duel-player__name"><?= htmlspecialchars($rivalName) ?></div>
    <div class="duel-player__score" id="score-rival">0</div>
  </div>
</div>

<div class="duel-progress" id="round-pips">
  <?php for ($i = 0; $i < 5; $i++): ?>
    <div class="duel-round-pip" id="pip-<?= $i ?>"></div>
  <?php endfor; ?>
</div>

<div class="duel-arena">
  <div class="duel-question-card" id="question-card">
    <div class="duel-waiting" id="loading-msg">
      <i class="fa-solid fa-circle-notch"></i>
      Cargando duelo…
    </div>

    <div id="question-area" style="display:none;">
      <div class="duel-question-meta">
        <span class="duel-badge" id="q-difficulty"></span>
        <span class="duel-badge duel-badge--cat" id="q-category"></span>
        <span class="duel-badge">Ronda <span id="q-round">1</span>/5</span>
      </div>
      <div class="duel-timer-bar"><div class="duel-timer-fill" id="timer-fill"></div></div>
      <div class="duel-consigna" id="q-consigna"></div>
      <div class="duel-answers" id="q-answers"></div>
      <div class="duel-round-history" id="round-history"></div>
    </div>
  </div>
</div>

<!-- Result overlay -->
<div class="duel-result-screen" id="result-screen">
  <div class="duel-result-icon" id="result-icon">🏆</div>
  <div class="duel-result-title" id="result-title">¡Ganaste!</div>
  <div class="duel-result-pts">Puntos ganados: <strong id="result-pts">20</strong></div>
  <a href="dashboard.php" class="duel-result-back">Volver al Dashboard</a>
</div>

<script>
(function() {
  const DUEL_ID    = <?= $duelId ?>;
  const CSRF       = <?= json_encode($csrfToken) ?>;
  const MY_ROLE    = <?= json_encode($amChallenger ? 'challenger' : 'challenged') ?>;

  let lastQuestionIdx = -1;
  let currentQuestionId = null;
  let answered = false;
  let timerInterval = null;
  let pollInterval = null;
  let finished = false;

  async function post(url, data) {
    const fd = new FormData();
    for (const [k, v] of Object.entries(data)) fd.append(k, v);
    const r = await fetch(url, { method: 'POST', body: fd });
    return r.json();
  }

  async function poll() {
    if (finished) return;
    let data;
    try {
      const r = await fetch(`api_duel_status.php?duel_id=${DUEL_ID}`);
      data = await r.json();
    } catch { return; }

    if (data.error) return;

    // Update scores
    document.getElementById('score-me').textContent    = data.my_score;
    document.getElementById('score-rival').textContent = data.rival_score;

    // Update pips from round history
    updatePips(data.round_results, data.current_question_idx, data.status);

    if (data.status === 'finished') {
      clearInterval(pollInterval);
      finished = true;
      showResult(data);
      return;
    }

    if (data.status !== 'active') return;

    const q = data.question;
    if (!q) {
      // Waiting for question
      document.getElementById('loading-msg').style.display = '';
      document.getElementById('question-area').style.display = 'none';
      return;
    }

    document.getElementById('loading-msg').style.display = 'none';
    document.getElementById('question-area').style.display = '';

    // New question arrived
    if (q.order !== lastQuestionIdx) {
      lastQuestionIdx   = q.order;
      currentQuestionId = q.duel_question_id;
      answered          = false;
      renderQuestion(q, data.question_time_left);
    } else if (!answered && data.my_answered) {
      // We answered in a previous poll window — lock buttons
      answered = true;
      lockButtons(null);
      showWaitingForRival();
    }

    // Keep timer in sync
    updateTimer(data.question_time_left);

    // Round history
    renderHistory(data.round_results, MY_ROLE === 'challenger' ? data.challenger_name : data.challenged_name,
                  MY_ROLE === 'challenger' ? data.challenged_name : data.challenger_name);
  }

  function renderQuestion(q, timeLeft) {
    document.getElementById('q-difficulty').textContent = q.dificultad;
    document.getElementById('q-category').textContent   = q.categoria;
    document.getElementById('q-round').textContent      = q.order + 1;
    document.getElementById('q-consigna').textContent   = q.consigna;

    const container = document.getElementById('q-answers');
    container.innerHTML = '';
    q.answers.forEach(a => {
      const btn = document.createElement('button');
      btn.className = 'duel-answer-btn';
      btn.textContent = a.text;
      btn.dataset.formula = a.text;
      btn.dataset.correct = a.correct ? '1' : '0';
      btn.addEventListener('click', () => onAnswer(btn, q));
      container.appendChild(btn);
    });

    startTimer(timeLeft);
  }

  function onAnswer(btn, q) {
    if (answered) return;
    answered = true;
    lockButtons(btn);

    post('api_duel_answer.php', {
      csrf_token:  CSRF,
      duel_id:     DUEL_ID,
      question_id: currentQuestionId,
      formula:     btn.dataset.formula,
    }).then(res => {
      if (res.correct) {
        btn.classList.add('correct');
        if (res.round_won) {
          btn.textContent += '  ✓ ¡Punto!';
        } else {
          btn.textContent += '  ✓ Correcto (rival fue más rápido)';
        }
      } else {
        btn.classList.add('wrong');
        btn.textContent += '  ✗ Incorrecto';
        // Show correct
        document.querySelectorAll('.duel-answer-btn').forEach(b => {
          if (b.dataset.correct === '1') b.classList.add('correct');
        });
      }
    }).catch(() => {});
  }

  function lockButtons(selectedBtn) {
    document.querySelectorAll('.duel-answer-btn').forEach(b => {
      b.disabled = true;
      if (b !== selectedBtn) b.style.opacity = '0.5';
    });
  }

  function showWaitingForRival() {
    const container = document.getElementById('q-answers');
    const msg = document.createElement('p');
    msg.style.cssText = 'color:#64748b;font-size:.85rem;margin-top:.8rem;';
    msg.textContent = 'Esperando respuesta del rival…';
    container.appendChild(msg);
  }

  let timerTimeLeft = 20;
  function startTimer(seconds) {
    clearInterval(timerInterval);
    timerTimeLeft = seconds;
    const fill = document.getElementById('timer-fill');
    fill.style.width = (timerTimeLeft / 20 * 100) + '%';
    fill.classList.toggle('urgent', timerTimeLeft <= 6);

    timerInterval = setInterval(() => {
      timerTimeLeft = Math.max(0, timerTimeLeft - 1);
      fill.style.width = (timerTimeLeft / 20 * 100) + '%';
      fill.classList.toggle('urgent', timerTimeLeft <= 6);
      if (timerTimeLeft === 0) clearInterval(timerInterval);
    }, 1000);
  }

  function updateTimer(seconds) {
    if (Math.abs(timerTimeLeft - seconds) > 2) {
      timerTimeLeft = seconds;
      const fill = document.getElementById('timer-fill');
      fill.style.width = (seconds / 20 * 100) + '%';
      fill.classList.toggle('urgent', seconds <= 6);
    }
  }

  function updatePips(roundResults, currentIdx, status) {
    roundResults.forEach(r => {
      const pip = document.getElementById('pip-' + r.order);
      if (!pip) return;
      pip.classList.remove('win','loss','tie','current');
      if (r.winner_id !== null) {
        pip.classList.add(r.my_win ? 'win' : 'loss');
      } else if (status === 'active' && r.order < currentIdx) {
        pip.classList.add('tie');
      }
    });
    if (status === 'active' && currentIdx < 5) {
      const pip = document.getElementById('pip-' + currentIdx);
      if (pip && !pip.classList.contains('win') && !pip.classList.contains('loss')) {
        pip.classList.add('current');
      }
    }
  }

  function renderHistory(roundResults, myName, rivalName) {
    const container = document.getElementById('round-history');
    container.innerHTML = '';
    const completed = roundResults.filter(r => r.winner_id !== null || false);
    if (completed.length === 0) return;
    roundResults.forEach(r => {
      if (r.winner_id === null && r.order >= (document.getElementById('q-round').textContent - 1)) return;
      const row = document.createElement('div');
      row.className = 'duel-round-row';
      let icon, label;
      if (r.winner_id === null) {
        icon  = '<i class="fa-solid fa-minus" style="color:#64748b"></i>';
        label = `Ronda ${r.order+1} — Sin respuesta (${r.categoria})`;
      } else if (r.my_win) {
        icon  = '<i class="fa-solid fa-check" style="color:#42f575"></i>';
        label = `Ronda ${r.order+1} — Ganaste (${r.categoria})`;
      } else {
        icon  = '<i class="fa-solid fa-x" style="color:#f74f6e"></i>';
        label = `Ronda ${r.order+1} — Ganó ${rivalName} (${r.categoria})`;
      }
      row.innerHTML = icon + ' <span>' + label + '</span>';
      container.appendChild(row);
    });
  }

  function showResult(data) {
    clearInterval(timerInterval);
    const screen = document.getElementById('result-screen');
    const icon   = document.getElementById('result-icon');
    const title  = document.getElementById('result-title');
    const pts    = document.getElementById('result-pts');

    if (data.result === 'win') {
      icon.textContent = '🏆'; title.textContent = '¡Ganaste el duelo!'; title.className = 'duel-result-title win';
    } else if (data.result === 'tie') {
      icon.textContent = '🤝'; title.textContent = '¡Empate!'; title.className = 'duel-result-title tie';
    } else {
      icon.textContent = '😔'; title.textContent = 'Perdiste esta vez…'; title.className = 'duel-result-title loss';
    }
    pts.textContent = data.points_earned;

    document.getElementById('question-area').style.display = 'none';

    setTimeout(() => screen.classList.add('show'), 800);
  }

  // Start polling
  poll();
  pollInterval = setInterval(poll, 1500);
})();
</script>
</body>
</html>
