<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/layout.php';

require_login();

$userId = current_user_id();
$user = fetch_user_by_id((int) $userId);
$progress = get_user_progress((int) $userId);

// Check email verification status
$emailVerified = true;
$stmtVerif = getPDO()->prepare('SELECT email_verified FROM users WHERE id = ? LIMIT 1');
$stmtVerif->execute([$userId]);
$verifRow = $stmtVerif->fetch();
if ($verifRow && (int) $verifRow['email_verified'] === 0) {
    $emailVerified = false;
}

$levels = get_all_levels();
$statusMap = get_user_level_status_map((int) $userId);
$flash = get_flash();
$leaderboard = fetch_leaderboard(8);
$onlineUsers  = fetch_online_users((int) $userId, 20);
$onlineLbMap  = [];
foreach ($onlineUsers as $ou) $onlineLbMap[(int) $ou['id']] = true;
$currentLevel = max(1, min(TOTAL_LEVELS, (int) $progress['nivel_actual']));
$progressPercent = number_format(progress_percentage($progress), 2, '.', '');
$previewSize = 12;
$previewStart = max(1, $currentLevel - 2);
$previewEnd = min(TOTAL_LEVELS, $previewStart + $previewSize - 1);
$previewStart = max(1, $previewEnd - $previewSize + 1);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php render_head(APP_NAME . ' | Panel'); ?>
    <style>
        /* ── Dashboard Ambient Background ── */
        .dash-ambient{position:fixed;inset:0;pointer-events:none;z-index:0;overflow:hidden}
        .dash-ambient__orb{position:absolute;border-radius:50%;filter:blur(80px);opacity:.5;will-change:transform}
        .dash-ambient__orb--1{width:400px;height:400px;top:-10%;left:-5%;background:rgba(33,115,70,.3);animation:dash-orb1 12s ease-in-out infinite alternate}
        .dash-ambient__orb--2{width:350px;height:350px;top:30%;right:-8%;background:rgba(59,130,246,.25);animation:dash-orb2 15s ease-in-out infinite alternate}
        .dash-ambient__orb--3{width:300px;height:300px;bottom:-5%;left:30%;background:rgba(250,204,21,.15);animation:dash-orb3 10s ease-in-out infinite alternate}
        .dash-ambient__orb--4{width:250px;height:250px;top:60%;left:10%;background:rgba(239,68,68,.12);animation:dash-orb4 18s ease-in-out infinite alternate}
        @keyframes dash-orb1{0%{transform:translate(0,0) scale(1)}100%{transform:translate(60px,40px) scale(1.15)}}
        @keyframes dash-orb2{0%{transform:translate(0,0) scale(1)}100%{transform:translate(-50px,30px) scale(1.2)}}
        @keyframes dash-orb3{0%{transform:translate(0,0) scale(1)}100%{transform:translate(40px,-30px) scale(1.1)}}
        @keyframes dash-orb4{0%{transform:translate(0,0) scale(1)}100%{transform:translate(-30px,-50px) scale(1.15)}}

        /* ── Floating Particles (enhanced with shapes) ── */
        .dash-particle{position:fixed;border-radius:50%;pointer-events:none;z-index:0;animation:dashParticleFloat linear infinite}
        .dash-particle--ring{background:transparent !important;border:1.5px solid currentColor}
        .dash-particle--cross{background:transparent !important;border-radius:0 !important;width:2px !important;height:var(--size,8px);position:fixed}
        .dash-particle--cross::after{content:'';position:absolute;width:var(--size,8px);height:2px;background:currentColor;top:50%;left:50%;transform:translate(-50%,-50%)}
        @keyframes dashParticleFloat{0%{transform:translateY(100vh) rotate(0deg);opacity:0}8%{opacity:.6}50%{opacity:.8}92%{opacity:.6}100%{transform:translateY(-10vh) rotate(720deg);opacity:0}}

        /* ── Cursor glow follower ── */
        .dash-cursor-glow{position:fixed;width:320px;height:320px;border-radius:50%;pointer-events:none;z-index:0;background:radial-gradient(circle,rgba(51,196,129,.07),transparent 70%);transform:translate(-50%,-50%);transition:left .3s ease,top .3s ease;will-change:left,top}

        /* ── Hero Welcome Card (enhanced) ── */
        .dash-welcome{position:relative;display:grid;grid-template-columns:1fr auto;align-items:center;gap:32px;padding:36px 40px;margin-bottom:28px;border-radius:var(--radius-xl);background:linear-gradient(135deg,rgba(17,24,39,.96),rgba(15,23,42,.92));border:1px solid rgba(51,196,129,.2);overflow:hidden;box-shadow:0 24px 70px rgba(2,6,23,.45);animation:welcomeEntrance .8s cubic-bezier(.22,1,.36,1) both}
        @keyframes welcomeEntrance{0%{opacity:0;transform:translateY(30px) scale(.97)}100%{opacity:1;transform:translateY(0) scale(1)}}
        .dash-welcome::before{content:'';position:absolute;top:-40%;right:-15%;width:500px;height:500px;border-radius:50%;background:radial-gradient(circle,rgba(51,196,129,.1),transparent 70%);pointer-events:none;animation:welcome-glow 6s ease-in-out infinite alternate}
        .dash-welcome::after{content:'';position:absolute;inset:0 0 auto;height:2px;background:linear-gradient(90deg,transparent,var(--primary-strong),var(--secondary),transparent);animation:accent-shimmer 3s ease-in-out infinite}
        @keyframes welcome-glow{0%{opacity:.5;transform:scale(1)}100%{opacity:1;transform:scale(1.15)}}

        /* ── Shimmer sweep on welcome ── */
        .dash-welcome__shimmer{position:absolute;inset:0;pointer-events:none;overflow:hidden;z-index:2}
        .dash-welcome__shimmer::after{content:'';position:absolute;top:0;left:-100%;width:60%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.04),rgba(255,255,255,.08),rgba(255,255,255,.04),transparent);animation:shimmerSweep 4s ease-in-out infinite;transform:skewX(-15deg)}
        @keyframes shimmerSweep{0%,100%{left:-100%}50%{left:150%}}

        /* ── Orbiting rings around welcome ── */
        .dash-welcome__orbit{position:absolute;border-radius:50%;border:1px solid rgba(51,196,129,.08);pointer-events:none}
        .dash-welcome__orbit--1{width:300px;height:300px;top:-100px;right:-80px;animation:orbitSpin 20s linear infinite}
        .dash-welcome__orbit--2{width:440px;height:440px;top:-170px;right:-150px;animation:orbitSpin 30s linear infinite reverse}
        .dash-welcome__orbit--3{width:200px;height:200px;bottom:-60px;left:-60px;border-color:rgba(59,130,246,.06);animation:orbitSpin 15s linear infinite}
        @keyframes orbitSpin{0%{transform:rotate(0deg)}100%{transform:rotate(360deg)}}
        .dash-welcome__orbit::before{content:'';position:absolute;width:6px;height:6px;border-radius:50%;top:0;left:50%;transform:translateX(-50%)}
        .dash-welcome__orbit--1::before{background:#34D399;box-shadow:0 0 10px rgba(52,211,153,.6)}
        .dash-welcome__orbit--2::before{background:#60A5FA;box-shadow:0 0 10px rgba(96,165,250,.6)}
        .dash-welcome__orbit--3::before{background:#FBBF24;box-shadow:0 0 10px rgba(251,191,36,.6)}

        .dash-welcome__greeting{font-size:.77rem;font-weight:800;letter-spacing:.18em;text-transform:uppercase;color:var(--accent);display:inline-flex;align-items:center;gap:10px;margin-bottom:10px}
        .dash-welcome__greeting::before{content:'';width:32px;height:2px;border-radius:999px;background:linear-gradient(90deg,var(--primary-strong),var(--secondary));animation:greetingLineGrow .6s .3s cubic-bezier(.22,1,.36,1) both}
        @keyframes greetingLineGrow{0%{width:0;opacity:0}100%{width:32px;opacity:1}}
        .dash-welcome__title{font-family:var(--font-display);font-size:clamp(2rem,5vw,3.2rem);line-height:.95;letter-spacing:-.03em;margin:0 0 8px;background:linear-gradient(135deg,#fff 40%,rgba(51,196,129,.8));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;animation:titleReveal .7s .15s cubic-bezier(.22,1,.36,1) both}
        @keyframes titleReveal{0%{opacity:0;transform:translateY(15px);filter:blur(4px)}100%{opacity:1;transform:translateY(0);filter:blur(0)}}
        .dash-welcome__subtitle{color:var(--muted);font-size:1rem;margin:0 0 20px;max-width:40ch;animation:subtitleReveal .7s .3s cubic-bezier(.22,1,.36,1) both}
        @keyframes subtitleReveal{0%{opacity:0;transform:translateY(10px)}100%{opacity:1;transform:translateY(0)}}
        .dash-welcome__actions{display:flex;flex-wrap:wrap;gap:12px;animation:actionsReveal .7s .45s cubic-bezier(.22,1,.36,1) both}
        @keyframes actionsReveal{0%{opacity:0;transform:translateY(10px)}100%{opacity:1;transform:translateY(0)}}
        .dash-welcome__ring{position:relative;justify-self:center;animation:ringEntrance .9s .3s cubic-bezier(.22,1,.36,1) both}
        @keyframes ringEntrance{0%{opacity:0;transform:scale(.7) rotate(-10deg)}100%{opacity:1;transform:scale(1) rotate(0deg)}}

        /* ── Focus ring pulse effect ── */
        .dash-welcome__ring .focus-ring{animation:ringPulse 3s ease-in-out infinite}
        @keyframes ringPulse{0%,100%{box-shadow:inset 0 0 0 1px rgba(229,231,235,.08),0 0 30px rgba(51,196,129,.08)}50%{box-shadow:inset 0 0 0 1px rgba(229,231,235,.12),0 0 50px rgba(51,196,129,.18),0 0 80px rgba(51,196,129,.06)}}

        /* ── Stat Cards Grid (enhanced) ── */
        .dash-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-bottom:28px}
        .dash-stat{position:relative;display:flex;align-items:center;gap:16px;padding:22px 24px;border-radius:22px;background:linear-gradient(135deg,rgba(30,41,59,.92),rgba(15,23,42,.95));border:1px solid rgba(148,163,184,.1);box-shadow:0 4px 20px rgba(0,0,0,.2);overflow:hidden;transition:transform 320ms cubic-bezier(.22,1,.36,1),box-shadow 320ms ease,border-color 320ms ease}
        .dash-stat:hover{transform:translateY(-5px) scale(1.02);box-shadow:0 20px 40px rgba(0,0,0,.3)}
        .dash-stat::after{content:'';position:absolute;inset:0 0 auto;height:1px;background:linear-gradient(90deg,transparent,rgba(255,255,255,.15),transparent);pointer-events:none}

        /* ── Stat hover ripple ── */
        .dash-stat::before{content:'';position:absolute;width:150%;height:150%;border-radius:50%;left:var(--ripple-x,50%);top:var(--ripple-y,50%);transform:translate(-50%,-50%) scale(0);background:radial-gradient(circle,rgba(255,255,255,.06),transparent 70%);transition:transform .6s ease;pointer-events:none}
        .dash-stat:hover::before{transform:translate(-50%,-50%) scale(1)}

        .dash-stat__icon{display:grid;place-items:center;width:50px;height:50px;border-radius:16px;font-size:1.2rem;flex-shrink:0;transition:transform .4s cubic-bezier(.22,1,.36,1),box-shadow .3s ease}
        .dash-stat:hover .dash-stat__icon{transform:scale(1.15) rotate(5deg)}
        .dash-stat__body{display:flex;flex-direction:column;gap:3px;min-width:0}
        .dash-stat__label{font-size:.72rem;letter-spacing:.14em;text-transform:uppercase;color:var(--muted);transition:color .3s}
        .dash-stat:hover .dash-stat__label{color:var(--ink)}
        .dash-stat__value{font-size:1.6rem;line-height:1;font-family:var(--font-display);font-weight:800}
        .dash-stat__value small{font-size:.65em;color:var(--muted);font-weight:400}

        /* ── Stat color variants with glow hover ── */
        .dash-stat--xp{border-left:3px solid #FBBF24}.dash-stat--xp .dash-stat__icon{background:rgba(250,204,21,.13);color:#FBBF24}
        .dash-stat--xp:hover{border-color:rgba(250,204,21,.5);box-shadow:0 20px 40px rgba(250,204,21,.1)}
        .dash-stat--xp:hover .dash-stat__icon{box-shadow:0 0 20px rgba(250,204,21,.25)}
        .dash-stat--levels{border-left:3px solid #60A5FA}.dash-stat--levels .dash-stat__icon{background:rgba(59,130,246,.13);color:#60A5FA}
        .dash-stat--levels:hover{border-color:rgba(96,165,250,.5);box-shadow:0 20px 40px rgba(59,130,246,.1)}
        .dash-stat--levels:hover .dash-stat__icon{box-shadow:0 0 20px rgba(59,130,246,.25)}
        .dash-stat--lives{border-left:3px solid #F87171}.dash-stat--lives .dash-stat__icon{background:rgba(239,68,68,.13);color:#F87171;animation:heartbeat 1.4s ease-in-out infinite}
        .dash-stat--lives:hover{border-color:rgba(248,113,113,.5);box-shadow:0 20px 40px rgba(239,68,68,.1)}
        .dash-stat--lives:hover .dash-stat__icon{box-shadow:0 0 20px rgba(239,68,68,.25)}
        .dash-stat--next{text-decoration:none;color:inherit;background:linear-gradient(135deg,rgba(33,115,70,.2),rgba(15,23,42,.95));border-color:rgba(51,196,129,.25);border-left:3px solid #34D399;cursor:pointer}
        .dash-stat--next:hover{border-color:rgba(51,196,129,.5);box-shadow:0 20px 40px rgba(51,196,129,.12)}
        .dash-stat--next .dash-stat__icon{background:rgba(51,196,129,.15);color:#34D399;animation:playBounce 2s ease-in-out infinite}
        .dash-stat--next:hover .dash-stat__icon{box-shadow:0 0 20px rgba(51,196,129,.25)}
        @keyframes playBounce{0%,100%{transform:scale(1)}50%{transform:scale(1.08)}}
        .dash-stat__arrow{margin-left:auto;color:var(--muted);font-size:.9rem;transition:transform 320ms cubic-bezier(.22,1,.36,1),color 220ms ease}
        .dash-stat--next:hover .dash-stat__arrow{transform:translateX(6px);color:#34D399}

        /* ── Stat stagger entrance ── */
        .dash-stat{animation:statSlideUp .6s cubic-bezier(.22,1,.36,1) both}
        .dash-stat:nth-child(1){animation-delay:.1s}
        .dash-stat:nth-child(2){animation-delay:.2s}
        .dash-stat:nth-child(3){animation-delay:.3s}
        .dash-stat:nth-child(4){animation-delay:.4s}
        @keyframes statSlideUp{0%{opacity:0;transform:translateY(25px) scale(.95)}100%{opacity:1;transform:translateY(0) scale(1)}}

        /* ── Lives display in stat ── */
        .dash-stat .lives-bar{margin:4px 0 0}
        .dash-stat .lives-bar__heart{font-size:.85rem;transition:transform .2s ease}
        .dash-stat:hover .lives-bar__heart.is-full{animation:heartJump .5s ease forwards}
        .dash-stat:hover .lives-bar__heart.is-full:nth-child(2){animation-delay:.05s}
        .dash-stat:hover .lives-bar__heart.is-full:nth-child(3){animation-delay:.1s}
        .dash-stat:hover .lives-bar__heart.is-full:nth-child(4){animation-delay:.15s}
        .dash-stat:hover .lives-bar__heart.is-full:nth-child(5){animation-delay:.2s}
        @keyframes heartJump{0%{transform:scale(1)}40%{transform:scale(1.3) translateY(-3px)}100%{transform:scale(1)}}
        .dash-stat .lives-timer{position:absolute;right:16px;top:50%;transform:translateY(-50%);padding:6px 12px;border-radius:10px;font-size:.82rem;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);display:flex;align-items:center;gap:6px;color:#FBBF24;font-family:var(--font-display);font-weight:700}
        .dash-stat .lives-timer i{color:#F97316;animation:pulse-glow 2s ease-in-out infinite}

        /* ── XP Progress Track (enhanced) ── */
        .dash-xp-track{margin-bottom:32px;padding:20px 26px;border-radius:22px;background:linear-gradient(135deg,rgba(30,41,59,.6),rgba(15,23,42,.7));border:1px solid rgba(148,163,184,.08);box-shadow:0 4px 16px rgba(0,0,0,.15);position:relative;overflow:hidden;animation:fadeSlideUp .6s .5s cubic-bezier(.22,1,.36,1) both}
        @keyframes fadeSlideUp{0%{opacity:0;transform:translateY(20px)}100%{opacity:1;transform:translateY(0)}}
        .dash-xp-track::after{content:'';position:absolute;inset:0 0 auto;height:1px;background:linear-gradient(90deg,transparent,rgba(255,255,255,.12),transparent);pointer-events:none}
        .dash-xp-track__row{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
        .dash-xp-track__label{font-size:.82rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--muted);display:flex;align-items:center;gap:8px}
        .dash-xp-track__label i{color:#F97316;font-size:.9rem;animation:fireFlicker 1.5s ease-in-out infinite}
        @keyframes fireFlicker{0%,100%{transform:scale(1);opacity:1}25%{transform:scale(1.15) rotate(-5deg);opacity:.8}75%{transform:scale(1.1) rotate(5deg);opacity:.9}}
        .dash-xp-track__pct{font-size:1.15rem;font-weight:800;font-family:var(--font-display);background:linear-gradient(135deg,var(--primary-strong),var(--accent));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}

        /* ── Animated progress bar glow ── */
        .dash-xp-track .progress-bar__fill{position:relative}
        .dash-xp-track .progress-bar__fill::after{content:'';position:absolute;right:0;top:50%;transform:translateY(-50%);width:14px;height:14px;border-radius:50%;background:#34D399;box-shadow:0 0 12px rgba(52,211,153,.6),0 0 24px rgba(52,211,153,.3);animation:progressDot 2s ease-in-out infinite}
        @keyframes progressDot{0%,100%{opacity:1;box-shadow:0 0 12px rgba(52,211,153,.6),0 0 24px rgba(52,211,153,.3)}50%{opacity:.7;box-shadow:0 0 20px rgba(52,211,153,.8),0 0 40px rgba(52,211,153,.4)}}

        /* ── Dashboard Main Grid ── */
        .dash-main{display:grid;grid-template-columns:minmax(0,1.5fr) minmax(320px,.64fr);gap:22px;align-items:start}

        /* ── Levels Panel (enhanced) ── */
        .dash-levels{padding:30px;border-radius:var(--radius-xl);background:var(--paper);border:1px solid var(--line);backdrop-filter:blur(18px);box-shadow:var(--shadow-lg);position:relative;overflow:hidden;animation:fadeSlideUp .7s .6s cubic-bezier(.22,1,.36,1) both}
        .dash-levels::after{content:'';position:absolute;inset:0 0 auto;height:1px;background:linear-gradient(90deg,transparent,rgba(255,255,255,.3),transparent);pointer-events:none}
        .dash-levels__heading{display:flex;align-items:start;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px}
        .dash-levels__heading h2{margin:0;font-family:var(--font-display);letter-spacing:-.03em;font-size:1.3rem}
        .dash-levels__heading h2 i{background:linear-gradient(135deg,#34D399,#60A5FA);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
        .dash-levels__summary{margin:8px 0 0;color:var(--muted);max-width:54ch;font-size:.9rem}

        /* ── Side Panel ── */
        .dash-sidebar{display:grid;gap:22px}

        /* ── Leaderboard Card (enhanced) ── */
        .dash-leaderboard{padding:28px;border-radius:var(--radius-xl);background:var(--paper);border:1px solid var(--line);backdrop-filter:blur(18px);box-shadow:var(--shadow-lg);position:relative;overflow:hidden;animation:fadeSlideUp .7s .65s cubic-bezier(.22,1,.36,1) both}
        .dash-leaderboard::after{content:'';position:absolute;inset:0 0 auto;height:1px;background:linear-gradient(90deg,transparent,rgba(255,255,255,.3),transparent);pointer-events:none}
        .dash-leaderboard__head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px}
        .dash-leaderboard__head h2{margin:0;font-family:var(--font-display);letter-spacing:-.03em;font-size:1.2rem}
        .dash-leaderboard__head h2 i{color:#FBBF24;animation:crownBounce 2s ease-in-out infinite}
        @keyframes crownBounce{0%,100%{transform:translateY(0) rotate(0deg)}50%{transform:translateY(-4px) rotate(5deg)}}
        .dash-leaderboard__head a{color:var(--muted);font-size:.85rem;font-weight:600;transition:color .2s,transform .2s}
        .dash-leaderboard__head a:hover{color:var(--ink);transform:translateX(3px)}

        /* ── Leaderboard rows stagger ── */
        .dash-leaderboard .leaderboard-list li{animation:lbRowSlide .4s cubic-bezier(.22,1,.36,1) both}
        .dash-leaderboard .leaderboard-list li:nth-child(1){animation-delay:.7s}
        .dash-leaderboard .leaderboard-list li:nth-child(2){animation-delay:.78s}
        .dash-leaderboard .leaderboard-list li:nth-child(3){animation-delay:.86s}
        .dash-leaderboard .leaderboard-list li:nth-child(4){animation-delay:.94s}
        .dash-leaderboard .leaderboard-list li:nth-child(5){animation-delay:1.02s}
        .dash-leaderboard .leaderboard-list li:nth-child(6){animation-delay:1.1s}
        .dash-leaderboard .leaderboard-list li:nth-child(7){animation-delay:1.18s}
        .dash-leaderboard .leaderboard-list li:nth-child(8){animation-delay:1.26s}
        @keyframes lbRowSlide{0%{opacity:0;transform:translateX(-20px)}100%{opacity:1;transform:translateX(0)}}

        /* ── Tips Card (enhanced) ── */
        .dash-tips{padding:28px;border-radius:var(--radius-xl);background:linear-gradient(135deg,rgba(17,24,39,.92),rgba(15,23,42,.96));border:1px solid rgba(250,204,21,.12);backdrop-filter:blur(18px);box-shadow:var(--shadow-lg);position:relative;overflow:hidden;animation:fadeSlideUp .7s .8s cubic-bezier(.22,1,.36,1) both}
        .dash-tips::before{content:'';position:absolute;bottom:-40%;right:-20%;width:200px;height:200px;border-radius:50%;background:radial-gradient(circle,rgba(250,204,21,.08),transparent 70%);pointer-events:none;animation:tipGlow 5s ease-in-out infinite alternate}
        @keyframes tipGlow{0%{transform:scale(1);opacity:.5}100%{transform:scale(1.3);opacity:1}}
        .dash-tips::after{content:'';position:absolute;inset:0 0 auto;height:1px;background:linear-gradient(90deg,transparent,rgba(250,204,21,.25),transparent);pointer-events:none}
        .dash-tips h2{margin:0 0 16px;font-family:var(--font-display);letter-spacing:-.03em;font-size:1.15rem;color:#FBBF24}
        .dash-tips h2 i{animation:lightbulbGlow 3s ease-in-out infinite}
        @keyframes lightbulbGlow{0%,100%{filter:drop-shadow(0 0 2px rgba(251,191,36,.4));transform:rotate(0deg)}50%{filter:drop-shadow(0 0 8px rgba(251,191,36,.8));transform:rotate(8deg)}}
        .dash-tips ul{list-style:none;margin:0;padding:0;display:grid;gap:10px}
        .dash-tips li{display:flex;align-items:baseline;gap:10px;color:var(--muted);font-size:.9rem;line-height:1.5;animation:tipFadeIn .5s ease both}
        .dash-tips li:nth-child(1){animation-delay:.9s}
        .dash-tips li:nth-child(2){animation-delay:1s}
        .dash-tips li:nth-child(3){animation-delay:1.1s}
        @keyframes tipFadeIn{0%{opacity:0;transform:translateX(-10px)}100%{opacity:1;transform:translateX(0)}}
        .dash-tips li::before{content:'';width:5px;height:5px;border-radius:50%;background:#FBBF24;flex-shrink:0;margin-top:6px;animation:dotPulse 2s ease-in-out infinite}
        @keyframes dotPulse{0%,100%{box-shadow:0 0 0 0 rgba(251,191,36,.4)}50%{box-shadow:0 0 0 4px rgba(251,191,36,0)}}

        /* ── Achievement Badge (enhanced) ── */
        .dash-achievement{padding:22px 26px;border-radius:var(--radius-xl);background:linear-gradient(135deg,rgba(139,92,246,.12),rgba(15,23,42,.95));border:1px solid rgba(139,92,246,.2);position:relative;overflow:hidden;display:flex;align-items:center;gap:18px;animation:fadeSlideUp .7s .75s cubic-bezier(.22,1,.36,1) both;transition:transform .3s ease,box-shadow .3s ease,border-color .3s ease}
        .dash-achievement:hover{transform:translateY(-3px);box-shadow:0 16px 40px rgba(139,92,246,.15);border-color:rgba(139,92,246,.4)}
        .dash-achievement::after{content:'';position:absolute;inset:0 0 auto;height:1px;background:linear-gradient(90deg,transparent,rgba(139,92,246,.3),transparent);pointer-events:none}
        .dash-achievement::before{content:'';position:absolute;top:-50%;left:-50%;width:200%;height:200%;background:conic-gradient(from 0deg,transparent,rgba(139,92,246,.05),transparent,rgba(139,92,246,.05),transparent);animation:achievementRotate 8s linear infinite;pointer-events:none}
        @keyframes achievementRotate{0%{transform:rotate(0deg)}100%{transform:rotate(360deg)}}
        .dash-achievement__icon{width:48px;height:48px;display:grid;place-items:center;border-radius:14px;background:rgba(139,92,246,.15);color:#A78BFA;font-size:1.3rem;flex-shrink:0;animation:achieveIconFloat 3s ease-in-out infinite;position:relative;z-index:1}
        @keyframes achieveIconFloat{0%,100%{transform:translateY(0) scale(1)}50%{transform:translateY(-4px) scale(1.05)}}
        .dash-achievement__body{min-width:0;position:relative;z-index:1}
        .dash-achievement__title{font-weight:800;font-size:.95rem;margin:0 0 2px;background:linear-gradient(135deg,#fff,#A78BFA);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
        .dash-achievement__desc{color:var(--muted);font-size:.82rem;margin:0}

        /* ── Magnetic buttons on welcome ── */
        .dash-welcome__actions .button{transition:transform .3s cubic-bezier(.22,1,.36,1),box-shadow .3s ease}

        /* ── Animated gradient border on welcome hover ── */
        .dash-welcome:hover{border-color:rgba(51,196,129,.35)}
        .dash-welcome:hover .dash-welcome__title{animation:titleShine 2s linear infinite}
        @keyframes titleShine{0%{background-position:0% 50%}100%{background-position:200% 50%}}
        .dash-welcome:hover .dash-welcome__title{background-size:200% auto;background-image:linear-gradient(135deg,#fff 0%,rgba(51,196,129,.8) 25%,#fff 50%,rgba(51,196,129,.8) 75%,#fff 100%)}

        /* ── Responsiveness ── */
        @media(max-width:1240px){
            .dash-stats{grid-template-columns:repeat(2,minmax(0,1fr))}
            .dash-main{grid-template-columns:1fr}
        }
        @media(max-width:768px){
            .dash-welcome{grid-template-columns:1fr;text-align:center;padding:28px 22px}
            .dash-welcome__greeting{justify-content:center}
            .dash-welcome__subtitle{margin-left:auto;margin-right:auto}
            .dash-welcome__actions{justify-content:center}
            .dash-welcome__ring{margin-bottom:8px}
            .dash-welcome__orbit{display:none}
        }
        @media(max-width:640px){
            .dash-stats{grid-template-columns:repeat(2,minmax(0,1fr))}
            .dash-stat{padding:16px 18px}
            .dash-stat__icon{width:42px;height:42px}
            .dash-stat__value{font-size:1.35rem}
        }
        @media(prefers-reduced-motion:reduce){
            *,*::before,*::after{animation-duration:0s !important;transition-duration:0s !important}
        }

        /* ── Online indicator & challenge buttons ── */
        .lb-online-dot{display:inline-block;width:8px;height:8px;border-radius:50%;background:#22c55e;box-shadow:0 0 5px #22c55e;flex-shrink:0;animation:onlinePulse 2s ease-in-out infinite}
        @keyframes onlinePulse{0%,100%{box-shadow:0 0 4px #22c55e}50%{box-shadow:0 0 10px #22c55e,0 0 18px rgba(34,197,94,.3)}}
        .lb-challenge-btn{background:none;border:1.5px solid #4f8ef7;color:#4f8ef7;border-radius:8px;padding:.2rem .55rem;font-size:.75rem;cursor:pointer;transition:background .2s,color .2s;white-space:nowrap;flex-shrink:0}
        .lb-challenge-btn:hover{background:#4f8ef7;color:#fff}
        .leaderboard-list li{display:flex;align-items:center;gap:.5rem;}

        /* ══════════════════════════════════════════════
           ONLINE PLAYERS — REDISEÑO DESTACADO
        ══════════════════════════════════════════════ */
        .online-section {
            position: relative;
            padding: 22px 24px 20px;
            border-radius: 22px;
            background: linear-gradient(135deg, rgba(10,30,18,.95), rgba(10,22,14,.98));
            border: 1.5px solid rgba(34,197,94,.35);
            box-shadow: 0 0 0 1px rgba(34,197,94,.08), 0 8px 32px rgba(0,0,0,.3), 0 0 40px rgba(34,197,94,.06);
            overflow: hidden;
            animation: fadeSlideUp .7s .62s cubic-bezier(.22,1,.36,1) both;
            transition: border-color .4s, box-shadow .4s;
        }
        .online-section:hover {
            border-color: rgba(34,197,94,.6);
            box-shadow: 0 0 0 1px rgba(34,197,94,.15), 0 12px 40px rgba(0,0,0,.35), 0 0 60px rgba(34,197,94,.1);
        }
        /* Animated green glow background */
        .online-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 80% 60% at 50% 0%, rgba(34,197,94,.07) 0%, transparent 70%);
            pointer-events: none;
        }
        /* Top shimmer line */
        .online-section::after {
            content: '';
            position: absolute;
            inset: 0 0 auto;
            height: 1.5px;
            background: linear-gradient(90deg, transparent, rgba(34,197,94,.6), rgba(52,211,153,.8), rgba(34,197,94,.6), transparent);
            animation: onlineShimmer 3s ease-in-out infinite;
        }
        @keyframes onlineShimmer {
            0%,100% { opacity: .5; }
            50%      { opacity: 1; }
        }

        /* Header row */
        .online-section__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
        }
        .online-section__title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: var(--font-display);
            font-size: 1.05rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -.02em;
            margin: 0;
        }
        /* Live indicator ring */
        .online-live-ring {
            position: relative;
            width: 14px; height: 14px;
            flex-shrink: 0;
        }
        .online-live-ring__dot {
            position: absolute;
            inset: 3px;
            border-radius: 50%;
            background: #22c55e;
            box-shadow: 0 0 6px #22c55e;
            animation: liveRingDot 1.5s ease-in-out infinite;
        }
        .online-live-ring__pulse {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 1.5px solid #22c55e;
            animation: liveRingPulse 1.5s ease-out infinite;
        }
        @keyframes liveRingDot  { 0%,100%{opacity:1} 50%{opacity:.6} }
        @keyframes liveRingPulse { 0%{transform:scale(1);opacity:.8} 100%{transform:scale(2.2);opacity:0} }

        /* Live badge */
        .online-live-badge {
            font-size: .62rem;
            font-weight: 900;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #22c55e;
            background: rgba(34,197,94,.12);
            border: 1px solid rgba(34,197,94,.3);
            border-radius: 999px;
            padding: .18rem .55rem;
            animation: liveBadgePulse 2s ease-in-out infinite;
        }
        @keyframes liveBadgePulse { 0%,100%{border-color:rgba(34,197,94,.3)} 50%{border-color:rgba(34,197,94,.7);box-shadow:0 0 8px rgba(34,197,94,.2)} }

        /* Count pill */
        .online-count-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(34,197,94,.15);
            border: 1px solid rgba(34,197,94,.3);
            border-radius: 999px;
            padding: .28rem .8rem;
            font-size: .78rem;
            font-weight: 800;
            color: #4ade80;
            letter-spacing: .04em;
        }
        .online-count-pill i { font-size: .7rem; }

        /* Player list */
        .online-players-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 6px;
            position: relative;
            z-index: 1;
        }
        .online-player-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 10px;
            border-radius: 14px;
            background: rgba(255,255,255,.03);
            border: 1px solid rgba(34,197,94,.08);
            transition: background .2s, border-color .2s, transform .2s;
            animation: onlineRowIn .4s cubic-bezier(.22,1,.36,1) both;
        }
        .online-player-row:nth-child(1){animation-delay:.05s}
        .online-player-row:nth-child(2){animation-delay:.1s}
        .online-player-row:nth-child(3){animation-delay:.15s}
        .online-player-row:nth-child(4){animation-delay:.2s}
        .online-player-row:nth-child(5){animation-delay:.25s}
        @keyframes onlineRowIn { from{opacity:0;transform:translateX(-12px)} to{opacity:1;transform:none} }
        .online-player-row:hover {
            background: rgba(34,197,94,.07);
            border-color: rgba(34,197,94,.25);
            transform: translateX(3px);
        }

        /* Avatar */
        .online-player-avatar {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #166534, #15803d);
            border: 2px solid rgba(34,197,94,.5);
            box-shadow: 0 0 10px rgba(34,197,94,.25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .82rem;
            font-weight: 900;
            color: #bbf7d0;
            flex-shrink: 0;
            text-transform: uppercase;
            letter-spacing: 0;
            position: relative;
        }
        .online-player-avatar::after {
            content: '';
            position: absolute;
            bottom: -1px; right: -1px;
            width: 10px; height: 10px;
            border-radius: 50%;
            background: #22c55e;
            border: 2px solid rgba(10,30,18,.98);
            box-shadow: 0 0 6px #22c55e;
            animation: onlinePulse 2s ease-in-out infinite;
        }

        /* Player info */
        .online-player-info { flex: 1; min-width: 0; }
        .online-player-name {
            font-weight: 700;
            font-size: .88rem;
            color: #e2e8f0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: block;
            line-height: 1.2;
        }
        .online-player-pts {
            font-size: .72rem;
            color: #4ade80;
            font-weight: 600;
            letter-spacing: .04em;
        }

        /* Challenge button */
        .online-challenge-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: linear-gradient(135deg, rgba(79,142,247,.15), rgba(79,142,247,.08));
            border: 1.5px solid rgba(79,142,247,.4);
            color: #60a5fa;
            border-radius: 10px;
            padding: .35rem .75rem;
            font-size: .78rem;
            font-weight: 800;
            cursor: pointer;
            transition: background .2s, border-color .2s, transform .15s, box-shadow .2s;
            white-space: nowrap;
            flex-shrink: 0;
            letter-spacing: .02em;
            font-family: inherit;
        }
        .online-challenge-btn:hover:not(:disabled) {
            background: linear-gradient(135deg, rgba(79,142,247,.35), rgba(79,142,247,.2));
            border-color: rgba(79,142,247,.8);
            color: #fff;
            transform: scale(1.05);
            box-shadow: 0 0 16px rgba(79,142,247,.3);
        }
        .online-challenge-btn:disabled { opacity: .6; cursor: default; }
        .online-challenge-btn i { font-size: .72rem; }

        /* Empty state */
        .online-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 20px 0 8px;
            color: rgba(74,222,128,.35);
            font-size: .85rem;
            position: relative;
            z-index: 1;
        }
        .online-empty i { font-size: 2rem; }
        .online-empty span { color: #475569; font-size: .82rem; }

        /* ── Duel invite modal — REDESIGNED ── */
        #duel-invite-modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.75);
            z-index: 900;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(8px);
        }
        #duel-invite-modal.show { display: flex; }
        .duel-invite-card {
            background: linear-gradient(135deg, rgba(13,17,30,.98), rgba(8,12,24,.99));
            border: 1.5px solid rgba(79,142,247,.4);
            border-radius: 24px;
            padding: 2.2rem 2rem;
            max-width: 400px;
            width: 92%;
            text-align: center;
            box-shadow: 0 0 0 1px rgba(79,142,247,.08), 0 24px 80px rgba(0,0,0,.7), 0 0 60px rgba(79,142,247,.1);
            animation: inviteCardIn .5s cubic-bezier(.22,.61,.36,1);
            position: relative;
            overflow: hidden;
        }
        @keyframes inviteCardIn { from{opacity:0;transform:scale(.85) translateY(20px)} to{opacity:1;transform:none} }
        .duel-invite-card::before {
            content: '';
            position: absolute;
            inset: 0 0 auto;
            height: 1.5px;
            background: linear-gradient(90deg, transparent, rgba(79,142,247,.8), rgba(129,140,248,.9), rgba(79,142,247,.8), transparent);
        }
        .duel-invite-icon {
            font-size: 3.5rem;
            margin-bottom: .8rem;
            display: block;
            animation: inviteIconBounce .6s .1s cubic-bezier(.36,.07,.19,.97) both;
        }
        @keyframes inviteIconBounce { 0%{transform:scale(0) rotate(-20deg)} 60%{transform:scale(1.2) rotate(5deg)} 100%{transform:scale(1) rotate(0)} }
        .duel-invite-card h3 {
            margin: 0 0 .5rem;
            font-size: 1.5rem;
            font-weight: 900;
            background: linear-gradient(135deg, #fff, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .duel-invite-card p {
            color: #64748b;
            font-size: .92rem;
            margin: .4rem 0 1.6rem;
            line-height: 1.5;
        }
        .duel-invite-card p strong { color: #e2e8f0; }
        .duel-invite-btns { display: flex; gap: .75rem; justify-content: center; }
        #duel-accept-btn {
            flex: 1;
            padding: .8rem 1.4rem;
            border-radius: 12px;
            font-size: .95rem;
            font-weight: 800;
            cursor: pointer;
            border: none;
            background: linear-gradient(135deg, #4f8ef7, #7c3aed);
            color: #fff;
            box-shadow: 0 4px 20px rgba(79,142,247,.4);
            transition: transform .2s, box-shadow .2s;
            font-family: inherit;
            display: flex; align-items: center; justify-content: center; gap: 6px;
        }
        #duel-accept-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(79,142,247,.5); }
        #duel-reject-btn {
            padding: .8rem 1.4rem;
            border-radius: 12px;
            font-size: .95rem;
            font-weight: 700;
            cursor: pointer;
            border: 1px solid rgba(100,116,139,.3);
            background: rgba(255,255,255,.04);
            color: #64748b;
            transition: background .2s, color .2s;
            font-family: inherit;
            display: flex; align-items: center; justify-content: center; gap: 6px;
        }
        #duel-reject-btn:hover { background: rgba(255,255,255,.08); color: #94a3b8; }
    </style>
</head>
<body class="app-page">
    <!-- ═══ Ambient Background ═══ -->
    <div class="dash-ambient" aria-hidden="true">
        <div class="dash-ambient__orb dash-ambient__orb--1"></div>
        <div class="dash-ambient__orb dash-ambient__orb--2"></div>
        <div class="dash-ambient__orb dash-ambient__orb--3"></div>
        <div class="dash-ambient__orb dash-ambient__orb--4"></div>
    </div>

    <div class="page-shell">
        <header class="site-header" data-reveal>
            <a class="brand" href="dashboard.php">
                <span class="brand__mark"><img src="assets/img/logo.png" alt="Excel Snake" width="46" height="46"></span>
                <span>
                    <strong>Excel Snake</strong>
                    <small>Panel de progreso</small>
                </span>
            </a>
            <nav class="site-nav site-nav--actions" id="main-nav">
                <a href="leaderboard.php">Ranking</a>
                <a href="logout.php">Salir</a>
            </nav>
            <button class="nav-toggle" type="button" aria-label="Menú" aria-expanded="false" data-nav-toggle>
                <span class="nav-toggle__bar"></span>
                <span class="nav-toggle__bar"></span>
                <span class="nav-toggle__bar"></span>
            </button>
        </header>

        <nav class="bottom-nav" aria-label="Navegación principal">
            <a href="dashboard.php" class="bottom-nav__item bottom-nav__item--active">
                <i class="fa-solid fa-map"></i>
                <span>Mapa</span>
            </a>
            <a href="snake.php?nivel=<?= e((string) $currentLevel) ?>" class="bottom-nav__item">
                <i class="fa-solid fa-gamepad"></i>
                <span>Snake</span>
            </a>
            <a href="leaderboard.php" class="bottom-nav__item">
                <i class="fa-solid fa-trophy"></i>
                <span>Ranking</span>
            </a>
            <a href="logout.php" class="bottom-nav__item">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Salir</span>
            </a>
        </nav>

        <?php if ($flash): ?>
            <div class="flash flash--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <?php if (!$emailVerified): ?>
            <div class="verification-banner" id="verification-banner">
                <i class="fa-solid fa-envelope-circle-check"></i>
                <span>Tu correo aún no está verificado. Revisa tu bandeja de entrada.</span>
                <a href="#" id="resend-verification">Reenviar correo</a>
                <button type="button" id="dismiss-verification" aria-label="Cerrar" title="Cerrar">&times;</button>
            </div>
        <?php endif; ?>

        <!-- ═══ HERO WELCOME ═══ -->
        <section class="dash-welcome" data-reveal>
            <div class="dash-welcome__shimmer"></div>
            <div class="dash-welcome__orbit dash-welcome__orbit--1"></div>
            <div class="dash-welcome__orbit dash-welcome__orbit--2"></div>
            <div class="dash-welcome__orbit dash-welcome__orbit--3"></div>
            <div>
                <span class="dash-welcome__greeting"><i class="fa-solid fa-bolt"></i> Hola, <?= e($user['username'] ?? $_SESSION['username'] ?? 'Jugador') ?></span>
                <h1 class="dash-welcome__title">Tu misión</h1>
                <p class="dash-welcome__subtitle"><?= e(level_band_title($currentLevel)) ?> · Nivel <?= e((string) $currentLevel) ?> te espera. Mueve la snake hasta la respuesta correcta.</p>
                <div class="dash-welcome__actions">
                    <a class="button button--primary button--glow button--lg" href="snake.php?nivel=<?= e((string) $currentLevel) ?>"><i class="fa-solid fa-play"></i> Jugar nivel <?= e((string) $currentLevel) ?></a>
                    <a class="button button--ghost" href="leaderboard.php"><i class="fa-solid fa-trophy"></i> Ranking</a>
                </div>
            </div>
            <div class="dash-welcome__ring">
                <div class="focus-ring" style="--progress: <?= e($progressPercent) ?>%;" aria-label="<?= e((string) number_format((float) $progressPercent, 0)) ?> por ciento completado">
                    <div class="focus-ring__inner">
                        <strong class="focus-ring__value"><?= e((string) number_format((float) $progressPercent, 0)) ?>%</strong>
                        <small>Completado</small>
                        <span class="focus-ring__meta"><?= e((string) $progress['niveles_completados']) ?> de <?= TOTAL_LEVELS ?> niveles</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══ QUICK STATS ═══ -->
        <section class="dash-stats" data-stagger-group>
            <div class="dash-stat dash-stat--xp" data-reveal-item>
                <div class="dash-stat__icon"><i class="fa-solid fa-bolt"></i></div>
                <div class="dash-stat__body">
                    <span class="dash-stat__label">XP Total</span>
                    <strong class="dash-stat__value"><?= e((string) $progress['puntos']) ?></strong>
                </div>
            </div>
            <div class="dash-stat dash-stat--levels" data-reveal-item>
                <div class="dash-stat__icon"><i class="fa-solid fa-layer-group"></i></div>
                <div class="dash-stat__body">
                    <span class="dash-stat__label">Niveles</span>
                    <strong class="dash-stat__value"><?= e((string) $progress['niveles_completados']) ?><small>/<?= TOTAL_LEVELS ?></small></strong>
                </div>
            </div>
            <div class="dash-stat dash-stat--lives" data-reveal-item>
                <div class="dash-stat__icon"><i class="fa-solid fa-heart"></i></div>
                <div class="dash-stat__body">
                    <span class="dash-stat__label">Vidas</span>
                    <strong class="dash-stat__value"><?= e((string) $progress['vidas']) ?><small>/5</small></strong>
                    <div class="lives-bar">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="lives-bar__heart <?= $i <= (int) $progress['vidas'] ? 'is-full' : 'is-empty' ?>"><i class="fa-solid fa-heart"></i></span>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php if ((int) $progress['vidas'] < 5 && !empty($progress['last_life_lost_at'])): ?>
                    <?php
                    $timerStmt = getPDO()->prepare('SELECT GREATEST(0, TIMESTAMPDIFF(SECOND, ?, NOW())) AS elapsed');
                    $timerStmt->execute([$progress['last_life_lost_at']]);
                    $secsElapsed = (int) $timerStmt->fetchColumn();
                    $secsInCycle = $secsElapsed % 120;
                    $secsLeft = 120 - $secsInCycle;
                    if ($secsLeft > 120) { $secsLeft = 120; }
                    ?>
                    <div class="lives-timer"><i class="fa-solid fa-clock"></i> <span id="life-timer" data-seconds="<?= $secsLeft ?>"><?= sprintf('%d:%02d', intdiv($secsLeft, 60), $secsLeft % 60) ?></span></div>
                <?php endif; ?>
            </div>
            <a href="snake.php?nivel=<?= e((string) $currentLevel) ?>" class="dash-stat dash-stat--next" data-reveal-item>
                <div class="dash-stat__icon"><i class="fa-solid fa-play"></i></div>
                <div class="dash-stat__body">
                    <span class="dash-stat__label">Siguiente</span>
                    <strong class="dash-stat__value">Nv. <?= e((string) $currentLevel) ?></strong>
                </div>
                <i class="fa-solid fa-chevron-right dash-stat__arrow"></i>
            </a>
        </section>

        <!-- ═══ XP PROGRESS TRACK ═══ -->
        <section class="dash-xp-track" data-reveal>
            <div class="dash-xp-track__row">
                <span class="dash-xp-track__label"><i class="fa-solid fa-fire"></i> Progreso general</span>
                <span class="dash-xp-track__pct"><?= number_format(progress_percentage($progress), 0) ?>%</span>
            </div>
            <div class="progress-bar progress-bar--large progress-bar--animated">
                <div class="progress-bar__fill" style="width: <?= number_format(progress_percentage($progress), 2, '.', '') ?>%"></div>
            </div>
        </section>

        <!-- ═══ MAIN GRID ═══ -->
        <main class="dash-main">
            <!-- Levels Panel -->
            <section class="dash-levels" data-reveal>
                <div class="dash-levels__heading">
                    <div>
                        <h2><i class="fa-solid fa-route"></i> Ruta de niveles</h2>
                        <p class="dash-levels__summary">Niveles <?= e((string) $previewStart) ?>–<?= e((string) $previewEnd) ?> · Tu progreso actual</p>
                    </div>
                    <button class="button button--ghost levels-panel__toggle" type="button" data-route-toggle data-label-expand="Ver los <?= TOTAL_LEVELS ?> niveles" data-label-collapse="Volver a resumen">
                        Ver los <?= TOTAL_LEVELS ?> niveles
                    </button>
                </div>
                <div class="levels-panel__viewport is-collapsed" data-route-viewport>
                    <div class="levels-grid">
                    <?php foreach ($levels as $level): ?>
                        <?php
                        $number = (int) $level['numero'];
                        $status = $statusMap[$number] ?? null;
                        $completed = !empty($status['completed_at']);
                        $unlocked = level_is_unlocked($progress, $number);
                        $cardClass = $completed ? 'is-completed' : ($unlocked ? 'is-unlocked' : 'is-locked');
                        $hiddenInPreview = $number < $previewStart || $number > $previewEnd;
                        ?>
                        <?php $href = $unlocked ? 'snake.php?nivel=' . e((string) $number) : '#'; ?>
                        <a href="<?= $href ?>" class="level-card <?= e($cardClass) ?><?= $hiddenInPreview ? ' level-card--preview-hidden' : '' ?>" data-level-card <?= !$unlocked ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                            <div class="level-card__header">
                                <span class="level-card__number">Nivel <?= e((string) $number) ?></span>
                                <span class="pill <?= e(difficulty_class((string) $level['dificultad'])) ?>"><?= e($level['dificultad']) ?></span>
                            </div>
                            <h3><?= e($level['titulo']) ?></h3>
                            <p class="level-card__category"><?= e($level['categoria']) ?></p>
                            <div class="level-card__footer">
                                <?php if ($completed): ?>
                                    <span class="level-card__status level-card__status--done"><i class="fa-solid fa-circle-check"></i> Completado</span>
                                <?php elseif ($unlocked): ?>
                                    <span class="level-card__status level-card__status--open"><i class="fa-solid fa-play"></i> Disponible</span>
                                <?php else: ?>
                                    <span class="level-card__status level-card__status--locked"><i class="fa-solid fa-lock"></i> Bloqueado</span>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <!-- Sidebar -->
            <aside class="dash-sidebar">

                <!-- ★ ONLINE PLAYERS — DESTACADO ★ -->
                <?php $onlineCount = count(array_filter($onlineUsers, fn($ou) => (int)$ou['id'] !== (int)$userId)); ?>
                <section class="online-section" data-reveal>
                    <div class="online-section__head">
                        <h2 class="online-section__title">
                            <span class="online-live-ring">
                                <span class="online-live-ring__dot"></span>
                                <span class="online-live-ring__pulse"></span>
                            </span>
                            Jugadores en línea
                            <span class="online-live-badge">LIVE</span>
                        </h2>
                        <span class="online-count-pill">
                            <i class="fa-solid fa-circle-dot"></i>
                            <?= $onlineCount ?> <?= $onlineCount === 1 ? 'activo' : 'activos' ?>
                        </span>
                    </div>

                    <?php if ($onlineCount === 0): ?>
                        <div class="online-empty">
                            <i class="fa-regular fa-circle"></i>
                            <span>Nadie más en línea ahora mismo.</span>
                        </div>
                    <?php else: ?>
                        <ul class="online-players-list">
                            <?php foreach ($onlineUsers as $ou):
                                if ((int)$ou['id'] === (int)$userId) continue;
                                $initial = mb_strtoupper(mb_substr($ou['username'], 0, 1));
                            ?>
                            <li class="online-player-row">
                                <div class="online-player-avatar"><?= e($initial) ?></div>
                                <div class="online-player-info">
                                    <span class="online-player-name"><?= e($ou['username']) ?></span>
                                    <span class="online-player-pts"><i class="fa-solid fa-bolt" style="font-size:.65rem;"></i> <?= e((string)($ou['puntos'] ?? 0)) ?> pts</span>
                                </div>
                                <button class="online-challenge-btn lb-challenge-btn"
                                        data-user-id="<?= (int)$ou['id'] ?>"
                                        data-username="<?= e($ou['username']) ?>">
                                    <i class="fa-solid fa-swords"></i> Desafiar
                                </button>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </section>

                <!-- Leaderboard -->
                <section class="dash-leaderboard" data-reveal>
                    <div class="dash-leaderboard__head">
                        <h2><i class="fa-solid fa-crown"></i> Top jugadores</h2>
                        <a href="leaderboard.php">Ver más</a>
                    </div>
                    <ol class="leaderboard-list">
                        <?php foreach ($leaderboard as $idx => $entry): ?>
                            <?php $entryId = (int)($entry['id'] ?? 0); $isOnline = isset($onlineLbMap[$entryId]); ?>
                            <li>
                                <span class="lb-rank"><?= $idx + 1 ?></span>
                                <div style="display:flex;align-items:center;gap:.45rem;flex:1;min-width:0;">
                                    <?php if ($isOnline): ?>
                                        <span class="lb-online-dot" title="En línea"></span>
                                    <?php endif; ?>
                                    <strong><?= e($entry['username']) ?></strong>
                                    <span><?= e((string) $entry['niveles_completados']) ?> niveles</span>
                                </div>
                                <span class="lb-pts"><?= e((string) $entry['puntos']) ?></span>
                                <?php if ($isOnline && $entryId !== (int)$userId): ?>
                                    <button class="lb-challenge-btn"
                                            data-user-id="<?= $entryId ?>"
                                            data-username="<?= e($entry['username']) ?>"
                                            title="Desafiar a <?= e($entry['username']) ?>">
                                        <i class="fa-solid fa-swords"></i>
                                    </button>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </section>

                <!-- Achievement -->
                <?php
                $completedCount = (int) $progress['niveles_completados'];
                $achievementIcon = 'fa-seedling';
                $achievementTitle = 'Principiante';
                $achievementDesc = 'Completa tu primer nivel para empezar tu camino.';
                if ($completedCount >= 100) {
                    $achievementIcon = 'fa-gem';
                    $achievementTitle = 'Maestro Excel';
                    $achievementDesc = 'Has completado más de 100 niveles. Eres imparable.';
                } elseif ($completedCount >= 50) {
                    $achievementIcon = 'fa-fire-flame-curved';
                    $achievementTitle = 'En llamas';
                    $achievementDesc = 'Más de 50 niveles completados. Tu dominio crece.';
                } elseif ($completedCount >= 20) {
                    $achievementIcon = 'fa-medal';
                    $achievementTitle = 'Avanzado';
                    $achievementDesc = 'Ya dominas más de 20 niveles. Sigue así.';
                } elseif ($completedCount >= 5) {
                    $achievementIcon = 'fa-star';
                    $achievementTitle = 'Explorador';
                    $achievementDesc = 'Has completado 5+ niveles. Vas por buen camino.';
                } elseif ($completedCount >= 1) {
                    $achievementIcon = 'fa-flag-checkered';
                    $achievementTitle = 'Primer paso';
                    $achievementDesc = 'Completaste tu primer nivel. El inicio de algo grande.';
                }
                ?>
                <div class="dash-achievement" data-reveal>
                    <div class="dash-achievement__icon"><i class="fa-solid <?= $achievementIcon ?>"></i></div>
                    <div class="dash-achievement__body">
                        <p class="dash-achievement__title"><?= $achievementTitle ?></p>
                        <p class="dash-achievement__desc"><?= $achievementDesc ?></p>
                    </div>
                </div>

                <!-- Tips -->
                <section class="dash-tips" data-reveal>
                    <h2><i class="fa-solid fa-lightbulb"></i> Tips rápidos</h2>
                    <ul>
                        <li>Escribe la fórmula con o sin espacios: el validador normaliza el formato.</li>
                        <li>Puedes usar coma o punto y coma como separador de argumentos.</li>
                        <li>Revisa la celda objetivo antes de enviar tu respuesta.</li>
                    </ul>
                </section>
            </aside>
        </main>
    </div>
    <?php render_app_scripts(); ?>
    <script>
    (function(){
        /* ═══ Cursor Glow Follower ═══ */
        if (!window.matchMedia('(pointer: coarse)').matches) {
            var glow = document.createElement('div');
            glow.className = 'dash-cursor-glow';
            document.body.appendChild(glow);
            document.addEventListener('mousemove', function(e) {
                glow.style.left = e.clientX + 'px';
                glow.style.top = e.clientY + 'px';
            });
        }

        /* ═══ Floating particles (enhanced with shapes) ═══ */
        var colors = ['#34D399','#3b82f6','#fbbf24','#a855f7','#f87171','#f97316','#e879f9','#22d3ee'];
        var types = ['dot','dot','dot','ring','cross'];
        for (var i = 0; i < 20; i++) {
            var p = document.createElement('div');
            var type = types[i % types.length];
            p.className = 'dash-particle' + (type !== 'dot' ? ' dash-particle--' + type : '');
            var size = type === 'dot' ? (3 + Math.random() * 5) : (8 + Math.random() * 6);
            var dur = 12 + Math.random() * 18;
            var delay = Math.random() * 15;
            var left = Math.random() * 100;
            var color = colors[i % colors.length];
            p.style.cssText = 'width:'+size+'px;height:'+size+'px;left:'+left+'%;color:'+color+';background:'+color+';animation-duration:'+dur+'s;animation-delay:'+delay+'s;opacity:0;--size:'+size+'px;';
            document.body.appendChild(p);
        }

        /* ═══ Stat cards ripple on hover ═══ */
        document.querySelectorAll('.dash-stat').forEach(function(stat) {
            stat.addEventListener('mousemove', function(e) {
                var rect = stat.getBoundingClientRect();
                var x = ((e.clientX - rect.left) / rect.width * 100);
                var y = ((e.clientY - rect.top) / rect.height * 100);
                stat.style.setProperty('--ripple-x', x + '%');
                stat.style.setProperty('--ripple-y', y + '%');
            });
        });

        /* ═══ Counter animation for stat values ═══ */
        var statValues = document.querySelectorAll('.dash-stat__value');
        function animateCounter(el) {
            var text = el.textContent.trim();
            var match = text.match(/^(\d+)/);
            if (!match) return;
            var target = parseInt(match[1], 10);
            if (target === 0) return;
            var suffix = text.slice(match[1].length);
            var duration = 1200;
            var start = performance.now();
            el.textContent = '0' + suffix;
            function tick(now) {
                var elapsed = now - start;
                var progress = Math.min(elapsed / duration, 1);
                var eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = Math.round(target * eased) + suffix;
                if (progress < 1) requestAnimationFrame(tick);
            }
            requestAnimationFrame(tick);
        }
        if (window.IntersectionObserver) {
            var counterObserver = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        animateCounter(entry.target);
                        counterObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });
            statValues.forEach(function(el) { counterObserver.observe(el); });
        } else {
            statValues.forEach(animateCounter);
        }

        /* ═══ XP percentage counter ═══ */
        var xpPct = document.querySelector('.dash-xp-track__pct');
        if (xpPct) {
            var pctMatch = xpPct.textContent.trim().match(/^(\d+)/);
            if (pctMatch) {
                var pctTarget = parseInt(pctMatch[1], 10);
                var pctSuffix = xpPct.textContent.trim().slice(pctMatch[1].length);
                xpPct.textContent = '0' + pctSuffix;
                if (window.IntersectionObserver) {
                    var pctObs = new IntersectionObserver(function(entries) {
                        entries.forEach(function(entry) {
                            if (entry.isIntersecting) {
                                var s = performance.now();
                                (function t(now) {
                                    var p = Math.min((now - s) / 1400, 1);
                                    var e = 1 - Math.pow(1 - p, 3);
                                    xpPct.textContent = Math.round(pctTarget * e) + pctSuffix;
                                    if (p < 1) requestAnimationFrame(t);
                                })(s);
                                pctObs.unobserve(entry.target);
                            }
                        });
                    }, { threshold: 0.5 });
                    pctObs.observe(xpPct);
                }
            }
        }

        /* ═══ Progress bar animated fill ═══ */
        var progressFill = document.querySelector('.dash-xp-track .progress-bar__fill');
        if (progressFill) {
            var targetWidth = progressFill.style.width;
            progressFill.style.width = '0%';
            progressFill.style.transition = 'width 1.8s cubic-bezier(.22,1,.36,1)';
            if (window.IntersectionObserver) {
                var fillObs = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            setTimeout(function() { progressFill.style.width = targetWidth; }, 200);
                            fillObs.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.3 });
                fillObs.observe(progressFill.parentElement);
            } else {
                setTimeout(function() { progressFill.style.width = targetWidth; }, 500);
            }
        }

        /* ═══ Magnetic buttons in welcome ═══ */
        if (!window.matchMedia('(pointer: coarse)').matches) {
            document.querySelectorAll('.dash-welcome__actions .button').forEach(function(btn) {
                btn.addEventListener('mousemove', function(e) {
                    var rect = btn.getBoundingClientRect();
                    var x = e.clientX - rect.left - rect.width / 2;
                    var y = e.clientY - rect.top - rect.height / 2;
                    btn.style.transform = 'translateY(-2px) translateX(' + (x * 0.12) + 'px) translateY(' + (y * 0.12) + 'px)';
                });
                btn.addEventListener('mouseleave', function() {
                    btn.style.transform = '';
                });
            });
        }

        /* ═══ Tilt effect on welcome card ═══ */
        if (!window.matchMedia('(pointer: coarse)').matches) {
            var welcome = document.querySelector('.dash-welcome');
            if (welcome) {
                welcome.addEventListener('mousemove', function(e) {
                    var rect = welcome.getBoundingClientRect();
                    var px = (e.clientX - rect.left) / rect.width - 0.5;
                    var py = (e.clientY - rect.top) / rect.height - 0.5;
                    welcome.style.transform = 'perspective(1200px) rotateX(' + (py * -3) + 'deg) rotateY(' + (px * 3) + 'deg)';
                });
                welcome.addEventListener('mouseleave', function() {
                    welcome.style.transform = '';
                    welcome.style.transition = 'transform 0.5s ease';
                    setTimeout(function() { welcome.style.transition = ''; }, 500);
                });
            }
        }

        /* ═══ Level card hover glow effect ═══ */
        document.querySelectorAll('.level-card:not([aria-disabled])').forEach(function(card) {
            card.addEventListener('mousemove', function(e) {
                var rect = card.getBoundingClientRect();
                var x = e.clientX - rect.left;
                var y = e.clientY - rect.top;
                card.style.background = 'radial-gradient(circle at ' + x + 'px ' + y + 'px, rgba(255,255,255,.06), rgba(255,255,255,.02) 50%, rgba(255,255,255,.04))';
            });
            card.addEventListener('mouseleave', function() {
                card.style.background = '';
            });
        });

        /* ═══ Life timer countdown ═══ */
        var el = document.getElementById('life-timer');
        if (el) {
            var secs = parseInt(el.dataset.seconds, 10);
            var iv = setInterval(function(){
                secs--;
                if (secs <= 0) { clearInterval(iv); location.reload(); return; }
                el.textContent = Math.floor(secs/60) + ':' + String(secs%60).padStart(2,'0');
            }, 1000);
        }

        /* ═══ Email verification banner ═══ */
        var banner = document.getElementById('verification-banner');
        if (banner) {
            if (localStorage.getItem('dismiss_email_banner') === '1') {
                banner.style.display = 'none';
            } else {
                var dismissBtn = document.getElementById('dismiss-verification');
                if (dismissBtn) {
                    dismissBtn.addEventListener('click', function() {
                        banner.style.display = 'none';
                        localStorage.setItem('dismiss_email_banner', '1');
                    });
                }

                var resendLink = document.getElementById('resend-verification');
                if (resendLink) {
                    resendLink.addEventListener('click', function(e) {
                        e.preventDefault();
                        resendLink.textContent = 'Enviando...';
                        resendLink.style.pointerEvents = 'none';
                        var fd = new FormData();
                        fd.append('csrf_token', '<?= e(csrf_token()) ?>');
                        fetch('resend_verification.php', {
                            method: 'POST',
                            body: fd,
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(function(r) { return r.json(); })
                        .then(function(d) {
                            banner.querySelector('span').textContent = d.message;
                            resendLink.textContent = 'Reenviar correo';
                            setTimeout(function() { resendLink.style.pointerEvents = ''; }, 120000);
                        })
                        .catch(function() {
                            resendLink.textContent = 'Reenviar correo';
                            resendLink.style.pointerEvents = '';
                        });
                    });
                }
            }
        }
    })();
    </script>

<!-- Duel invite modal -->
<div id="duel-invite-modal" role="dialog" aria-modal="true" aria-labelledby="duel-invite-title">
  <div class="duel-invite-card">
    <span class="duel-invite-icon">⚔️</span>
    <h3 id="duel-invite-title">¡Te desafían!</h3>
    <p id="duel-invite-msg"><strong>Alguien</strong> quiere un duelo de 5 preguntas de Excel.</p>
    <div class="duel-invite-btns">
      <button id="duel-accept-btn"><i class="fa-solid fa-check"></i> Aceptar duelo</button>
      <button id="duel-reject-btn"><i class="fa-solid fa-xmark"></i> Rechazar</button>
    </div>
  </div>
</div>

<script>
(function() {
    const CSRF = <?= json_encode(csrf_token()) ?>;
    let activeDuelIdForInvite = null;
    let heartbeatSeenDuelId = null;
    let sentDuelId = null;

    async function post(url, data) {
        const fd = new FormData();
        for (const [k, v] of Object.entries(data)) fd.append(k, v);
        const r = await fetch(url, { method: 'POST', body: fd });
        return r.json();
    }

    // Heartbeat: update last_seen + check for pending invites
    async function heartbeat() {
        try {
            const data = await post('api_user_heartbeat.php', { csrf_token: CSRF });
            if (data.pending_duel_id && data.pending_duel_id !== heartbeatSeenDuelId) {
                heartbeatSeenDuelId = data.pending_duel_id;
                activeDuelIdForInvite = data.pending_duel_id;
                showInviteModal(data.challenger_name || 'Un jugador');
            }
            // Redirect challenger when their sent duel becomes active
            if (data.active_duel_id && data.active_duel_id === sentDuelId) {
                window.location.href = 'duel.php?id=' + data.active_duel_id;
            }
        } catch {}
    }

    // Challenge button click
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.lb-challenge-btn');
        if (!btn) return;
        const targetId   = btn.dataset.userId;
        const targetName = btn.dataset.username;
        if (!targetId) return;

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';

        try {
            const res = await post('api_duel_create.php', {
                csrf_token:   CSRF,
                challenged_id: targetId,
            });
            if (res.ok) {
                sentDuelId = res.duel_id;
                btn.innerHTML = '<i class="fa-solid fa-check"></i> ¡Invitación enviada!';
                btn.style.borderColor = '#22c55e';
                btn.style.color = '#22c55e';
            } else {
                btn.innerHTML = '<i class="fa-solid fa-swords"></i> ' + (res.error || 'Error');
                btn.disabled = false;
            }
        } catch {
            btn.innerHTML = '<i class="fa-solid fa-swords"></i> Error';
            btn.disabled = false;
        }
    });

    // Invite modal handling
    function showInviteModal(challengerName) {
        document.getElementById('duel-invite-msg').innerHTML =
            '<strong>' + challengerName + '</strong> te desafía a una ronda de 5 preguntas de Excel.';
        document.getElementById('duel-invite-modal').classList.add('show');
    }

    document.getElementById('duel-accept-btn').addEventListener('click', async () => {
        if (!activeDuelIdForInvite) return;
        const duelId = activeDuelIdForInvite;
        activeDuelIdForInvite = null;
        document.getElementById('duel-invite-modal').classList.remove('show');

        const res = await post('api_duel_respond.php', {
            csrf_token: CSRF,
            duel_id:    duelId,
            action:     'accept',
        });
        if (res.ok) {
            window.location.href = 'duel.php?id=' + duelId;
        }
    });

    document.getElementById('duel-reject-btn').addEventListener('click', async () => {
        if (!activeDuelIdForInvite) return;
        const duelId = activeDuelIdForInvite;
        activeDuelIdForInvite = null;
        document.getElementById('duel-invite-modal').classList.remove('show');

        post('api_duel_respond.php', {
            csrf_token: CSRF,
            duel_id:    duelId,
            action:     'reject',
        }).catch(() => {});
    });

    // Start heartbeat immediately + every 15s
    heartbeat();
    setInterval(heartbeat, 15000);
})();
</script>
</body>
</html>
