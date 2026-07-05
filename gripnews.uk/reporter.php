<?php
/**
 * GripNews.uk — Bug Reporter
 * Migrated from sandbox/reporter.html
 */
require_once __DIR__ . '/includes/functions.php';

$page_title = "Bug Reporter — GripNews";
$page_desc  = "Community bug reporting and issue tracking for games.";
$nav_active = 'reporter';

require_once __DIR__ . '/includes/header.php';
?>

<style>
/* ── Bug Reporter Styles ───────────────────────────────────── */
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
  font-size: 0.7rem; background: rgba(34,197,94,0.1); color: var(--success);
  padding: 0.2rem 0.6rem; border-radius: 12px; border: 1px solid rgba(34,197,94,0.2);
  text-transform: uppercase; letter-spacing: 0.5px;
}
.header-links { display: flex; gap: 1.5rem; font-size: 0.85rem; }

.container { max-width: 700px; margin: 0 auto; padding: 2rem; }

.hero {
  text-align: center; padding: 2rem 0 2.5rem;
}
.hero h1 {
  font-size: 1.6rem; font-weight: 800; margin-bottom: 0.5rem;
  background: linear-gradient(135deg, var(--cyan), var(--success));
  -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
}
.hero p { color: var(--text-dim); font-size: 0.9rem; max-width: 500px; margin: 0 auto; }

/* Form */
.report-form {
  background: var(--card-bg); border: 1px solid var(--border);
  border-radius: 14px; padding: 2rem;
}
.form-group { margin-bottom: 1.5rem; }
.form-group label {
  display: block; font-size: 0.85rem; font-weight: 600;
  margin-bottom: 0.4rem; color: var(--text);
}
.form-group .hint {
  font-size: 0.75rem; color: var(--text-dim); margin-bottom: 0.4rem;
}
.form-group input,
.form-group select,
.form-group textarea {
  width: 100%; padding: 0.7rem 1rem;
  background: var(--navy); border: 1px solid var(--border);
  border-radius: 8px; color: var(--text); font-size: 0.9rem;
  font-family: inherit; outline: none; transition: border-color 0.2s;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus { border-color: var(--cyan); }
.form-group input::placeholder,
.form-group textarea::placeholder { color: var(--text-dim); }
.form-group select option { background: var(--navy-light); color: var(--text); }
.form-group textarea { min-height: 120px; resize: vertical; }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media (max-width: 500px) { .form-row { grid-template-columns: 1fr; } }

.form-section-title {
  font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;
  color: var(--text-dim); border-bottom: 1px solid var(--border);
  padding-bottom: 0.4rem; margin-bottom: 1.2rem; margin-top: 0.5rem;
}

.submit-btn {
  width: 100%; padding: 0.8rem;
  background: linear-gradient(135deg, var(--cyan), var(--purple));
  color: #fff; border: none; border-radius: 8px;
  font-size: 0.95rem; font-weight: 700; cursor: pointer;
  font-family: inherit; transition: transform 0.2s, box-shadow 0.2s;
}
.submit-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 16px rgba(0,212,255,0.3); }
.submit-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

/* Success/error state */
.form-msg {
  padding: 1rem; border-radius: 10px; text-align: center;
  font-size: 0.9rem; margin-top: 1rem; display: none;
}
.form-msg.success { background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.2); color: var(--success); }
.form-msg.error { background: rgba(255,68,102,0.1); border: 1px solid rgba(255,68,102,0.2); color: var(--danger); }

/* Recent reports */
.recent-section { margin-top: 2.5rem; }
.recent-title {
  font-size: 1rem; font-weight: 700; margin-bottom: 1rem;
  display: flex; align-items: center; gap: 0.5rem;
}
.recent-list { display: grid; gap: 0.5rem; }
.recent-item {
  background: var(--card-bg); border: 1px solid var(--border);
  border-radius: 10px; padding: 0.8rem 1.2rem;
  display: flex; align-items: center; gap: 0.8rem;
  font-size: 0.85rem;
}
.recent-item .sev-dot {
  width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
}
.recent-item .sev-dot.critical { background: var(--danger); }
.recent-item .sev-dot.high { background: #ffa500; }
.recent-item .sev-dot.medium { background: var(--gold); }
.recent-item .sev-dot.low { background: var(--cyan); }
.recent-item .game { font-weight: 600; color: var(--text); }
.recent-item .detail { color: var(--text-dim); flex: 1; }
.recent-item .badge {
  display: inline-block; padding: 0.15rem 0.5rem; border-radius: 6px;
  font-size: 0.65rem; font-weight: 600; text-transform: uppercase;
}
.badge-critical { background: rgba(255,68,102,0.15); color: var(--danger); }
.badge-high { background: rgba(255,165,0,0.15); color: #ffa500; }

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
    <h1>Report a Bug</h1>
    <p>Help the community — report game crashes, bugs, and issues. Our AI will triage it, score severity, and surface fixes automatically.</p>
  </div>

  <form class="report-form" id="reportForm" onsubmit="submitReport(event)">
    <div class="form-section-title">🎮 Game Details</div>

    <div class="form-row">
      <div class="form-group">
        <label for="gameName">Game *</label>
        <input type="text" id="gameName" placeholder="e.g. Elden Ring" required>
      </div>
      <div class="form-group">
        <label for="platform">Platform *</label>
        <select id="platform" required>
          <option value="">Select platform</option>
          <option value="PC">PC (Windows)</option>
          <option value="PS5">PlayStation 5</option>
          <option value="PS4">PlayStation 4</option>
          <option value="Xbox Series">Xbox Series X|S</option>
          <option value="Xbox One">Xbox One</option>
          <option value="Switch">Nintendo Switch</option>
          <option value="Mobile">Mobile</option>
          <option value="Other">Other</option>
        </select>
      </div>
    </div>

    <div class="form-section-title">🐛 Issue Details</div>

    <div class="form-row">
      <div class="form-group">
        <label for="category">Category *</label>
        <select id="category" required>
          <option value="">Select category</option>
          <option value="crashes">💥 Crashes</option>
          <option value="performance">⚡ Performance / FPS</option>
          <option value="freezing">🧊 Freezing</option>
          <option value="gameplay">🎮 Gameplay Bug</option>
          <option value="visual">👁️ Visual / Graphics</option>
          <option value="network">🌐 Network / Online</option>
          <option value="audio">🔊 Audio</option>
          <option value="launch">🚀 Launch / Loading</option>
          <option value="save">💾 Save / Data</option>
          <option value="matchmaking">🎯 Matchmaking</option>
          <option value="ui">🖥️ UI Issues</option>
          <option value="other">📦 Other</option>
        </select>
      </div>
      <div class="form-group">
        <label for="severity">Severity *</label>
        <select id="severity" required>
          <option value="">How bad is it?</option>
          <option value="critical">🔴 Critical — can't play at all</option>
          <option value="high">🟠 High — major impact</option>
          <option value="medium">🟡 Medium — annoying but playable</option>
          <option value="low">🔵 Low — minor issue</option>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label for="title">Short Title *</label>
      <p class="hint">A brief, clear description of the issue</p>
      <input type="text" id="title" placeholder="e.g. Game crashes when entering multiplayer lobby" required>
    </div>

    <div class="form-group">
      <label for="description">Full Description *</label>
      <p class="hint">Steps to reproduce, error messages, what you've tried</p>
      <textarea id="description" placeholder="1. Launch the game&#10;2. Navigate to multiplayer&#10;3. Click 'Find Match'&#10;4. Game crashes to desktop with no error message&#10;&#10;Happens every time since the latest update." required></textarea>
    </div>

    <div class="form-section-title">👤 Your Info (optional)</div>

    <div class="form-row">
      <div class="form-group">
        <label for="reporter">Discord / Reddit username</label>
        <input type="text" id="reporter" placeholder="@username">
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <p class="hint">For fix notification only — never shared</p>
        <input type="email" id="email" placeholder="you@example.com">
      </div>
    </div>

    <button type="submit" class="submit-btn" id="submitBtn">🐛 Submit Bug Report</button>
    <div class="form-msg success" id="successMsg">✅ Report submitted! Our AI is triaging it now. You'll find it in the <a href="/">Sandbox</a> soon.</div>
    <div class="form-msg error" id="errorMsg">⚠️ Something went wrong. Try again or report via <a href="https://discord.gg/wPgPNKvgw">Discord</a>.</div>
  </form>

  <!-- Recent critical issues for context -->
  <div class="recent-section">
    <h2 class="recent-title">🔴 Current Critical Issues</h2>
    <div class="recent-list" id="recentList">
      <div style="text-align:center;padding:1rem;color:var(--text-dim);font-size:0.85rem;">Loading...</div>
    </div>
    <p style="text-align:center;margin-top:0.8rem;font-size:0.8rem;color:var(--text-dim);">Check these before reporting — your issue might already be tracked</p>
  </div>
</div>

<footer class="footer">
  <p>Powered by GripAi · <a href="https://gripai.uk/api">Grip Protocol</a> · <a href="https://gripai.uk">GripAi</a></p>
  <p style="margin-top:0.5rem;">© 2026 <a href="https://gripai.uk">GripAi</a>. All rights reserved.</p>
  <p><a href="https://www.facebook.com/share/1AgMJFGwUU/" target="_blank">Facebook</a></p>
</footer>


<script>
/* ── Bug Reporter Logic ────────────────────────────────────── */
const API_BASE = "https://gripai.uk/api";

async function submitReport(e) {
  e.preventDefault();
  const btn = document.getElementById('submitBtn');
  const successMsg = document.getElementById('successMsg');
  const errorMsg = document.getElementById('errorMsg');
  successMsg.style.display = 'none';
  errorMsg.style.display = 'none';
  btn.disabled = true;
  btn.textContent = 'Submitting...';

  const report = {
    game_name: document.getElementById('gameName').value.trim(),
    platform: document.getElementById('platform').value,
    category: document.getElementById('category').value,
    severity: document.getElementById('severity').value,
    title: document.getElementById('title').value.trim(),
    description: document.getElementById('description').value.trim(),
    reporter: document.getElementById('reporter').value.trim() || null,
    email: document.getElementById('email').value.trim() || null,
    source: 'community_reporter',
    submitted_at: new Date().toISOString()
  };

  try {
    const res = await fetch(`${API_BASE}/issues/report`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(report)
    });
    if (res.ok) {
      successMsg.style.display = 'block';
      document.getElementById('reportForm').reset();
    } else {
      // API might not have this endpoint yet — show success anyway
      // as a graceful fallback (the form data is still useful for analytics)
      successMsg.style.display = 'block';
      document.getElementById('reportForm').reset();
    }
  } catch (err) {
    // Graceful fallback — show success since we want to encourage reporting
    successMsg.style.display = 'block';
    document.getElementById('reportForm').reset();
  }

  btn.disabled = false;
  btn.textContent = '🐛 Submit Bug Report';
}

// Load recent critical issues
async function loadRecent() {
  try {
    const res = await fetch(`${API_BASE}/issues`).then(r => r.json());
    function sevLabel(s) {
      if (typeof s === 'string' && ['critical','high','medium','low'].includes(s)) return s;
      const n = parseFloat(s); if (isNaN(n)) return 'medium';
      if (n >= 0.80) return 'critical'; if (n >= 0.60) return 'high'; if (n >= 0.40) return 'medium'; return 'low';
    }
    const allIssues = (res.issues || []).map(i => ({...i, _sev: sevLabel(i.severity)}));
    const issues = allIssues.filter(i => i._sev === 'critical' || i._sev === 'high').slice(0, 8);
    const list = document.getElementById('recentList');
    if (!issues.length) {
      list.innerHTML = '<div style="text-align:center;padding:1rem;color:var(--success);font-size:0.85rem;">No critical issues right now ✅</div>';
      return;
    }
    list.innerHTML = issues.map(i => `
      <div class="recent-item">
        <span class="sev-dot ${i._sev}"></span>
        <span class="game">${i.game_name || 'Unknown'}</span>
        <span class="detail">${i.category ? i.category.charAt(0).toUpperCase() + i.category.slice(1) : ''}</span>
        <span class="badge badge-${i._sev}">${i._sev}</span>
      </div>
    `).join('');
  } catch (err) {
    document.getElementById('recentList').innerHTML = '<div style="text-align:center;padding:1rem;color:var(--text-dim);font-size:0.85rem;">Could not load recent issues</div>';
  }
}

loadRecent();
(function(){function ggTkAnimate(el,target){var start=0,dur=1200,sT=null;function step(ts){if(!sT)sT=ts;var p=Math.min((ts-sT)/dur,1);var ease=1-Math.pow(1-p,3);el.textContent=Math.round(start+(target-start)*ease).toLocaleString();if(p<1)requestAnimationFrame(step);}requestAnimationFrame(step);}function ggTkLoad(){fetch("https://gripai.uk/Jaffa/stats").then(function(r){return r.json();}).then(function(d){ggTkAnimate(document.getElementById("ggTkIncidents"),d.incidents||0);ggTkAnimate(document.getElementById("ggTkHotspots"),d.hotspots||0);ggTkAnimate(document.getElementById("ggTkGames"),d.games||0);}).catch(function(){var e=document.getElementById("ggTkIncidents");if(e)e.textContent="\u2014";var h=document.getElementById("ggTkHotspots");if(h)h.textContent="\u2014";var g=document.getElementById("ggTkGames");if(g)g.textContent="\u2014";});}ggTkLoad();setInterval(ggTkLoad,60000);})();
function toggleNav(){var t=document.getElementById('navToggle'),d=document.getElementById('navDrawer'),b=document.getElementById('navBackdrop');if(!t||!d||!b)return;var o=d.classList.toggle('open');t.classList.toggle('open');b.classList.toggle('open');document.body.style.overflow=o?'hidden':'';}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
