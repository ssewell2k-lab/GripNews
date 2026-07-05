<?php
/**
 * GripNews.uk — Game Issue Monitor
 * Migrated from sandbox/monitor.html
 */
require_once __DIR__ . '/includes/functions.php';

$page_title = "Game Issue Monitor — GripNews";
$page_desc  = "Real-time game issue monitoring. Track crashes, bugs, and performance across 500+ games.";
$nav_active = 'monitor';

require_once __DIR__ . '/includes/header.php';
?>

<style>
/* ── Game Issue Monitor Styles ───────────────────────────────────── */
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
  line-height: 1.6;
  min-height: 100vh;
}
a { color: var(--cyan); text-decoration: none; transition: color 0.2s; }
a:hover { color: var(--purple); }

.header {
  background: linear-gradient(135deg, var(--navy) 0%, var(--navy-mid) 100%);
  border-bottom: 1px solid var(--border);
  padding: 1rem 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: sticky; top: 0; z-index: 100;
  backdrop-filter: blur(12px);
}
.header-left { display: flex; align-items: center; gap: 1rem; }
.logo { font-size: 1.5rem; font-weight: 800; text-decoration: none; }
.logo span:first-child { color: var(--cyan); }
.logo span:last-child { color: var(--text); }
.header-tag {
  font-size: 0.7rem;
  background: rgba(0,212,255,0.1);
  color: var(--cyan);
  padding: 0.2rem 0.6rem;
  border-radius: 12px;
  border: 1px solid rgba(0,212,255,0.2);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.header-links { display: flex; gap: 1.5rem; font-size: 0.85rem; align-items: center; }

.container { max-width: 1200px; margin: 0 auto; padding: 2rem; }

/* Status Bar */
.status-bar {
  display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 2rem;
}
.status-card {
  flex: 1; min-width: 150px;
  background: var(--card-bg); border: 1px solid var(--border);
  border-radius: 12px; padding: 1.2rem; text-align: center;
  position: relative; overflow: hidden;
}
.status-card::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
}
.status-card.cyan::before { background: var(--cyan); }
.status-card.danger::before { background: var(--danger); }
.status-card.gold::before { background: var(--gold); }
.status-card.success::before { background: var(--success); }
.status-card.purple::before { background: var(--purple); }
.status-card .value {
  font-size: 2rem; font-weight: 800;
  font-variant-numeric: tabular-nums;
}
.status-card.cyan .value { color: var(--cyan); }
.status-card.danger .value { color: var(--danger); }
.status-card.gold .value { color: var(--gold); }
.status-card.success .value { color: var(--success); }
.status-card.purple .value { color: var(--purple); }
.status-card .label {
  font-size: 0.7rem; color: var(--text-dim);
  text-transform: uppercase; letter-spacing: 0.5px; margin-top: 0.2rem;
}

/* Live Feed */
.section-title {
  font-size: 1.1rem; font-weight: 700; margin-bottom: 1rem;
  display: flex; align-items: center; gap: 0.5rem;
}
.live-dot {
  width: 8px; height: 8px; border-radius: 50%; background: var(--success);
  animation: pulse 2s infinite;
}
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.4; }
}

.feed-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
@media (max-width: 768px) { .feed-grid { grid-template-columns: 1fr; } }

.feed-panel {
  background: var(--card-bg); border: 1px solid var(--border);
  border-radius: 12px; overflow: hidden;
}
.feed-panel-header {
  padding: 0.8rem 1.2rem;
  border-bottom: 1px solid var(--border);
  font-size: 0.85rem; font-weight: 600;
  display: flex; align-items: center; gap: 0.5rem;
}
.feed-panel-body {
  padding: 0; max-height: 400px; overflow-y: auto;
}
.feed-panel-body::-webkit-scrollbar { width: 4px; }
.feed-panel-body::-webkit-scrollbar-thumb { background: #333; border-radius: 4px; }

.feed-item {
  padding: 0.8rem 1.2rem;
  border-bottom: 1px solid rgba(30,38,64,0.5);
  display: flex; align-items: center; gap: 0.8rem;
  transition: background 0.2s;
  font-size: 0.85rem;
}
.feed-item:last-child { border-bottom: none; }
.feed-item:hover { background: rgba(0,212,255,0.03); }
.feed-item .sev-dot {
  width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
}
.feed-item .sev-dot.critical { background: var(--danger); }
.feed-item .sev-dot.high { background: #ffa500; }
.feed-item .sev-dot.medium { background: var(--gold); }
.feed-item .sev-dot.low { background: var(--cyan); }
.feed-item .game { font-weight: 600; color: var(--text); flex: 1; }
.feed-item .category { color: var(--text-dim); font-size: 0.75rem; }
.feed-item .time { color: var(--text-dim); font-size: 0.7rem; white-space: nowrap; }

.badge {
  display: inline-block; padding: 0.15rem 0.5rem; border-radius: 6px;
  font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px;
}
.badge-critical { background: rgba(255,68,102,0.15); color: var(--danger); }
.badge-high { background: rgba(255,165,0,0.15); color: #ffa500; }
.badge-medium { background: rgba(255,215,0,0.15); color: var(--gold); }
.badge-low { background: rgba(0,212,255,0.15); color: var(--cyan); }

/* Severity breakdown */
.sev-breakdown {
  display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem;
  margin-bottom: 2rem;
}
.sev-item {
  background: var(--card-bg); border: 1px solid var(--border);
  border-radius: 10px; padding: 0.8rem; text-align: center;
}
.sev-item .count { font-size: 1.4rem; font-weight: 800; font-variant-numeric: tabular-nums; }
.sev-item .sev-label { font-size: 0.65rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.4px; margin-top: 0.15rem; }
.sev-item.critical .count { color: var(--danger); }
.sev-item.high .count { color: #ffa500; }
.sev-item.medium .count { color: var(--gold); }
.sev-item.low .count { color: var(--cyan); }

/* Heatmap */
.heatmap-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  gap: 0.4rem; margin-bottom: 2rem;
}
.heatmap-cell {
  background: var(--card-bg); border: 1px solid var(--border);
  border-radius: 8px; padding: 0.6rem 0.8rem;
  display: flex; justify-content: space-between; align-items: center;
  font-size: 0.78rem; transition: border-color 0.2s;
}
.heatmap-cell:hover { border-color: rgba(0,212,255,0.3); }
.heatmap-cell .name { color: var(--text); font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.heatmap-cell .count { font-weight: 700; font-variant-numeric: tabular-nums; }

/* Footer */
.footer {
  text-align: center; padding: 2rem;
  border-top: 1px solid var(--border); margin-top: 2rem;
  font-size: 0.8rem; color: var(--text-dim);
}

/* Refresh bar */
.refresh-bar {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 1.5rem; padding: 0.6rem 1rem;
  background: var(--card-bg); border: 1px solid var(--border);
  border-radius: 10px; font-size: 0.8rem;
}
.refresh-bar .last-updated { color: var(--text-dim); }
.refresh-bar .last-updated strong { color: var(--text); }
.refresh-btn {
  background: rgba(0,212,255,0.1); border: 1px solid rgba(0,212,255,0.2);
  color: var(--cyan); padding: 0.3rem 0.8rem; border-radius: 6px;
  cursor: pointer; font-size: 0.75rem; font-family: inherit; transition: all 0.2s;
}
.refresh-btn:hover { background: rgba(0,212,255,0.2); }

.loading { text-align: center; padding: 3rem; color: var(--text-dim); }
.loading .spinner {
  width: 30px; height: 30px;
  border: 3px solid var(--border); border-top-color: var(--cyan);
  border-radius: 50%; animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}
@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 600px) {
  .sev-breakdown { grid-template-columns: repeat(2, 1fr); }
  .status-bar { flex-direction: column; }
  .header { padding: 1rem; flex-wrap: wrap; gap: 0.5rem; }
  .header-links { gap: 1rem; font-size: 0.75rem; }
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

<h1 style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap">Game Issue Monitor — Live Crash &amp; Bug Tracking Dashboard</h1>
<div class="sb-powered"><span class="sb-powered-text">Powered by GripAi</span></div></div></div></div>

<div class="container">
  <div class="refresh-bar">
    <span class="last-updated">Last refreshed: <strong id="lastRefresh">—</strong></span>
    <button class="refresh-btn" onclick="loadData()">↻ Refresh</button>
  </div>

  <!-- Top-level stats -->
  <div class="status-bar" id="statusBar">
    <div class="status-card cyan">
      <div class="value" id="totalIssues">—</div>
      <div class="label">Total Issues</div>
    </div>
    <div class="status-card danger">
      <div class="value" id="criticalCount">—</div>
      <div class="label">Critical</div>
    </div>
    <div class="status-card gold">
      <div class="value" id="fixGuides">—</div>
      <div class="label">Fix Guides</div>
    </div>
    <div class="status-card success">
      <div class="value" id="enrichedCount">—</div>
      <div class="label">Enriched</div>
    </div>
    <div class="status-card purple">
      <div class="value" id="gameCount">—</div>
      <div class="label">Games Tracked</div>
    </div>
  </div>

  <!-- Severity breakdown -->
  <div class="section-title"><span class="live-dot"></span> Severity Breakdown</div>
  <div class="sev-breakdown" id="sevBreakdown">
    <div class="sev-item critical"><div class="count" id="sevCritical">—</div><div class="sev-label">Critical</div></div>
    <div class="sev-item high"><div class="count" id="sevHigh">—</div><div class="sev-label">High</div></div>
    <div class="sev-item medium"><div class="count" id="sevMedium">—</div><div class="sev-label">Medium</div></div>
    <div class="sev-item low"><div class="count" id="sevLow">—</div><div class="sev-label">Low</div></div>
  </div>

  <!-- Live feed panels -->
  <div class="feed-grid">
    <div class="feed-panel">
      <div class="feed-panel-header">🔴 Critical & High Issues</div>
      <div class="feed-panel-body" id="criticalFeed">
        <div class="loading"><div class="spinner"></div>Loading...</div>
      </div>
    </div>
    <div class="feed-panel">
      <div class="feed-panel-header">📝 Latest Fix Guides</div>
      <div class="feed-panel-body" id="guideFeed">
        <div class="loading"><div class="spinner"></div>Loading...</div>
      </div>
    </div>
  </div>

  <!-- Game heatmap -->
  <div class="section-title" style="margin-top:2rem;">🎮 Most Affected Games</div>
  <div class="heatmap-grid" id="heatmap">
    <div class="loading"><div class="spinner"></div></div>
  </div>

  <!-- Category distribution -->
  <div class="section-title">📊 Issues by Category</div>
  <div class="heatmap-grid" id="categoryGrid">
    <div class="loading"><div class="spinner"></div></div>
  </div>
</div>

<footer class="footer">
  <p>Powered by GripAi · <a href="https://gripai.uk/api">Grip Protocol</a> · <a href="https://gripai.uk">GripAi</a></p>
  <p style="margin-top:0.5rem;">© 2026 <a href="https://gripai.uk">GripAi</a>. All rights reserved.</p>
  <p><a href="https://www.facebook.com/share/1AgMJFGwUU/" target="_blank">Facebook</a></p>
</footer>


<script>
/* ── Game Issue Monitor Logic ────────────────────────────────────── */
const API_BASE = "https://gripai.uk/api";

function sevLabel(sev) {
  if (typeof sev === 'string' && ['critical','high','medium','low'].includes(sev)) return sev;
  const n = parseFloat(sev);
  if (isNaN(n)) return 'medium';
  if (n >= 0.80) return 'critical';
  if (n >= 0.60) return 'high';
  if (n >= 0.40) return 'medium';
  return 'low';
}

const CATEGORY_ICONS = {
  crashes: '💥', performance: '⚡', gameplay: '🎮', visual: '👁️',
  network: '🌐', audio: '🔊', freezing: '🧊', matchmaking: '🎯',
  save: '💾', launch: '🚀', progression: '📈', ui: '🖥️', other: '📦'
};

function formatTime(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  const now = new Date();
  const diffMs = now - d;
  const diffH = Math.floor(diffMs / 3600000);
  if (diffH < 1) return 'Just now';
  if (diffH < 24) return diffH + 'h ago';
  const diffD = Math.floor(diffH / 24);
  return diffD + 'd ago';
}

async function loadData() {
  try {
    const [issuesRes, blogRes] = await Promise.all([
      fetch(`${API_BASE}/issues`).then(r => r.json()).catch(() => ({ issues: [], count: 0 })),
      fetch(`${API_BASE}/blog`).then(r => r.json()).catch(() => ({ posts: [], count: 0 })),
    ]);

    const issues = issuesRes.issues || [];
    const posts = blogRes.posts || [];

    // Stats
    document.getElementById('totalIssues').textContent = issuesRes.count || issues.length;
    document.getElementById('fixGuides').textContent = blogRes.count || posts.length;
    document.getElementById('enrichedCount').textContent = issuesRes.enriched || issues.filter(i => ['enriched','complete','blog_ready','published'].includes(i.pipeline_status)).length;
    document.getElementById('criticalCount').textContent = issuesRes.critical || issues.filter(i => sevLabel(i.severity) === 'critical').length;

    const games = new Set(issues.map(i => i.game_name).filter(Boolean));
    document.getElementById('gameCount').textContent = games.size;

    // Normalize severity on all issues
    issues.forEach(i => { i._sev = sevLabel(i.severity); });

    // Severity breakdown
    const sevCounts = { critical: 0, high: 0, medium: 0, low: 0 };
    issues.forEach(i => { sevCounts[i._sev]++; });
    document.getElementById('sevCritical').textContent = sevCounts.critical;
    document.getElementById('sevHigh').textContent = sevCounts.high;
    document.getElementById('sevMedium').textContent = sevCounts.medium;
    document.getElementById('sevLow').textContent = sevCounts.low;

    // Critical feed
    const critical = issues.filter(i => i._sev === 'critical' || i._sev === 'high').slice(0, 20);
    const critFeed = document.getElementById('criticalFeed');
    if (critical.length === 0) {
      critFeed.innerHTML = '<div style="padding:1.5rem;text-align:center;color:var(--text-dim);">No critical issues right now ✅</div>';
    } else {
      critFeed.innerHTML = critical.map(i => `
        <div class="feed-item">
          <span class="sev-dot ${i._sev}"></span>
          <span class="game">${i.game_name || 'Unknown'}</span>
          <span class="badge badge-${i._sev}">${i._sev}</span>
          <span class="category">${i.category || ''}</span>
          <span class="time">${formatTime(i.created_at)}</span>
        </div>
      `).join('');
    }

    // Guide feed
    const seenTitles = new Set();
    const uniquePosts = posts.filter(p => {
      const key = (p.title || '').toLowerCase();
      if (seenTitles.has(key)) return false;
      seenTitles.add(key);
      return true;
    });
    const guideFeed = document.getElementById('guideFeed');
    guideFeed.innerHTML = uniquePosts.slice(0, 20).map(p => {
      const rawSev = p.severity || (issues.find(i => i.id === p.issue_id) || {}).severity || 'medium';
      const sev = sevLabel(rawSev);
      return `
        <div class="feed-item">
          <span class="sev-dot ${sev}"></span>
          <a class="game" href="/sandbox/#guide/${p.slug}" style="color:var(--text);">${p.title || 'Untitled'}</a>
          <span class="badge badge-${sev}">${sev}</span>
          <span class="time">${formatTime(p.created_at)}</span>
        </div>
      `;
    }).join('');

    // Game heatmap
    const gameCounts = {};
    issues.forEach(i => {
      const name = i.game_name || 'Unknown';
      gameCounts[name] = (gameCounts[name] || 0) + 1;
    });
    const sortedGames = Object.entries(gameCounts).sort((a, b) => b[1] - a[1]).slice(0, 24);
    const maxCount = sortedGames[0] ? sortedGames[0][1] : 1;
    document.getElementById('heatmap').innerHTML = sortedGames.map(([name, count]) => {
      const intensity = Math.max(0.15, count / maxCount);
      const color = count >= maxCount * 0.7 ? 'var(--danger)' : count >= maxCount * 0.4 ? 'var(--gold)' : 'var(--cyan)';
      return `<div class="heatmap-cell" style="border-color:${color.replace('var(', 'rgba(').replace(')', ',0.3)')};"><span class="name">${name}</span><span class="count" style="color:${color}">${count}</span></div>`;
    }).join('');

    // Category distribution
    const catCounts = {};
    issues.forEach(i => {
      const cat = i.category || 'other';
      catCounts[cat] = (catCounts[cat] || 0) + 1;
    });
    const sortedCats = Object.entries(catCounts).sort((a, b) => b[1] - a[1]);
    document.getElementById('categoryGrid').innerHTML = sortedCats.map(([cat, count]) => {
      const icon = CATEGORY_ICONS[cat] || '📦';
      return `<div class="heatmap-cell"><span class="name">${icon} ${cat.charAt(0).toUpperCase() + cat.slice(1)}</span><span class="count" style="color:var(--cyan)">${count}</span></div>`;
    }).join('');

    document.getElementById('lastRefresh').textContent = new Date().toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });

  } catch (err) {
    document.getElementById('criticalFeed').innerHTML = '<div style="padding:1.5rem;text-align:center;color:var(--danger);">Failed to connect to API</div>';
  }
}

loadData();
// Auto-refresh every 60 seconds
setInterval(loadData, 60000);
(function(){function ggTkAnimate(el,target){var start=0,dur=1200,sT=null;function step(ts){if(!sT)sT=ts;var p=Math.min((ts-sT)/dur,1);var ease=1-Math.pow(1-p,3);el.textContent=Math.round(start+(target-start)*ease).toLocaleString();if(p<1)requestAnimationFrame(step);}requestAnimationFrame(step);}function ggTkLoad(){fetch("https://gripai.uk/Jaffa/stats").then(function(r){return r.json();}).then(function(d){ggTkAnimate(document.getElementById("ggTkIncidents"),d.incidents||0);ggTkAnimate(document.getElementById("ggTkHotspots"),d.hotspots||0);ggTkAnimate(document.getElementById("ggTkGames"),d.games||0);}).catch(function(){var e=document.getElementById("ggTkIncidents");if(e)e.textContent="\u2014";var h=document.getElementById("ggTkHotspots");if(h)h.textContent="\u2014";var g=document.getElementById("ggTkGames");if(g)g.textContent="\u2014";});}ggTkLoad();setInterval(ggTkLoad,60000);})();
function toggleNav(){var t=document.getElementById('navToggle'),d=document.getElementById('navDrawer'),b=document.getElementById('navBackdrop');if(!t||!d||!b)return;var o=d.classList.toggle('open');t.classList.toggle('open');b.classList.toggle('open');document.body.style.overflow=o?'hidden':'';}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
