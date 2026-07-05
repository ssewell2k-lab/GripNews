<?php
/**
 * GripNews.uk — Gaming Overlay
 * Migrated from sandbox/overlay.html
 */
require_once __DIR__ . '/includes/functions.php';

$page_title = "Gaming Overlay — GripNews";
$page_desc  = "Gaming overlay intelligence and tracking dashboard.";
$nav_active = 'overlay';

require_once __DIR__ . '/includes/header.php';
?>

<style>
/* ── Gaming Overlay Styles ───────────────────────────────────── */
:root {
  --navy: #0B0E17;
  --navy-light: #111628;
  --navy-mid: #161C2E;
  --cyan: #00d4ff;
  --purple: #bf5fff;
  --gold: #FFD700;
  --text: #e0e6f0;
  --text-dim: #8892a8;
  --border: #1e2640;
  --card-bg: #17171c;
  --danger: #ff4466;
  --success: #22c55e;
}
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  background: var(--navy);
  color: var(--text);
  line-height: 1.5;
  min-height: 100vh;
}
a { color: var(--cyan); text-decoration: none; }
a:hover { color: var(--purple); }

.header {
  background: linear-gradient(135deg, var(--navy) 0%, var(--navy-mid) 100%);
  border-bottom: 1px solid var(--border);
  padding: 1rem 2rem;
  display: flex; align-items: center; justify-content: space-between;
  position: sticky; top: 0; z-index: 100;
  backdrop-filter: blur(12px);
}
.header-left { display: flex; align-items: center; gap: 1rem; }
.logo { font-size: 1.5rem; font-weight: 800; text-decoration: none; }
.logo span:first-child { color: var(--cyan); }
.logo span:last-child { color: var(--text); }
.header-tag {
  font-size: 0.7rem; background: rgba(191,95,255,0.1); color: var(--purple);
  padding: 0.2rem 0.6rem; border-radius: 12px; border: 1px solid rgba(191,95,255,0.2);
  text-transform: uppercase; letter-spacing: 0.5px;
}
.header-links { display: flex; gap: 1.5rem; font-size: 0.85rem; }

.container { max-width: 900px; margin: 0 auto; padding: 2rem; }

/* Hero */
.hero {
  text-align: center; padding: 2rem 0 2.5rem;
}
.hero h1 {
  font-size: 1.6rem; font-weight: 800; margin-bottom: 0.5rem;
  background: linear-gradient(135deg, var(--purple), var(--cyan));
  -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
}
.hero p { color: var(--text-dim); font-size: 0.9rem; max-width: 500px; margin: 0 auto; }

/* Game search */
.game-search {
  max-width: 500px; margin: 0 auto 2rem; position: relative;
}
.game-search input {
  width: 100%; padding: 0.8rem 1.2rem; padding-right: 2.5rem;
  background: var(--card-bg); border: 1px solid var(--border); border-radius: 10px;
  color: var(--text); font-size: 0.9rem; font-family: inherit; outline: none;
  transition: border-color 0.2s;
}
.game-search input::placeholder { color: var(--text-dim); }
.game-search input:focus { border-color: var(--cyan); }
.game-search .icon {
  position: absolute; right: 1rem; top: 50%; transform: translateY(-50%);
  color: var(--text-dim); font-size: 1rem;
}

/* Overlay preview */
.preview-label {
  font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase;
  letter-spacing: 0.5px; margin-bottom: 0.8rem; text-align: center;
}

.overlay-container {
  position: relative;
  background: linear-gradient(135deg, #1a1a2e, #16213e);
  border: 1px solid var(--border); border-radius: 14px;
  padding: 1.5rem; min-height: 450px;
  overflow: hidden;
}
.overlay-container::before {
  content: ''; position: absolute; inset: 0;
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="4" height="4"><rect width="1" height="1" fill="rgba(255,255,255,0.02)"/></svg>');
  pointer-events: none;
}

/* Simulated game background */
.game-bg-label {
  position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
  font-size: 0.85rem; color: rgba(255,255,255,0.08); text-transform: uppercase;
  letter-spacing: 4px; font-weight: 700; pointer-events: none;
}

/* Overlay widget */
.overlay-widget {
  position: relative; width: 320px;
  background: rgba(11,14,23,0.92); border: 1px solid rgba(0,212,255,0.15);
  border-radius: 12px; backdrop-filter: blur(16px);
  box-shadow: 0 8px 32px rgba(0,0,0,0.4);
  animation: slideIn 0.5s ease-out;
}
@keyframes slideIn {
  from { transform: translateX(-20px); opacity: 0; }
  to { transform: translateX(0); opacity: 1; }
}

.widget-header {
  padding: 0.7rem 1rem; border-bottom: 1px solid rgba(0,212,255,0.1);
  display: flex; align-items: center; justify-content: space-between;
}
.widget-header-left { display: flex; align-items: center; gap: 0.5rem; }
.widget-logo { font-size: 0.75rem; font-weight: 700; }
.widget-logo span:first-child { color: var(--cyan); }
.widget-logo span:last-child { color: var(--text); }
.widget-status {
  width: 6px; height: 6px; border-radius: 50%; background: var(--success);
  animation: pulse 2s infinite;
}
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
.widget-minimize {
  background: none; border: none; color: var(--text-dim); cursor: pointer;
  font-size: 0.8rem; padding: 0.2rem;
}

.widget-alerts {
  max-height: 300px; overflow-y: auto;
}
.widget-alerts::-webkit-scrollbar { width: 3px; }
.widget-alerts::-webkit-scrollbar-thumb { background: rgba(0,212,255,0.2); border-radius: 3px; }

.alert-item {
  padding: 0.6rem 1rem; border-bottom: 1px solid rgba(30,38,64,0.4);
  transition: background 0.15s;
}
.alert-item:hover { background: rgba(0,212,255,0.04); }
.alert-item:last-child { border-bottom: none; }
.alert-top { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.2rem; }
.alert-sev {
  width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0;
}
.alert-sev.critical { background: var(--danger); box-shadow: 0 0 6px rgba(255,68,102,0.4); }
.alert-sev.high { background: #ffa500; }
.alert-sev.medium { background: var(--gold); }
.alert-sev.low { background: var(--cyan); }
.alert-game { font-size: 0.78rem; font-weight: 600; color: var(--text); }
.alert-time { font-size: 0.6rem; color: var(--text-dim); margin-left: auto; }
.alert-desc { font-size: 0.7rem; color: var(--text-dim); padding-left: 1rem; }

.widget-footer {
  padding: 0.5rem 1rem; border-top: 1px solid rgba(0,212,255,0.1);
  text-align: center;
}
.widget-footer a {
  font-size: 0.65rem; color: var(--cyan); opacity: 0.7;
}

/* How it works */
.how-section {
  margin-top: 2.5rem;
}
.how-title {
  font-size: 1.1rem; font-weight: 700; margin-bottom: 1.2rem;
  text-align: center;
}
.how-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
@media (max-width: 600px) { .how-grid { grid-template-columns: 1fr; } }
.how-card {
  background: var(--card-bg); border: 1px solid var(--border);
  border-radius: 12px; padding: 1.5rem; text-align: center;
}
.how-card .icon { font-size: 1.8rem; margin-bottom: 0.6rem; }
.how-card h3 { font-size: 0.9rem; font-weight: 600; margin-bottom: 0.3rem; }
.how-card p { font-size: 0.8rem; color: var(--text-dim); }

/* CTA */
.cta-bar {
  text-align: center; margin-top: 2rem; padding: 1.5rem;
  background: linear-gradient(135deg, rgba(0,212,255,0.05), rgba(191,95,255,0.05));
  border: 1px solid var(--border); border-radius: 14px;
}
.cta-bar p { color: var(--text-dim); font-size: 0.85rem; margin-bottom: 0.8rem; }
.cta-btn {
  display: inline-block; padding: 0.6rem 1.5rem;
  background: linear-gradient(135deg, var(--cyan), var(--purple));
  color: #fff; border-radius: 8px; font-weight: 600; font-size: 0.85rem;
  transition: transform 0.2s, box-shadow 0.2s;
}
.cta-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 16px rgba(0,212,255,0.3); color: #fff; }

.footer {
  text-align: center; padding: 2rem;
  border-top: 1px solid var(--border); margin-top: 2rem;
  font-size: 0.8rem; color: var(--text-dim);
}
/* ── Keyframes ── */
@keyframes sb-neon-pulse{0%,100%{opacity:.3}50%{opacity:.7}}
@keyframes sb-dot-pulse{0%,100%{opacity:1;box-shadow:0 0 8px #00d4ff}50%{opacity:.5;box-shadow:0 0 16px #00d4ff}}

/* ── Eco Banner ── */
.eco-banner{background:linear-gradient(90deg,rgba(77,171,247,0.08) 0%,rgba(132,94,247,0.08) 50%,rgba(0,208,132,0.08) 100%);border-bottom:1px solid rgba(77,171,247,0.12);padding:6px 2rem;text-align:center;font-size:0.72rem;letter-spacing:0.03em;position:relative;z-index:10001;font-family:'Inter',-apple-system,sans-serif}
.eco-banner-inner{display:flex;align-items:center;justify-content:center;gap:0.4rem;max-width:1200px;margin:0 auto}
.eco-banner-label{color:#6b6b8a;font-weight:500;margin-right:0.3rem}
.eco-banner a{color:#6b6b8a;text-decoration:none;font-weight:600;padding:2px 8px;border-radius:4px;transition:all 0.2s}
.eco-banner a:hover{color:#e8e8f0;background:rgba(255,255,255,0.06)}
.eco-banner a.eco-grip{color:#4dabf7}
.eco-banner a.eco-game{color:#20c997}
.eco-banner a.eco-grip:hover{background:rgba(77,171,247,0.12)}
.eco-banner a.eco-game:hover{background:rgba(32,201,151,0.12)}
.eco-dot{color:rgba(255,255,255,0.15);font-size:0.5rem}
.eco-banner-close{position:absolute;right:1rem;top:50%;transform:translateY(-50%);background:none;border:none;color:#6b6b8a;cursor:pointer;font-size:0.8rem;padding:2px 6px;border-radius:4px;transition:all 0.2s}
.eco-banner-close:hover{color:#e8e8f0;background:rgba(255,255,255,0.06)}
@media(max-width:768px){.eco-banner{padding:6px 1rem}.eco-banner-label{display:none}}

/* ── GG Ticker (LIVE stats) ── */
#gg-ticker .sb-shell{position:relative;background:rgba(7,7,15,0.96);backdrop-filter:blur(24px);border-bottom:1px solid rgba(255,255,255,0.07);overflow:hidden;box-shadow:0 8px 40px rgba(0,0,0,0.7)}
#gg-ticker .sb-scanlines{position:absolute;inset:0;background:repeating-linear-gradient(0deg,transparent,transparent 2px,rgba(255,255,255,0.007) 2px,rgba(255,255,255,0.007) 4px);pointer-events:none;z-index:1}
#gg-ticker .sb-neon-top{position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,#00d4ff 25%,#bf5fff 50%,#00d4ff 75%,transparent);opacity:.7;animation:sb-neon-pulse 3s ease infinite}
#gg-ticker .sb-neon-bot{position:absolute;bottom:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,#bf5fff 25%,#00d4ff 50%,#bf5fff 75%,transparent);opacity:.35;animation:sb-neon-pulse 3s ease infinite 1.5s}
#gg-ticker .sb-inner{display:flex;align-items:center;justify-content:center;gap:0;padding:0;height:52px;position:relative;z-index:2}
#gg-ticker .sb-brand{display:flex;align-items:center;gap:8px;padding:0 20px;border-right:1px solid rgba(255,255,255,0.08);height:100%;flex-shrink:0}
#gg-ticker .sb-brand-dot{width:6px;height:6px;border-radius:50%;background:#00d4ff;box-shadow:0 0 8px #00d4ff;animation:sb-dot-pulse 2s ease infinite}
#gg-ticker .sb-brand-text{font-family:'DM Mono','Courier New',monospace;font-size:9px;letter-spacing:.18em;text-transform:uppercase;color:#00d4ff;font-weight:700;text-shadow:0 0 12px rgba(0,212,255,0.6)}
#gg-ticker .sb-stats{display:flex;align-items:center;gap:0;height:100%;flex:1;justify-content:center}
#gg-ticker .sb-stat{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:0 28px;height:100%;border-right:1px solid rgba(255,255,255,0.05);min-width:130px}
#gg-ticker .sb-stat:last-child{border-right:none}
#gg-ticker .sb-stat-num{font-family:'Inter',sans-serif;font-size:20px;line-height:1;letter-spacing:.04em;font-weight:800;color:#f0f0f8;transition:color .3s ease}
#gg-ticker .sb-stat-num.accent-cyan{color:#00d4ff;text-shadow:0 0 10px rgba(0,212,255,0.3)}
#gg-ticker .sb-stat-num.accent-purple{color:#bf5fff;text-shadow:0 0 10px rgba(191,95,255,0.3)}
#gg-ticker .sb-stat-num.accent-green{color:#00D26A;text-shadow:0 0 10px rgba(0,210,106,0.3)}
#gg-ticker .sb-stat-num.accent-gold{color:#FFD700;text-shadow:0 0 10px rgba(255,215,0,0.3)}
#gg-ticker .sb-stat-label{font-family:'DM Mono','Courier New',monospace;font-size:7px;letter-spacing:.14em;text-transform:uppercase;color:rgba(255,255,255,0.35);margin-top:3px}
#gg-ticker .sb-powered{display:flex;align-items:center;gap:6px;padding:0 16px;border-left:1px solid rgba(255,255,255,0.08);height:100%;flex-shrink:0}
#gg-ticker .sb-powered-text{font-family:'DM Mono','Courier New',monospace;font-size:7px;letter-spacing:.1em;color:rgba(255,255,255,0.2)}
#gg-ticker .sb-powered-text a{color:rgba(255,255,255,0.35)!important;text-decoration:none}
#gg-ticker .sb-powered-text a:hover{color:#00d4ff!important}
@media(max-width:768px){#gg-ticker .sb-stat{padding:0 14px;min-width:90px}#gg-ticker .sb-stat-num{font-size:16px}#gg-ticker .sb-brand{padding:0 12px}#gg-ticker .sb-powered{display:none}}
@media(max-width:480px){#gg-ticker .sb-inner{flex-wrap:wrap;height:auto}#gg-ticker .sb-brand{width:100%;justify-content:center;height:28px;border-right:none;border-bottom:1px solid rgba(255,255,255,0.05)}#gg-ticker .sb-stats{flex-wrap:wrap;padding:6px 0}#gg-ticker .sb-stat{padding:6px 12px;min-width:80px;border-right:none}}

/* ── WS Nav ── */
.ws-nav{background:rgba(11,14,23,0.95);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border-bottom:1px solid var(--card-border,#1e2235);padding:0 2rem;position:sticky;top:0;z-index:100;font-family:'Inter',-apple-system,sans-serif}
.ws-nav-inner{max-width:1200px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;height:56px}
.ws-nav-brand{display:flex;align-items:center;gap:0.5rem;text-decoration:none;color:var(--text,#e8e8f0)}
.ws-nav-icon{width:28px;height:28px;background:linear-gradient(135deg,#4dabf7,#845ef7);border-radius:6px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;color:#fff}
.ws-nav-title{font-weight:700;font-size:0.95rem}
.ws-nav-badge{background:rgba(255,145,0,0.15);color:#ff9100;font-size:0.65rem;font-weight:600;padding:2px 6px;border-radius:4px;letter-spacing:0.05em}
.ws-nav-links{display:flex;gap:0.5rem}
.ws-nav-links a{color:var(--dim,#6b6b8a);text-decoration:none;font-size:0.82rem;font-weight:500;padding:6px 10px;border-radius:6px;transition:all 0.2s}
.ws-nav-links a:hover,.ws-nav-links a.active{color:var(--text,#e8e8f0);background:rgba(255,255,255,0.06)}
.ws-nav-signin{color:#e8e8f0!important;background:rgba(77,171,247,0.15);font-weight:600!important;margin-left:0.5rem}
.ws-nav-signin:hover{background:rgba(77,171,247,0.25)!important}
.ws-nav-toggle{display:none;background:none;border:none;cursor:pointer;width:30px;height:22px;position:relative;padding:0;z-index:10002}
.ws-nav-toggle span{display:block;width:100%;height:2px;background:var(--text,#e8e8f0);border-radius:2px;position:absolute;left:0;transition:all 0.3s}
.ws-nav-toggle span:nth-child(1){top:0}
.ws-nav-toggle span:nth-child(2){top:10px}
.ws-nav-toggle span:nth-child(3){top:20px}
.ws-nav-toggle.open span:nth-child(1){transform:translateY(10px) rotate(45deg)}
.ws-nav-toggle.open span:nth-child(2){opacity:0}
.ws-nav-toggle.open span:nth-child(3){transform:translateY(-10px) rotate(-45deg)}
.ws-nav-drawer{position:fixed;top:0;right:-320px;width:300px;max-width:85vw;height:100vh;background:var(--bg,#0B0E17);border-left:1px solid var(--card-border,#1e2235);z-index:10001;transition:right 0.3s ease;padding:5rem 1.5rem 2rem;overflow-y:auto}
.ws-nav-drawer.open{right:0}
.ws-nav-drawer a{display:block;padding:0.8rem 1rem;color:var(--dim,#6b6b8a);text-decoration:none;font-size:0.95rem;font-weight:600;border-radius:6px;transition:all 0.2s;margin-bottom:0.2rem}
.ws-nav-drawer a:hover{color:var(--text,#e8e8f0);background:rgba(255,255,255,0.04)}
.ws-nav-backdrop{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:10000}
.ws-nav-backdrop.open{display:block}
@media(max-width:768px){.ws-nav{padding:0 1rem}.ws-nav-links{display:none}.ws-nav-toggle{display:block}}
</style>

<div class="sb-powered"><span class="sb-powered-text">Powered by GripAi</span></div></div></div></div>

<div class="container">
  <div class="hero">
    <h1>In-Game Issue Overlay</h1>
    <p>A compact, real-time feed of active issues and fixes — designed to sit on top of your game. Know about problems before they hit you.</p>
  </div>

  <div class="game-search">
    <input type="text" id="gameFilter" placeholder="Filter by game name..." oninput="filterAlerts()">
    <span class="icon">🔍</span>
  </div>

  <p class="preview-label">Live Preview</p>

  <div class="overlay-container">
    <div class="game-bg-label">Your Game Here</div>

    <div class="overlay-widget" id="overlayWidget">
      <div class="widget-header">
        <div class="widget-header-left">
          <span class="widget-status"></span>
          <span class="widget-logo"><span>Game</span><span>Grip</span></span>
        </div>
        <button class="widget-minimize" title="Minimize">—</button>
      </div>
      <div class="widget-alerts" id="alertList">
        <div style="padding:1rem;text-align:center;color:var(--text-dim);font-size:0.8rem;">Loading alerts...</div>
      </div>
      <div class="widget-footer">
        <a href="/sandbox">gripnews.uk/sandbox</a>
      </div>
    </div>
  </div>

  <!-- How it works -->
  <div class="how-section">
    <h2 class="how-title">How It Works</h2>
    <div class="how-grid">
      <div class="how-card">
        <div class="icon">🕷️</div>
        <h3>Crawl</h3>
        <p>142 sources monitored continuously for new bugs, crashes, and performance issues.</p>
      </div>
      <div class="how-card">
        <div class="icon">🧠</div>
        <h3>Triage</h3>
        <p>AI scores every issue by severity, affected platforms, and player impact.</p>
      </div>
      <div class="how-card">
        <div class="icon">🔔</div>
        <h3>Alert</h3>
        <p>Relevant issues surface in your overlay in real-time. Fixes included when available.</p>
      </div>
    </div>
  </div>

  <div class="cta-bar">
    <p>The overlay is coming soon as a standalone desktop widget. Want early access?</p>
    <a href="https://gripai.uk#pricing" class="cta-btn">Join the Waitlist →</a>
  </div>
</div>

<footer class="footer">
  <p>Powered by GripAi · <a href="https://gripai.uk/api">Grip Protocol</a> · <a href="https://gripai.uk">GripAi</a></p>
  <p style="margin-top:0.5rem;">© 2026 <a href="https://gripai.uk">GripAi</a>. All rights reserved.</p>
  <p><a href="https://www.facebook.com/share/1AgMJFGwUU/" target="_blank">Facebook</a></p>
</footer>


<script>
/* ── Gaming Overlay Logic ────────────────────────────────────── */
const API_BASE = "https://gripai.uk/api";
let allAlerts = [];

function sevLabel(sev) {
  if (typeof sev === 'string' && ['critical','high','medium','low'].includes(sev)) return sev;
  const n = parseFloat(sev);
  if (isNaN(n)) return 'medium';
  if (n >= 0.80) return 'critical';
  if (n >= 0.60) return 'high';
  if (n >= 0.40) return 'medium';
  return 'low';
}

function formatTime(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  const diffH = Math.floor((Date.now() - d) / 3600000);
  if (diffH < 1) return 'now';
  if (diffH < 24) return diffH + 'h';
  return Math.floor(diffH / 24) + 'd';
}

function renderAlerts(items) {
  const list = document.getElementById('alertList');
  if (!items.length) {
    list.innerHTML = '<div style="padding:1rem;text-align:center;color:var(--text-dim);font-size:0.8rem;">No matching issues ✅</div>';
    return;
  }
  list.innerHTML = items.slice(0, 15).map(i => {
    const sev = sevLabel(i.severity);
    return `
    <div class="alert-item" data-game="${(i.game_name||'').toLowerCase()}">
      <div class="alert-top">
        <span class="alert-sev ${sev}"></span>
        <span class="alert-game">${i.game_name || 'Unknown'}</span>
        <span class="alert-time">${formatTime(i.created_at)}</span>
      </div>
      <div class="alert-desc">${i.category ? i.category.charAt(0).toUpperCase() + i.category.slice(1) : 'Issue'} — ${i.title || i.summary || 'Investigating'}</div>
    </div>`;
  }).join('');
}

function filterAlerts() {
  const q = document.getElementById('gameFilter').value.toLowerCase().trim();
  if (!q) return renderAlerts(allAlerts);
  renderAlerts(allAlerts.filter(i => (i.game_name || '').toLowerCase().includes(q)));
}

async function loadOverlay() {
  try {
    const res = await fetch(`${API_BASE}/issues`).then(r => r.json());
    const issues = res.issues || [];
    // Sort by severity weight then date
    const sevWeight = { critical: 4, high: 3, medium: 2, low: 1 };
    allAlerts = issues.sort((a, b) => (sevWeight[sevLabel(b.severity)] || 0) - (sevWeight[sevLabel(a.severity)] || 0));
    renderAlerts(allAlerts);
  } catch (err) {
    document.getElementById('alertList').innerHTML = '<div style="padding:1rem;text-align:center;color:var(--danger);font-size:0.8rem;">Connection failed</div>';
  }
}

loadOverlay();
setInterval(loadOverlay, 60000);
(function(){function ggTkAnimate(el,target){var start=0,dur=1200,sT=null;function step(ts){if(!sT)sT=ts;var p=Math.min((ts-sT)/dur,1);var ease=1-Math.pow(1-p,3);el.textContent=Math.round(start+(target-start)*ease).toLocaleString();if(p<1)requestAnimationFrame(step);}requestAnimationFrame(step);}function ggTkLoad(){fetch("https://gripai.uk/Jaffa/stats").then(function(r){return r.json();}).then(function(d){ggTkAnimate(document.getElementById("ggTkIncidents"),d.incidents||0);ggTkAnimate(document.getElementById("ggTkHotspots"),d.hotspots||0);ggTkAnimate(document.getElementById("ggTkGames"),d.games||0);}).catch(function(){var e=document.getElementById("ggTkIncidents");if(e)e.textContent="\u2014";var h=document.getElementById("ggTkHotspots");if(h)h.textContent="\u2014";var g=document.getElementById("ggTkGames");if(g)g.textContent="\u2014";});}ggTkLoad();setInterval(ggTkLoad,60000);})();
function toggleNav(){var t=document.getElementById('navToggle'),d=document.getElementById('navDrawer'),b=document.getElementById('navBackdrop');if(!t||!d||!b)return;var o=d.classList.toggle('open');t.classList.toggle('open');b.classList.toggle('open');document.body.style.overflow=o?'hidden':'';}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
