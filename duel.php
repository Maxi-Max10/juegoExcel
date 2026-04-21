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
$myInitial    = mb_strtoupper(mb_substr($myName, 0, 1));
$rivalInitial = mb_strtoupper(mb_substr($rivalName, 0, 1));
$csrfToken    = csrf_token();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Duelo VS · <?= APP_NAME ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap">
<link rel="stylesheet" href="assets/css/styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* ══════════════════════════════════════════════════════════
   DUEL ARENA — EPIC REDESIGN
══════════════════════════════════════════════════════════ */
:root {
  --d-bg:        #080b18;
  --d-card:      rgba(16,20,42,0.85);
  --d-border:    rgba(60,70,120,0.5);
  --d-blue:      #4f8ef7;
  --d-blue-glow: rgba(79,142,247,0.35);
  --d-red:       #f74f6e;
  --d-red-glow:  rgba(247,79,110,0.35);
  --d-gold:      #f5c842;
  --d-gold-glow: rgba(245,200,66,0.35);
  --d-green:     #3df5a0;
  --d-green-glow:rgba(61,245,160,0.35);
  --d-purple:    #a855f7;
  --d-text:      #e2e8f0;
  --d-muted:     #64748b;
}

*, *::before, *::after { box-sizing: border-box; }

body {
  background: var(--d-bg);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0;
  margin: 0;
  font-family: 'Inter', sans-serif;
  color: var(--d-text);
  overflow-x: hidden;
}

/* ── Animated Background ─────────────────────────────── */
.duel-bg {
  position: fixed;
  inset: 0;
  z-index: 0;
  overflow: hidden;
  pointer-events: none;
}
.duel-bg::before {
  content: '';
  position: absolute;
  inset: 0;
  background:
    radial-gradient(ellipse 80% 50% at 20% 40%, rgba(79,142,247,0.07) 0%, transparent 60%),
    radial-gradient(ellipse 60% 40% at 80% 60%, rgba(168,85,247,0.06) 0%, transparent 60%),
    radial-gradient(ellipse 50% 60% at 50% 10%,  rgba(247,79,110,0.04) 0%, transparent 60%);
}
.duel-bg-grid {
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(rgba(79,142,247,0.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(79,142,247,0.04) 1px, transparent 1px);
  background-size: 40px 40px;
  animation: gridMove 20s linear infinite;
}
@keyframes gridMove {
  0%   { transform: translateY(0); }
  100% { transform: translateY(40px); }
}
.duel-orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.15;
  animation: orbFloat 12s ease-in-out infinite;
}
.duel-orb-1 {
  width: 400px; height: 400px;
  background: radial-gradient(circle, var(--d-blue) 0%, transparent 70%);
  top: -100px; left: -100px;
  animation-duration: 14s;
}
.duel-orb-2 {
  width: 300px; height: 300px;
  background: radial-gradient(circle, var(--d-purple) 0%, transparent 70%);
  top: 30%; right: -80px;
  animation-duration: 10s;
  animation-delay: -5s;
}
.duel-orb-3 {
  width: 250px; height: 250px;
  background: radial-gradient(circle, var(--d-red) 0%, transparent 70%);
  bottom: 10%; left: 20%;
  animation-duration: 16s;
  animation-delay: -8s;
}
@keyframes orbFloat {
  0%, 100% { transform: translate(0, 0) scale(1); }
  33%       { transform: translate(30px, -20px) scale(1.05); }
  66%       { transform: translate(-20px, 15px) scale(0.95); }
}

/* ── Layout Shell ──────────────────────────────────────── */
.duel-shell {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 820px;
  padding: 0 1rem;
  display: flex;
  flex-direction: column;
  align-items: center;
}

/* ── Header ────────────────────────────────────────────── */
.duel-header {
  width: 100%;
  padding: 1.2rem 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.duel-header__back {
  display: inline-flex;
  align-items: center;
  gap: .45rem;
  color: var(--d-muted);
  text-decoration: none;
  font-size: .83rem;
  font-weight: 600;
  padding: .45rem .9rem;
  border-radius: 999px;
  border: 1px solid rgba(100,116,139,0.25);
  background: rgba(255,255,255,0.03);
  backdrop-filter: blur(8px);
  transition: color .2s, border-color .2s, background .2s;
}
.duel-header__back:hover {
  color: var(--d-text);
  border-color: rgba(100,116,139,0.5);
  background: rgba(255,255,255,0.06);
}
.duel-header__id {
  font-size: .75rem;
  color: var(--d-muted);
  padding: .4rem .8rem;
  border-radius: 999px;
  background: rgba(255,255,255,0.03);
  border: 1px solid rgba(100,116,139,0.15);
  font-weight: 600;
  letter-spacing: .06em;
  text-transform: uppercase;
}

/* ── Scoreboard ────────────────────────────────────────── */
.duel-scoreboard {
  width: 100%;
  display: grid;
  grid-template-columns: 1fr auto 1fr;
  align-items: center;
  gap: .8rem;
  padding: .5rem 0 1.2rem;
}

.duel-player {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: .6rem;
  padding: 1.2rem 1rem;
  background: var(--d-card);
  border: 1px solid var(--d-border);
  border-radius: 20px;
  backdrop-filter: blur(16px);
  transition: border-color .4s, box-shadow .4s;
  position: relative;
  overflow: hidden;
}
.duel-player::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(255,255,255,0.02) 0%, transparent 60%);
  pointer-events: none;
}
.duel-player.is-me {
  border-color: rgba(79,142,247,0.6);
  box-shadow: 0 0 24px var(--d-blue-glow), inset 0 0 24px rgba(79,142,247,0.04);
}
.duel-player.is-rival {
  border-color: rgba(247,79,110,0.4);
  box-shadow: 0 0 16px rgba(247,79,110,0.15);
}
.duel-player.is-winning {
  animation: winnerPulse 2s ease-in-out infinite;
}
@keyframes winnerPulse {
  0%, 100% { box-shadow: 0 0 24px var(--d-gold-glow); }
  50%       { box-shadow: 0 0 40px var(--d-gold-glow), 0 0 60px rgba(245,200,66,0.15); }
}

.duel-avatar {
  width: 52px; height: 52px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.3rem;
  font-weight: 900;
  letter-spacing: 0;
  flex-shrink: 0;
  position: relative;
}
.duel-avatar.avatar-me {
  background: linear-gradient(135deg, #3b6fd4 0%, #7c3aed 100%);
  box-shadow: 0 0 16px rgba(79,142,247,0.5);
}
.duel-avatar.avatar-rival {
  background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%);
  box-shadow: 0 0 16px rgba(247,79,110,0.4);
}
.duel-avatar::after {
  content: '';
  position: absolute;
  inset: -2px;
  border-radius: 50%;
  border: 2px solid rgba(255,255,255,0.15);
}

.duel-player__name {
  font-size: .82rem;
  font-weight: 700;
  color: var(--d-text);
  max-width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  text-align: center;
}
.duel-player__tag {
  font-size: .65rem;
  color: var(--d-blue);
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .08em;
  background: rgba(79,142,247,0.12);
  padding: .15rem .5rem;
  border-radius: 999px;
}
.duel-player__score {
  font-size: 2.8rem;
  font-weight: 900;
  line-height: 1;
  background: linear-gradient(135deg, var(--d-gold) 0%, #ff9f43 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  text-shadow: none;
  filter: drop-shadow(0 0 8px var(--d-gold-glow));
  transition: transform .2s;
}
.duel-player__score.bump {
  animation: scoreBump .35s cubic-bezier(.36,.07,.19,.97);
}
@keyframes scoreBump {
  0%   { transform: scale(1); }
  40%  { transform: scale(1.4); }
  70%  { transform: scale(0.9); }
  100% { transform: scale(1); }
}

/* ── VS Divider ─────────────────────────────────────────── */
.duel-vs-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: .3rem;
  flex-shrink: 0;
}
.duel-vs-text {
  font-size: 1.6rem;
  font-weight: 900;
  letter-spacing: .08em;
  background: linear-gradient(180deg, #fff 0%, var(--d-muted) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  filter: drop-shadow(0 0 10px rgba(255,255,255,0.25));
  animation: vsPulse 3s ease-in-out infinite;
}
@keyframes vsPulse {
  0%, 100% { filter: drop-shadow(0 0 6px rgba(255,255,255,0.2)); }
  50%       { filter: drop-shadow(0 0 16px rgba(255,255,255,0.5)); }
}
.duel-vs-bolt {
  font-size: .95rem;
  color: var(--d-gold);
  animation: boltFlash 2s ease-in-out infinite;
}
@keyframes boltFlash {
  0%, 90%, 100% { opacity: 1; transform: scale(1); }
  95%            { opacity: .3; transform: scale(1.3); }
}

/* ── Round Pips ────────────────────────────────────────── */
.duel-progress {
  width: 100%;
  padding: 0 0 1.2rem;
  display: flex;
  flex-direction: column;
  gap: .5rem;
}
.duel-progress__label {
  font-size: .72rem;
  font-weight: 700;
  color: var(--d-muted);
  text-transform: uppercase;
  letter-spacing: .1em;
  text-align: center;
}
.duel-pips-row {
  display: flex;
  gap: .5rem;
}
.duel-round-pip {
  flex: 1;
  height: 10px;
  border-radius: 5px;
  background: rgba(42,47,74,0.6);
  border: 1px solid rgba(60,70,120,0.3);
  transition: background .4s, box-shadow .4s;
  position: relative;
  overflow: hidden;
}
.duel-round-pip::after {
  content: '';
  position: absolute;
  top: 0; left: -100%;
  width: 100%; height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
  transition: left .6s;
}
.duel-round-pip.win {
  background: linear-gradient(90deg, var(--d-green), #00d68f);
  box-shadow: 0 0 10px var(--d-green-glow);
  border-color: var(--d-green);
}
.duel-round-pip.win::after { left: 100%; }
.duel-round-pip.loss {
  background: linear-gradient(90deg, var(--d-red), #ff6b6b);
  box-shadow: 0 0 10px var(--d-red-glow);
  border-color: var(--d-red);
}
.duel-round-pip.tie {
  background: linear-gradient(90deg, #64748b, #94a3b8);
  border-color: #94a3b8;
}
.duel-round-pip.current {
  background: linear-gradient(90deg, var(--d-blue), #818cf8);
  box-shadow: 0 0 12px var(--d-blue-glow);
  border-color: var(--d-blue);
  animation: pipPulse 1.2s ease-in-out infinite;
}
@keyframes pipPulse {
  0%, 100% { opacity: 1; box-shadow: 0 0 8px var(--d-blue-glow); }
  50%       { opacity: .6; box-shadow: 0 0 20px var(--d-blue-glow); }
}

/* ── Question Card ─────────────────────────────────────── */
.duel-arena { width: 100%; padding-bottom: 2rem; }

.duel-question-card {
  background: var(--d-card);
  border: 1px solid var(--d-border);
  border-radius: 24px;
  padding: 1.6rem;
  backdrop-filter: blur(20px);
  box-shadow: 0 8px 32px rgba(0,0,0,0.4), inset 0 1px 0 rgba(255,255,255,0.05);
  transition: box-shadow .4s;
}
.duel-question-card.new-q {
  animation: cardSlideIn .45s cubic-bezier(.22,.61,.36,1);
}
@keyframes cardSlideIn {
  from { opacity: 0; transform: translateY(20px) scale(.97); }
  to   { opacity: 1; transform: translateY(0) scale(1); }
}

.duel-question-meta {
  display: flex;
  gap: .5rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
  align-items: center;
}
.duel-badge {
  padding: .25rem .7rem;
  border-radius: 999px;
  font-size: .7rem;
  font-weight: 800;
  letter-spacing: .06em;
  text-transform: uppercase;
}
.duel-badge--diff { background: rgba(100,116,139,0.2); color: #94a3b8; border: 1px solid rgba(100,116,139,0.2); }
.duel-badge--cat  { background: rgba(79,142,247,0.15); color: var(--d-blue); border: 1px solid rgba(79,142,247,0.25); }
.duel-badge--round {
  background: rgba(245,200,66,0.12);
  color: var(--d-gold);
  border: 1px solid rgba(245,200,66,0.25);
  margin-left: auto;
}

/* ── Timer ──────────────────────────────────────────────── */
.duel-timer-wrap {
  position: relative;
  margin-bottom: 1.4rem;
}
.duel-timer-bar {
  height: 8px;
  border-radius: 4px;
  background: rgba(42,47,74,0.8);
  overflow: visible;
  position: relative;
}
.duel-timer-fill {
  height: 100%;
  border-radius: 4px;
  background: linear-gradient(90deg, var(--d-gold), #ff9f43);
  transition: width 1s linear;
  transform-origin: left;
  position: relative;
  box-shadow: 0 0 10px var(--d-gold-glow);
}
.duel-timer-fill::after {
  content: '';
  position: absolute;
  right: -1px; top: 50%;
  width: 12px; height: 12px;
  border-radius: 50%;
  background: var(--d-gold);
  transform: translateY(-50%);
  box-shadow: 0 0 8px var(--d-gold);
  transition: background .3s, box-shadow .3s;
}
.duel-timer-fill.urgent {
  background: linear-gradient(90deg, var(--d-red), #ff6b6b);
  box-shadow: 0 0 12px var(--d-red-glow);
  animation: timerShake .15s ease-in-out infinite;
}
.duel-timer-fill.urgent::after {
  background: var(--d-red);
  box-shadow: 0 0 8px var(--d-red);
}
@keyframes timerShake {
  0%, 100% { transform-origin: left; transform: scaleY(1); }
  50%       { transform: scaleY(1.4); }
}
.duel-timer-num {
  position: absolute;
  right: 0; top: -1.5rem;
  font-size: .72rem;
  font-weight: 800;
  color: var(--d-gold);
  transition: color .3s;
  min-width: 2ch;
  text-align: right;
}
.duel-timer-num.urgent { color: var(--d-red); }

/* ── Question Text ──────────────────────────────────────── */
.duel-consigna {
  font-size: 1.08rem;
  line-height: 1.65;
  margin-bottom: 1.5rem;
  color: var(--d-text);
  font-weight: 500;
}

/* ── Answer Buttons ─────────────────────────────────────── */
.duel-answers {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: .75rem;
}
@media(max-width: 500px) { .duel-answers { grid-template-columns: 1fr; } }

.duel-answer-btn {
  position: relative;
  padding: .85rem 1rem .85rem 3rem;
  background: rgba(26,31,54,0.7);
  border: 1.5px solid rgba(60,70,120,0.5);
  border-radius: 14px;
  color: var(--d-text);
  font-family: 'Inter', sans-serif;
  font-size: .88rem;
  font-weight: 600;
  cursor: pointer;
  text-align: left;
  overflow: hidden;
  transition: border-color .2s, background .2s, box-shadow .2s, transform .15s;
  word-break: break-word;
  backdrop-filter: blur(8px);
}
.duel-answer-btn::before {
  content: attr(data-label);
  position: absolute;
  left: .85rem; top: 50%;
  transform: translateY(-50%);
  width: 24px; height: 24px;
  border-radius: 7px;
  background: rgba(60,70,120,0.5);
  border: 1px solid rgba(100,116,139,0.3);
  color: var(--d-muted);
  font-size: .7rem;
  font-weight: 900;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background .2s, color .2s, border-color .2s;
  line-height: 1;
  text-align: center;
}
/* Ripple */
.duel-answer-btn .ripple {
  position: absolute;
  border-radius: 50%;
  background: rgba(255,255,255,0.12);
  transform: scale(0);
  animation: rippleAnim .5s linear;
  pointer-events: none;
}
@keyframes rippleAnim {
  to { transform: scale(4); opacity: 0; }
}
.duel-answer-btn:hover:not(:disabled) {
  border-color: rgba(79,142,247,0.7);
  background: rgba(30,42,74,0.85);
  box-shadow: 0 0 12px var(--d-blue-glow);
  transform: translateY(-1px);
}
.duel-answer-btn:hover:not(:disabled)::before {
  background: rgba(79,142,247,0.3);
  color: var(--d-blue);
  border-color: rgba(79,142,247,0.5);
}
.duel-answer-btn:active:not(:disabled) { transform: translateY(0) scale(.98); }
.duel-answer-btn.selected {
  border-color: var(--d-blue);
  box-shadow: 0 0 14px var(--d-blue-glow);
}
.duel-answer-btn.correct {
  border-color: var(--d-green);
  background: rgba(61,245,160,0.08);
  color: var(--d-green);
  box-shadow: 0 0 18px var(--d-green-glow);
  animation: correctPop .4s cubic-bezier(.36,.07,.19,.97);
}
.duel-answer-btn.correct::before {
  background: rgba(61,245,160,0.25);
  color: var(--d-green);
  border-color: var(--d-green);
}
@keyframes correctPop {
  0%  { transform: scale(1); }
  40% { transform: scale(1.04); }
  70% { transform: scale(0.98); }
  100%{ transform: scale(1); }
}
.duel-answer-btn.wrong {
  border-color: var(--d-red);
  background: rgba(247,79,110,0.08);
  color: var(--d-red);
  animation: wrongShake .4s cubic-bezier(.36,.07,.19,.97);
}
.duel-answer-btn.wrong::before {
  background: rgba(247,79,110,0.2);
  color: var(--d-red);
  border-color: var(--d-red);
}
@keyframes wrongShake {
  0%,100%{ transform: translateX(0); }
  20%    { transform: translateX(-5px); }
  40%    { transform: translateX(5px); }
  60%    { transform: translateX(-4px); }
  80%    { transform: translateX(4px); }
}
.duel-answer-btn:disabled { cursor: default; }

/* ── Loading / Waiting ──────────────────────────────────── */
.duel-waiting {
  text-align: center;
  padding: 3rem 1rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}
.duel-spinner {
  width: 48px; height: 48px;
  border-radius: 50%;
  border: 3px solid rgba(79,142,247,0.15);
  border-top-color: var(--d-blue);
  animation: spinnerAnim .9s linear infinite;
  box-shadow: 0 0 16px var(--d-blue-glow);
}
@keyframes spinnerAnim { to { transform: rotate(360deg); } }
.duel-waiting__text {
  color: var(--d-muted);
  font-size: .9rem;
  font-weight: 600;
}
.duel-waiting__dots::after {
  content: '';
  animation: dotAnim 1.5s steps(4, end) infinite;
}
@keyframes dotAnim {
  0%   { content: ''; }
  25%  { content: '.'; }
  50%  { content: '..'; }
  75%  { content: '...'; }
}

/* ── Wait State (after answering) ───────────────────────── */
.duel-wait-state {
  margin-top: 1rem;
  padding: .8rem 1.1rem;
  border-radius: 12px;
  background: rgba(30,37,64,0.7);
  border: 1px solid rgba(60,70,120,0.4);
  color: var(--d-muted);
  font-size: .85rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: .7rem;
  backdrop-filter: blur(8px);
}
.duel-wait-state i { color: var(--d-blue); animation: waitPulse 1.5s ease-in-out infinite; }
@keyframes waitPulse { 0%,100%{opacity:1} 50%{opacity:.3} }

/* ── Round History ──────────────────────────────────────── */
.duel-round-history {
  margin-top: 1.3rem;
  display: flex;
  flex-direction: column;
  gap: .45rem;
}
.duel-round-row {
  display: flex;
  align-items: center;
  gap: .7rem;
  padding: .55rem 1rem;
  border-radius: 12px;
  background: rgba(26,31,54,0.6);
  border: 1px solid rgba(60,70,120,0.25);
  font-size: .8rem;
  font-weight: 600;
  color: #94a3b8;
  animation: rowSlide .3s ease;
}
@keyframes rowSlide { from { opacity:0; transform: translateX(-10px); } to { opacity:1; transform: none; } }
.duel-round-row__dot {
  width: 8px; height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
}
.duel-round-row__dot.win  { background: var(--d-green); box-shadow: 0 0 6px var(--d-green); }
.duel-round-row__dot.loss { background: var(--d-red);   box-shadow: 0 0 6px var(--d-red); }
.duel-round-row__dot.tie  { background: var(--d-muted); }

/* ── Result Overlay ─────────────────────────────────────── */
.duel-result-screen {
  display: none;
  position: fixed;
  inset: 0;
  z-index: 1000;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  background: rgba(6,8,20,0.92);
  backdrop-filter: blur(20px);
}
.duel-result-screen.show { display: flex; }

.duel-result-card {
  background: rgba(16,20,42,0.9);
  border-radius: 28px;
  padding: 2.5rem 2rem;
  max-width: 420px;
  width: 100%;
  text-align: center;
  border: 1px solid var(--d-border);
  box-shadow: 0 24px 80px rgba(0,0,0,0.6);
  animation: resultCardIn .6s cubic-bezier(.22,.61,.36,1);
  position: relative;
  overflow: hidden;
}
.duel-result-card::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(255,255,255,0.02) 0%, transparent 50%);
  pointer-events: none;
}
@keyframes resultCardIn {
  from { opacity: 0; transform: scale(.8) translateY(30px); }
  to   { opacity: 1; transform: scale(1) translateY(0); }
}

.duel-result-glow {
  position: absolute;
  top: -60px; left: 50%;
  transform: translateX(-50%);
  width: 200px; height: 200px;
  border-radius: 50%;
  filter: blur(60px);
  opacity: 0;
  transition: opacity .6s;
  pointer-events: none;
}
.duel-result-card.win-card  .duel-result-glow { background: var(--d-gold); opacity: 0.25; }
.duel-result-card.tie-card  .duel-result-glow { background: var(--d-blue); opacity: 0.2; }
.duel-result-card.loss-card .duel-result-glow { background: var(--d-red);  opacity: 0.15; }

.duel-result-icon-wrap {
  font-size: 4.5rem;
  line-height: 1;
  margin-bottom: 1rem;
  animation: iconBounce .7s .2s cubic-bezier(.36,.07,.19,.97) both;
}
@keyframes iconBounce {
  0%   { opacity:0; transform: scale(0) rotate(-20deg); }
  60%  { transform: scale(1.2) rotate(5deg); }
  80%  { transform: scale(.9) rotate(-2deg); }
  100% { opacity:1; transform: scale(1) rotate(0); }
}
.duel-result-title {
  font-size: 2rem;
  font-weight: 900;
  margin-bottom: .5rem;
  line-height: 1.15;
}
.duel-result-title.win  { background: linear-gradient(135deg, var(--d-gold), #ff9f43); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; filter:drop-shadow(0 0 12px var(--d-gold-glow)); }
.duel-result-title.tie  { background: linear-gradient(135deg, var(--d-blue), #818cf8); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
.duel-result-title.loss { background: linear-gradient(135deg, var(--d-red), #ff6b6b); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }

.duel-result-subtitle {
  font-size: .9rem;
  color: var(--d-muted);
  margin-bottom: 1.5rem;
}

.duel-result-stats {
  display: flex;
  justify-content: center;
  gap: 2rem;
  margin-bottom: 1.8rem;
  padding: 1.2rem;
  background: rgba(255,255,255,0.03);
  border-radius: 16px;
  border: 1px solid rgba(60,70,120,0.3);
}
.duel-stat {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: .3rem;
}
.duel-stat__val {
  font-size: 2rem;
  font-weight: 900;
  background: linear-gradient(135deg, var(--d-gold), #ff9f43);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
.duel-stat__label {
  font-size: .7rem;
  font-weight: 700;
  color: var(--d-muted);
  text-transform: uppercase;
  letter-spacing: .08em;
}
.duel-stat-divider {
  width: 1px;
  background: rgba(60,70,120,0.5);
  align-self: stretch;
}

.duel-result-back {
  display: inline-flex;
  align-items: center;
  gap: .5rem;
  padding: .9rem 2rem;
  background: linear-gradient(135deg, var(--d-blue), #7c3aed);
  color: #fff;
  border: none;
  border-radius: 14px;
  font-family: 'Inter', sans-serif;
  font-size: .95rem;
  font-weight: 800;
  cursor: pointer;
  text-decoration: none;
  box-shadow: 0 4px 20px var(--d-blue-glow);
  transition: transform .2s, box-shadow .2s;
  letter-spacing: .02em;
}
.duel-result-back:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 28px var(--d-blue-glow);
}
.duel-result-back:active { transform: translateY(0); }

/* ── Particles (win only) ───────────────────────────────── */
.duel-particle {
  position: fixed;
  width: 8px; height: 8px;
  border-radius: 2px;
  pointer-events: none;
  z-index: 1001;
  animation: particleFall 1.5s ease-in forwards;
}
@keyframes particleFall {
  0%   { opacity: 1; transform: translateY(0) rotate(0deg) scale(1); }
  100% { opacity: 0; transform: translateY(300px) rotate(720deg) scale(.3); }
}
</style>
</head>
<body>

<!-- Animated Background -->
<div class="duel-bg">
  <div class="duel-bg-grid"></div>
  <div class="duel-orb duel-orb-1"></div>
  <div class="duel-orb duel-orb-2"></div>
  <div class="duel-orb duel-orb-3"></div>
</div>

<div class="duel-shell">

  <!-- Header -->
  <div class="duel-header">
    <a href="dashboard.php" class="duel-header__back">
      <i class="fa-solid fa-chevron-left"></i> Volver
    </a>
    <span class="duel-header__id">Duelo #<?= $duelId ?></span>
  </div>

  <!-- Scoreboard -->
  <div class="duel-scoreboard">
    <div class="duel-player is-me" id="player-me">
      <div class="duel-avatar avatar-me"><?= htmlspecialchars($myInitial) ?></div>
      <div class="duel-player__name"><?= htmlspecialchars($myName) ?></div>
      <div class="duel-player__tag">Tú</div>
      <div class="duel-player__score" id="score-me">0</div>
    </div>

    <div class="duel-vs-wrap">
      <i class="fa-solid fa-bolt duel-vs-bolt"></i>
      <span class="duel-vs-text">VS</span>
    </div>

    <div class="duel-player is-rival" id="player-rival">
      <div class="duel-avatar avatar-rival"><?= htmlspecialchars($rivalInitial) ?></div>
      <div class="duel-player__name"><?= htmlspecialchars($rivalName) ?></div>
      <div class="duel-player__tag" style="color:var(--d-red);background:rgba(247,79,110,0.1);">Rival</div>
      <div class="duel-player__score" id="score-rival">0</div>
    </div>
  </div>

  <!-- Round Progress -->
  <div class="duel-progress" id="round-pips">
    <div class="duel-progress__label">Rondas</div>
    <div class="duel-pips-row">
      <?php for ($i = 0; $i < 5; $i++): ?>
        <div class="duel-round-pip" id="pip-<?= $i ?>" title="Ronda <?= $i+1 ?>"></div>
      <?php endfor; ?>
    </div>
  </div>

  <!-- Arena -->
  <div class="duel-arena">
    <div class="duel-question-card" id="question-card">

      <!-- Loading -->
      <div class="duel-waiting" id="loading-msg">
        <div class="duel-spinner"></div>
        <div class="duel-waiting__text">Cargando duelo<span class="duel-waiting__dots"></span></div>
      </div>

      <!-- Question Area -->
      <div id="question-area" style="display:none;">
        <div class="duel-question-meta">
          <span class="duel-badge duel-badge--diff" id="q-difficulty"></span>
          <span class="duel-badge duel-badge--cat"  id="q-category"></span>
          <span class="duel-badge duel-badge--round">Ronda <span id="q-round">1</span>/5</span>
        </div>

        <div class="duel-timer-wrap">
          <div class="duel-timer-num" id="timer-num">20</div>
          <div class="duel-timer-bar">
            <div class="duel-timer-fill" id="timer-fill"></div>
          </div>
        </div>

        <div class="duel-consigna" id="q-consigna"></div>
        <div class="duel-answers" id="q-answers"></div>
        <div class="duel-round-history" id="round-history"></div>
      </div>

    </div>
  </div>

</div><!-- /duel-shell -->

<!-- Result Overlay -->
<div class="duel-result-screen" id="result-screen">
  <div class="duel-result-card" id="result-card">
    <div class="duel-result-glow"></div>
    <div class="duel-result-icon-wrap" id="result-icon">🏆</div>
    <div class="duel-result-title" id="result-title">¡Ganaste!</div>
    <div class="duel-result-subtitle" id="result-subtitle">¡Increíble duelo!</div>
    <div class="duel-result-stats">
      <div class="duel-stat">
        <div class="duel-stat__val" id="result-score-me">0</div>
        <div class="duel-stat__label">Tus puntos</div>
      </div>
      <div class="duel-stat-divider"></div>
      <div class="duel-stat">
        <div class="duel-stat__val" id="result-pts">0</div>
        <div class="duel-stat__label">XP ganados</div>
      </div>
      <div class="duel-stat-divider"></div>
      <div class="duel-stat">
        <div class="duel-stat__val" id="result-score-rival">0</div>
        <div class="duel-stat__label">Rival</div>
      </div>
    </div>
    <a href="dashboard.php" class="duel-result-back">
      <i class="fa-solid fa-house"></i> Volver al inicio
    </a>
  </div>
</div>

<script>
(function() {
  const DUEL_ID = <?= $duelId ?>;
  const CSRF    = <?= json_encode($csrfToken) ?>;
  const MY_ROLE = <?= json_encode($amChallenger ? 'challenger' : 'challenged') ?>;

  let lastQuestionIdx   = -1;
  let currentQuestionId = null;
  let answered          = false;
  let timerInterval     = null;
  let pollInterval      = null;
  let finished          = false;
  let prevScoreMe       = 0;
  let prevScoreRival    = 0;
  let timerTimeLeft     = 20;

  /* ── Utility ─────────────────────────────── */
  async function post(url, data) {
    const fd = new FormData();
    for (const [k, v] of Object.entries(data)) fd.append(k, v);
    const r = await fetch(url, { method: 'POST', body: fd });
    return r.json();
  }

  function addRipple(btn, e) {
    const rect = btn.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height) * 2;
    const x = (e.clientX - rect.left) - size / 2;
    const y = (e.clientY - rect.top)  - size / 2;
    const ripple = document.createElement('span');
    ripple.className = 'ripple';
    ripple.style.cssText = `width:${size}px;height:${size}px;left:${x}px;top:${y}px;`;
    btn.appendChild(ripple);
    ripple.addEventListener('animationend', () => ripple.remove());
  }

  function animateScore(elId, newVal) {
    const el = document.getElementById(elId);
    if (parseInt(el.textContent) === newVal) return;
    el.textContent = newVal;
    el.classList.remove('bump');
    void el.offsetWidth;
    el.classList.add('bump');
  }

  /* ── Poll ────────────────────────────────── */
  async function poll() {
    if (finished) return;
    let data;
    try {
      const r = await fetch(`api_duel_status.php?duel_id=${DUEL_ID}`);
      data = await r.json();
    } catch { return; }
    if (data.error) return;

    // Scores with bump animation
    animateScore('score-me',    data.my_score);
    animateScore('score-rival', data.rival_score);

    // Leader highlight
    const pMe    = document.getElementById('player-me');
    const pRival = document.getElementById('player-rival');
    pMe.classList.toggle('is-winning',    data.my_score > data.rival_score);
    pRival.classList.toggle('is-winning', data.rival_score > data.my_score);

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
      document.getElementById('loading-msg').style.display   = '';
      document.getElementById('question-area').style.display = 'none';
      return;
    }

    document.getElementById('loading-msg').style.display   = 'none';
    document.getElementById('question-area').style.display = '';

    if (q.order !== lastQuestionIdx) {
      lastQuestionIdx   = q.order;
      currentQuestionId = q.duel_question_id;
      answered          = false;
      renderQuestion(q, data.question_time_left);
    } else if (!answered && data.my_answered) {
      answered = true;
      lockButtons(null);
      showWaitingForRival();
    }

    updateTimer(data.question_time_left);

    if (answered && q.order === lastQuestionIdx) {
      showWaitState(data.question_time_left);
    }

    renderHistory(
      data.round_results,
      MY_ROLE === 'challenger' ? data.challenger_name : data.challenged_name,
      MY_ROLE === 'challenger' ? data.challenged_name : data.challenger_name
    );
  }

  /* ── Render Question ─────────────────────── */
  function renderQuestion(q, timeLeft) {
    document.getElementById('wait-state-msg')?.remove();
    document.getElementById('q-difficulty').textContent = q.dificultad;
    document.getElementById('q-category').textContent   = q.categoria;
    document.getElementById('q-round').textContent      = q.order + 1;
    document.getElementById('q-consigna').textContent   = q.consigna;

    const card = document.getElementById('question-card');
    card.classList.remove('new-q');
    void card.offsetWidth;
    card.classList.add('new-q');

    const labels    = ['A', 'B', 'C', 'D'];
    const container = document.getElementById('q-answers');
    container.innerHTML = '';
    q.answers.forEach((a, i) => {
      const btn = document.createElement('button');
      btn.className        = 'duel-answer-btn';
      btn.textContent      = a.text;
      btn.dataset.formula  = a.text;
      btn.dataset.correct  = a.correct ? '1' : '0';
      btn.dataset.label    = labels[i] || String(i + 1);
      btn.addEventListener('click', e => { addRipple(btn, e); onAnswer(btn, q); });
      container.appendChild(btn);
    });

    startTimer(timeLeft);
  }

  /* ── Answer Handler ──────────────────────── */
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
        btn.textContent = btn.textContent + (res.round_won ? '  ✓ ¡Punto!' : '  ✓ Correcto');
      } else {
        btn.classList.add('wrong');
        btn.textContent += '  ✗';
        document.querySelectorAll('.duel-answer-btn').forEach(b => {
          if (b.dataset.correct === '1') b.classList.add('correct');
        });
      }
    }).catch(() => {});
  }

  function lockButtons(selectedBtn) {
    document.querySelectorAll('.duel-answer-btn').forEach(b => {
      b.disabled = true;
      if (b !== selectedBtn) b.style.opacity = '0.45';
    });
  }

  function showWaitingForRival() { /* handled by showWaitState */ }

  function showWaitState(secondsLeft) {
    let el = document.getElementById('wait-state-msg');
    if (!el) {
      el = document.createElement('div');
      el.id        = 'wait-state-msg';
      el.className = 'duel-wait-state';
      document.getElementById('q-answers').after(el);
    }
    const msg = secondsLeft === 0
      ? 'Cargando siguiente pregunta…'
      : `Esperando al rival… ${secondsLeft}s`;
    el.innerHTML = `<i class="fa-solid fa-hourglass-half"></i><span>${msg}</span>`;
  }

  /* ── Timer ───────────────────────────────── */
  function startTimer(seconds) {
    clearInterval(timerInterval);
    timerTimeLeft = seconds;
    syncTimerUI();
    timerInterval = setInterval(() => {
      timerTimeLeft = Math.max(0, timerTimeLeft - 1);
      syncTimerUI();
      if (timerTimeLeft === 0) clearInterval(timerInterval);
    }, 1000);
  }

  function updateTimer(seconds) {
    timerTimeLeft = seconds;
    syncTimerUI();
  }

  function syncTimerUI() {
    const fill = document.getElementById('timer-fill');
    const num  = document.getElementById('timer-num');
    const pct  = (timerTimeLeft / 20 * 100).toFixed(1) + '%';
    fill.style.width = pct;
    fill.classList.toggle('urgent', timerTimeLeft <= 6);
    num.textContent  = timerTimeLeft;
    num.classList.toggle('urgent', timerTimeLeft <= 6);
  }

  /* ── Pips ────────────────────────────────── */
  function updatePips(roundResults, currentIdx, status) {
    roundResults.forEach(r => {
      const pip = document.getElementById('pip-' + r.order);
      if (!pip) return;
      pip.classList.remove('win', 'loss', 'tie', 'current');
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

  /* ── History ─────────────────────────────── */
  function renderHistory(roundResults, myName, rivalName) {
    const container = document.getElementById('round-history');
    container.innerHTML = '';
    roundResults.forEach(r => {
      if (r.winner_id === null && r.order >= (parseInt(document.getElementById('q-round').textContent) - 1)) return;
      const row  = document.createElement('div');
      row.className = 'duel-round-row';
      let dotClass, label;
      if (r.winner_id === null) {
        dotClass = 'tie';
        label    = `Ronda ${r.order + 1} — Sin respuesta (${r.categoria})`;
      } else if (r.my_win) {
        dotClass = 'win';
        label    = `Ronda ${r.order + 1} — Ganaste (${r.categoria})`;
      } else {
        dotClass = 'loss';
        label    = `Ronda ${r.order + 1} — Ganó ${rivalName} (${r.categoria})`;
      }
      row.innerHTML = `<span class="duel-round-row__dot ${dotClass}"></span><span>${label}</span>`;
      container.appendChild(row);
    });
  }

  /* ── Result ──────────────────────────────── */
  function spawnParticles() {
    const colors = ['#f5c842','#4f8ef7','#3df5a0','#f74f6e','#a855f7','#ff9f43'];
    for (let i = 0; i < 40; i++) {
      const p = document.createElement('div');
      p.className = 'duel-particle';
      p.style.left       = Math.random() * 100 + 'vw';
      p.style.top        = (Math.random() * 40) + 'vh';
      p.style.background = colors[Math.floor(Math.random() * colors.length)];
      p.style.width      = (4 + Math.random() * 8) + 'px';
      p.style.height     = p.style.width;
      p.style.animationDelay    = (Math.random() * .6) + 's';
      p.style.animationDuration = (1 + Math.random() * 1) + 's';
      document.body.appendChild(p);
      p.addEventListener('animationend', () => p.remove());
    }
  }

  function showResult(data) {
    clearInterval(timerInterval);
    const screen = document.getElementById('result-screen');
    const card   = document.getElementById('result-card');
    const icon   = document.getElementById('result-icon');
    const title  = document.getElementById('result-title');
    const sub    = document.getElementById('result-subtitle');
    const pts    = document.getElementById('result-pts');
    const sMe    = document.getElementById('result-score-me');
    const sRiv   = document.getElementById('result-score-rival');

    document.getElementById('question-area').style.display = 'none';

    if (data.result === 'win') {
      icon.textContent   = '🏆';
      title.textContent  = '¡Ganaste el duelo!';
      title.className    = 'duel-result-title win';
      sub.textContent    = '¡Dominaste la arena!';
      card.className     = 'duel-result-card win-card';
      setTimeout(spawnParticles, 400);
    } else if (data.result === 'tie') {
      icon.textContent   = '🤝';
      title.textContent  = '¡Empate perfecto!';
      title.className    = 'duel-result-title tie';
      sub.textContent    = '¡Estuvieron muy parejos!';
      card.className     = 'duel-result-card tie-card';
    } else {
      icon.textContent   = '😤';
      title.textContent  = '¡La próxima es tuya!';
      title.className    = 'duel-result-title loss';
      sub.textContent    = 'Sigue practicando, puedes ganar.';
      card.className     = 'duel-result-card loss-card';
    }

    pts.textContent  = '+' + data.points_earned;
    sMe.textContent  = data.my_score;
    sRiv.textContent = data.rival_score;

    setTimeout(() => screen.classList.add('show'), 700);
  }

  /* ── Bootstrap ───────────────────────────── */
  poll();
  pollInterval = setInterval(poll, 1500);
})();
</script>
</body>
</html>
