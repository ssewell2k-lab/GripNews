<?php
/**
 * GripNews.uk — Hardware Intelligence
 * Migrated from sandbox/hardware.html
 */
require_once __DIR__ . '/includes/functions.php';

$page_title = "Hardware Intelligence — GripNews";
$page_desc  = "Live hardware tracking and performance intelligence across gaming platforms.";
$nav_active = 'hardware';

require_once __DIR__ . '/includes/header.php';
?>

<style>
/* ── Hardware Intelligence Styles ───────────────────────────────────── */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;700&display=swap');
:root {
  --bg: #0B0E17; --card: #12151f; --card-border: #1e2235;
  --text: #e8e8f0; --dim: #6b6b8a; --accent: #4dabf7;
  --red: #ff4d4d; --orange: #ff9100; --yellow: #ffd700; --green: #00d084;
  --purple: #845ef7; --gpu-color: #4dabf7; --cpu-color: #ff9100; --ram-color: #00d084;
}
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Inter', -apple-system, sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }

/* ── Nav ── */
.hw-nav { background: rgba(11,14,23,0.95); backdrop-filter: blur(20px); border-bottom: 1px solid var(--card-border); padding: 0 2rem; position: sticky; top: 0; z-index: 100; }
.hw-nav-inner { max-width: 1200px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; height: 56px; }
.hw-nav-brand { display: flex; align-items: center; gap: 0.5rem; text-decoration: none; color: var(--text); }
.hw-nav-icon { width: 28px; height: 28px; background: linear-gradient(135deg, var(--accent), var(--purple)); border-radius: 6px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; color: #fff; }
.hw-nav-title { font-weight: 700; font-size: 0.95rem; }
.hw-nav-badge { background: rgba(77,171,247,0.15); color: var(--accent); font-size: 0.65rem; font-weight: 600; padding: 2px 6px; border-radius: 4px; letter-spacing: 0.05em; }
.hw-nav-links { display: flex; gap: 0.5rem; }
.hw-nav-links a { color: var(--dim); text-decoration: none; font-size: 0.82rem; font-weight: 500; padding: 6px 10px; border-radius: 6px; transition: all 0.2s; }
.hw-nav-links a:hover, .hw-nav-links a.active { color: var(--text); background: rgba(255,255,255,0.06); }

/* ── Container ── */
.hw-wrap { max-width: 1200px; margin: 0 auto; padding: 2rem 1.5rem; }

/* ── Header ── */
.hw-header { margin-bottom: 2rem; }
.hw-header h1 { font-size: 1.8rem; font-weight: 800; margin-bottom: 0.4rem; }
.hw-header h1 span { background: linear-gradient(90deg, var(--gpu-color), var(--cpu-color), var(--ram-color)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.hw-header p { color: var(--dim); font-size: 0.9rem; line-height: 1.6; }

/* ── Stats Row ── */
.hw-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.75rem; margin-bottom: 1.5rem; }
.hw-stat { background: var(--card); border: 1px solid var(--card-border); border-radius: 10px; padding: 1rem; text-align: center; }
.hw-stat-value { font-size: 1.6rem; font-weight: 800; font-family: 'JetBrains Mono', monospace; }
.hw-stat-label { font-size: 0.72rem; color: var(--dim); margin-top: 0.2rem; text-transform: uppercase; letter-spacing: 0.05em; }
.stat-gpu { color: var(--gpu-color); }
.stat-cpu { color: var(--cpu-color); }
.stat-ram { color: var(--ram-color); }
.stat-total { color: var(--purple); }
.stat-critical { color: var(--red); }
.stat-crawled { color: var(--accent); }

/* ── Filters ── */
.hw-filters { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1.5rem; align-items: center; }
.hw-search { flex: 1; min-width: 200px; background: var(--card); border: 1px solid var(--card-border); border-radius: 8px; padding: 0.6rem 1rem; color: var(--text); font-size: 0.85rem; font-family: inherit; outline: none; transition: border-color 0.2s; }
.hw-search:focus { border-color: var(--accent); }
.hw-search::placeholder { color: var(--dim); }
.hw-select { background: var(--card); border: 1px solid var(--card-border); border-radius: 8px; padding: 0.6rem 0.8rem; color: var(--text); font-size: 0.82rem; font-family: inherit; outline: none; cursor: pointer; appearance: none; -webkit-appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%236b6b8a'%3E%3Cpath d='M6 8L1 3h10z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 8px center; padding-right: 2rem; }
.hw-select:focus { border-color: var(--accent); }

/* ── Category Pills ── */
.hw-pills { display: flex; gap: 0.4rem; flex-wrap: wrap; }
.hw-pill { border: 1px solid var(--card-border); border-radius: 20px; padding: 0.35rem 0.85rem; font-size: 0.78rem; font-weight: 600; cursor: pointer; transition: all 0.2s; background: transparent; color: var(--dim); }
.hw-pill:hover { border-color: var(--accent); color: var(--text); }
.hw-pill.active { border-color: var(--accent); background: rgba(77,171,247,0.12); color: var(--accent); }
.hw-pill[data-cat="gpu"].active { border-color: var(--gpu-color); background: rgba(77,171,247,0.12); color: var(--gpu-color); }
.hw-pill[data-cat="cpu"].active { border-color: var(--cpu-color); background: rgba(255,145,0,0.12); color: var(--cpu-color); }
.hw-pill[data-cat="ram"].active { border-color: var(--ram-color); background: rgba(0,208,132,0.12); color: var(--ram-color); }

/* ── Issue List ── */
.hw-list { display: flex; flex-direction: column; gap: 0.5rem; }
.hw-issue { background: var(--card); border: 1px solid var(--card-border); border-radius: 10px; padding: 1rem 1.25rem; display: flex; gap: 1rem; align-items: flex-start; transition: all 0.2s; cursor: default; }
.hw-issue:hover { border-color: rgba(77,171,247,0.3); transform: translateY(-1px); }

.hw-issue-sev { width: 4px; min-height: 40px; border-radius: 2px; flex-shrink: 0; margin-top: 2px; }
.sev-critical { background: var(--red); box-shadow: 0 0 8px rgba(255,77,77,0.4); }
.sev-major { background: var(--orange); box-shadow: 0 0 8px rgba(255,145,0,0.3); }
.sev-minor { background: var(--yellow); }
.sev-cosmetic { background: var(--dim); }

.hw-issue-body { flex: 1; min-width: 0; }
.hw-issue-title { font-size: 0.88rem; font-weight: 600; line-height: 1.4; margin-bottom: 0.35rem; }
.hw-issue-title a { color: var(--text); text-decoration: none; }
.hw-issue-title a:hover { color: var(--accent); }
.hw-issue-desc { font-size: 0.78rem; color: var(--dim); line-height: 1.5; margin-bottom: 0.5rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.hw-issue-tags { display: flex; flex-wrap: wrap; gap: 0.35rem; align-items: center; }
.hw-tag { font-size: 0.68rem; font-weight: 600; padding: 2px 8px; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.03em; }
.tag-gpu { background: rgba(77,171,247,0.15); color: var(--gpu-color); }
.tag-cpu { background: rgba(255,145,0,0.15); color: var(--cpu-color); }
.tag-ram { background: rgba(0,208,132,0.15); color: var(--ram-color); }
.tag-general { background: rgba(107,107,138,0.2); color: var(--dim); }
.tag-sev { font-size: 0.65rem; padding: 2px 6px; border-radius: 3px; }
.tag-sev-critical { background: rgba(255,77,77,0.15); color: var(--red); }
.tag-sev-major { background: rgba(255,145,0,0.15); color: var(--orange); }
.tag-sev-minor { background: rgba(255,215,0,0.15); color: var(--yellow); }
.tag-sev-cosmetic { background: rgba(107,107,138,0.15); color: var(--dim); }
.tag-component { background: rgba(132,94,247,0.15); color: var(--purple); font-family: 'JetBrains Mono', monospace; font-size: 0.65rem; }
.tag-source { background: rgba(255,255,255,0.06); color: var(--dim); font-size: 0.65rem; }
.tag-fix { background: rgba(0,208,132,0.15); color: var(--green); }
.hw-issue-time { font-size: 0.68rem; color: var(--dim); margin-left: auto; white-space: nowrap; }

/* ── Loading & Empty ── */
.hw-loading { text-align: center; padding: 3rem; color: var(--dim); }
.hw-loading .spinner { width: 28px; height: 28px; border: 3px solid var(--card-border); border-top-color: var(--accent); border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 1rem; }
@keyframes spin { to { transform: rotate(360deg); } }
.hw-empty { text-align: center; padding: 3rem; color: var(--dim); }
.hw-empty h3 { font-size: 1.1rem; color: var(--text); margin-bottom: 0.5rem; }

/* ── Pagination ── */
.hw-pager { display: flex; justify-content: center; gap: 0.5rem; margin-top: 1.5rem; }
.hw-pager button { background: var(--card); border: 1px solid var(--card-border); border-radius: 6px; padding: 0.5rem 1rem; color: var(--text); font-size: 0.82rem; cursor: pointer; transition: all 0.2s; font-family: inherit; }
.hw-pager button:hover:not(:disabled) { border-color: var(--accent); }
.hw-pager button:disabled { opacity: 0.4; cursor: default; }
.hw-pager .pager-info { display: flex; align-items: center; font-size: 0.78rem; color: var(--dim); }

/* ── Footer ── */
.hw-footer { margin-top: 3rem; padding: 2rem 0; border-top: 1px solid var(--card-border); text-align: center; color: var(--dim); font-size: 0.75rem; }
.hw-footer a { color: var(--accent); text-decoration: none; }

/* ── Responsive ── */
@media (max-width: 768px) {
  .hw-nav { padding: 0 1rem; }
  .hw-nav-links { display: none; }
  .hw-wrap { padding: 1.5rem 1rem; }
  .hw-header h1 { font-size: 1.3rem; }
  .hw-stats { grid-template-columns: repeat(3, 1fr); }
  .hw-issue { flex-direction: column; gap: 0.5rem; }
  .hw-issue-sev { width: 100%; min-height: 3px; }
  .hw-issue-time { margin-left: 0; }
  .hw-filters { flex-direction: column; }
  .hw-search { width: 100%; }
}

/* ── Mobile Nav ── */
.hw-nav-toggle { display: none; background: none; border: none; cursor: pointer; width: 30px; height: 22px; position: relative; padding: 0; }
.hw-nav-toggle span { display: block; width: 100%; height: 2px; background: var(--text); border-radius: 2px; position: absolute; left: 0; transition: all 0.3s; }
.hw-nav-toggle span:nth-child(1) { top: 0; }
.hw-nav-toggle span:nth-child(2) { top: 10px; }
.hw-nav-toggle span:nth-child(3) { top: 20px; }
.hw-nav-toggle.open span:nth-child(1) { transform: translateY(10px) rotate(45deg); }
.hw-nav-toggle.open span:nth-child(2) { opacity: 0; }
.hw-nav-toggle.open span:nth-child(3) { transform: translateY(-10px) rotate(-45deg); }
.hw-nav-drawer { position: fixed; top: 0; right: -320px; width: 300px; max-width: 85vw; height: 100vh; background: var(--bg, #0B0E17); border-left: 1px solid var(--card-border, #1e2235); z-index: 10001; transition: right 0.3s ease; padding: 5rem 1.5rem 2rem; overflow-y: auto; }
.hw-nav-drawer.open { right: 0; }
.hw-nav-drawer a { display: block; padding: 0.8rem 1rem; color: var(--dim, #6b6b8a); text-decoration: none; font-size: 0.95rem; font-weight: 600; border-radius: 6px; transition: all 0.2s; margin-bottom: 0.2rem; }
.hw-nav-drawer a:hover { color: var(--text, #e8e8f0); background: rgba(255,255,255,0.04); }
.hw-nav-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 10000; }
.hw-nav-backdrop.open { display: block; }

@media (max-width: 768px) { .hw-nav-links { display: none; } .hw-nav-toggle { display: block; } }

/* Last-updated timestamp */
.ws-last-updated {
  text-align: center;
  margin: 8px 0 0 0;
  font-size: 0.78rem;
  color: rgba(255,255,255,0.4);
  font-family: 'JetBrains Mono', monospace;
  letter-spacing: 0.3px;
}
.ws-last-updated span {
  color: rgba(255,255,255,0.55);
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

<div class="hw-wrap">
  <div class="hw-header">
    <h1>🔧 <span>Hardware Intelligence</span></h1>
    <p>Real-time GPU, CPU & RAM issue tracking — crawled daily from 10 hardware sources. Search by component, severity, or keyword.</p>
    <p class="ws-last-updated">Last updated: <span id="lastUpdatedTime">—</span></p>
  </div>

  <!-- Stats -->
  <div class="hw-stats" id="hwStats">
    <div class="hw-stat"><div class="hw-stat-value stat-total" id="statTotal">—</div><div class="hw-stat-label">Total Issues</div></div>
    <div class="hw-stat"><div class="hw-stat-value stat-gpu" id="statGPU">—</div><div class="hw-stat-label">GPU Issues</div></div>
    <div class="hw-stat"><div class="hw-stat-value stat-cpu" id="statCPU">—</div><div class="hw-stat-label">CPU Issues</div></div>
    <div class="hw-stat"><div class="hw-stat-value stat-ram" id="statRAM">—</div><div class="hw-stat-label">RAM Issues</div></div>
    <div class="hw-stat"><div class="hw-stat-value stat-critical" id="statCritical">—</div><div class="hw-stat-label">Critical</div></div>
    <div class="hw-stat"><div class="hw-stat-value stat-crawled" id="statCrawled">—</div><div class="hw-stat-label">Sources Crawled</div></div>
  </div>

  <!-- Filters -->
  <div class="hw-filters">
    <input type="text" class="hw-search" id="hwSearch" placeholder="Search issues — RTX 4090, BSOD, DDR5, driver crash...">
    <div class="hw-pills" id="hwPills">
      <button class="hw-pill active" data-cat="">All</button>
      <button class="hw-pill" data-cat="gpu">🖥️ GPU</button>
      <button class="hw-pill" data-cat="cpu">⚡ CPU</button>
      <button class="hw-pill" data-cat="ram">💾 RAM</button>
    </div>
    <select class="hw-select" id="hwSeverity">
      <option value="">All Severity</option>
      <option value="critical">🔴 Critical</option>
      <option value="major">🟠 Major</option>
      <option value="minor">🟡 Minor</option>
      <option value="cosmetic">⚪ Cosmetic</option>
    </select>
    <select class="hw-select" id="hwSource">
      <option value="">All Sources</option>
    </select>
  </div>

  <!-- Issue List -->
  <div id="hwList">
    <div class="hw-loading"><div class="spinner"></div>Loading hardware issues...</div>
  </div>

  <!-- Pagination -->
  <div class="hw-pager" id="hwPager" style="display:none;">
    <button id="prevBtn" onclick="changePage(-1)">← Prev</button>
    <div class="pager-info" id="pagerInfo"></div>
    <button id="nextBtn" onclick="changePage(1)">Next →</button>
  </div>

  <div class="hw-footer">
    <p>Hardware Intelligence by <a href="https://gripai.uk">GripAi</a> · Crawled daily from 10+ sources · <a href="https://gripai.uk/patches">Patch Notes</a></p>
    <p style="margin-top:0.5rem">Data powers <a href="https://gripai.uk/api">GripAi API</a> · <a href="https://gripai.uk">GripAi</a> · <a href="https://gripai.uk">Grip Protocol</a></p>
  </div>
</div>


<script>
/* ── Hardware Intelligence Logic ────────────────────────────────────── */
const API = 'https://gripai.uk/Jaffa/hardware';
let currentPage = 0;
const PAGE_SIZE = 30;
let totalIssues = 0;

function timeAgo(dateStr) {
  const diff = Date.now() - new Date(dateStr).getTime();
  const mins = Math.floor(diff / 60000);
  if (mins < 60) return mins + 'm ago';
  const hrs = Math.floor(mins / 60);
  if (hrs < 24) return hrs + 'h ago';
  const days = Math.floor(hrs / 24);
  if (days < 7) return days + 'd ago';
  return new Date(dateStr).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
}

function catTag(cat) {
  const cls = { gpu: 'tag-gpu', cpu: 'tag-cpu', ram: 'tag-ram' }[cat] || 'tag-general';
  return `<span class="hw-tag ${cls}">${(cat || 'general').toUpperCase()}</span>`;
}
function sevTag(sev) {
  return `<span class="hw-tag tag-sev tag-sev-${sev}">${sev}</span>`;
}

function renderIssue(issue) {
  const desc = issue.description ? issue.description.substring(0, 200) : '';
  const comp = issue.component ? `<span class="hw-tag tag-component">${issue.component}</span>` : '';
  const src = issue.source ? `<span class="hw-tag tag-source">${issue.source}</span>` : '';
  const fix = issue.is_fix ? `<span class="hw-tag tag-fix">✓ FIX</span>` : '';
  const link = issue.source_url ? `<a href="${issue.source_url}" target="_blank" rel="noopener">${issue.title || 'Untitled'}</a>` : (issue.title || 'Untitled');
  
  return `<div class="hw-issue">
    <div class="hw-issue-sev sev-${issue.severity || 'minor'}"></div>
    <div class="hw-issue-body">
      <div class="hw-issue-title">${link}</div>
      ${desc ? `<div class="hw-issue-desc">${desc}</div>` : ''}
      <div class="hw-issue-tags">
        ${catTag(issue.category)}
        ${sevTag(issue.severity || 'minor')}
        ${comp}${src}${fix}
      </div>
    </div>
    <div class="hw-issue-time">${issue.created_at ? timeAgo(issue.created_at) : ''}</div>
  </div>`;
}

async function loadStats() {
  try {
    const r = await fetch(API + '/stats');
    const d = await r.json();
    document.getElementById('statTotal').textContent = d.totals?.total_issues || 0;
    document.getElementById('statCrawled').textContent = d.totals?.total_results || 0;

    const cats = {};
    (d.by_category || []).forEach(c => cats[c.category] = c.count);
    document.getElementById('statGPU').textContent = cats.gpu || 0;
    document.getElementById('statCPU').textContent = cats.cpu || 0;
    document.getElementById('statRAM').textContent = cats.ram || 0;

    const crit = (d.by_severity || []).find(s => s.severity === 'critical');
    document.getElementById('statCritical').textContent = crit?.count || 0;

    // Populate source dropdown
    const sel = document.getElementById('hwSource');
    (d.by_source || []).forEach(s => {
      const opt = document.createElement('option');
      opt.value = s.source;
      opt.textContent = `${s.source} (${s.count})`;
      sel.appendChild(opt);
    });
  } catch (e) {
    console.error('Stats error:', e);
  }
}

async function loadIssues() {
  const list = document.getElementById('hwList');
  const pager = document.getElementById('hwPager');
  
  const q = document.getElementById('hwSearch').value.trim();
  const cat = document.querySelector('.hw-pill.active')?.dataset.cat || '';
  const sev = document.getElementById('hwSeverity').value;
  const src = document.getElementById('hwSource').value;

  const params = new URLSearchParams();
  if (q) params.set('q', q);
  if (cat) params.set('category', cat);
  if (sev) params.set('severity', sev);
  if (src) params.set('source', src);
  params.set('limit', PAGE_SIZE);
  params.set('offset', currentPage * PAGE_SIZE);

  list.innerHTML = '<div class="hw-loading"><div class="spinner"></div>Loading...</div>';

  try {
    const r = await fetch(API + '/issues?' + params.toString());
    const d = await r.json();
    totalIssues = d.total || 0;

    if (!d.issues || !d.issues.length) {
      list.innerHTML = '<div class="hw-empty"><h3>No issues found</h3><p>Try a different search or filter.</p></div>';
      pager.style.display = 'none';
      return;
    }

    list.innerHTML = '<div class="hw-list">' + d.issues.map(renderIssue).join('') + '</div>';
    
    // Pagination
    const totalPages = Math.ceil(totalIssues / PAGE_SIZE);
    if (totalPages > 1) {
      pager.style.display = 'flex';
      document.getElementById('prevBtn').disabled = currentPage === 0;
      document.getElementById('nextBtn').disabled = currentPage >= totalPages - 1;
      document.getElementById('pagerInfo').textContent = `Page ${currentPage + 1} of ${totalPages} · ${totalIssues} issues`;
    } else {
      pager.style.display = 'none';
    }
  } catch (e) {
    list.innerHTML = '<div class="hw-empty"><h3>⚠️ Connection Error</h3><p>Could not reach the hardware intelligence API.</p></div>';
    pager.style.display = 'none';
  }
}

function changePage(delta) {
  currentPage += delta;
  if (currentPage < 0) currentPage = 0;
  loadIssues();
  window.scrollTo({ top: 200, behavior: 'smooth' });
}

// Category pill clicks
document.querySelectorAll('.hw-pill').forEach(pill => {
  pill.addEventListener('click', () => {
    document.querySelectorAll('.hw-pill').forEach(p => p.classList.remove('active'));
    pill.classList.add('active');
    currentPage = 0;
    loadIssues();
  });
});

// Filter changes
let searchTimer;
document.getElementById('hwSearch').addEventListener('input', () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => { currentPage = 0; loadIssues(); }, 400);
});
document.getElementById('hwSeverity').addEventListener('change', () => { currentPage = 0; loadIssues(); });
document.getElementById('hwSource').addEventListener('change', () => { currentPage = 0; loadIssues(); });

// Init
loadStats();
loadIssues();
function hwToggleNav(){var t=document.getElementById("hwNavToggle"),d=document.getElementById("hwNavDrawer"),b=document.getElementById("hwNavBackdrop");if(t&&d&&b){d.classList.toggle("open");b.classList.toggle("open");t.classList.toggle("open");}}
// Set last-updated timestamp when data loads
function setLastUpdated() {
  var el = document.getElementById('lastUpdatedTime');
  if (el) {
    var now = new Date();
    var opts = { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: false };
    el.textContent = now.toLocaleDateString('en-GB', opts).replace(',', ' —');
  }
}

window.addEventListener('load', function() { setTimeout(setLastUpdated, 1500); });

(function(){function ggTkAnimate(el,target){var start=0,dur=1200,sT=null;function step(ts){if(!sT)sT=ts;var p=Math.min((ts-sT)/dur,1);var ease=1-Math.pow(1-p,3);el.textContent=Math.round(start+(target-start)*ease).toLocaleString();if(p<1)requestAnimationFrame(step);}requestAnimationFrame(step);}function ggTkLoad(){fetch("https://gripai.uk/Jaffa/stats").then(function(r){return r.json();}).then(function(d){ggTkAnimate(document.getElementById("ggTkIncidents"),d.incidents||0);ggTkAnimate(document.getElementById("ggTkHotspots"),d.hotspots||0);ggTkAnimate(document.getElementById("ggTkGames"),d.games||0);}).catch(function(){var e=document.getElementById("ggTkIncidents");if(e)e.textContent="\u2014";var h=document.getElementById("ggTkHotspots");if(h)h.textContent="\u2014";var g=document.getElementById("ggTkGames");if(g)g.textContent="\u2014";});}ggTkLoad();setInterval(ggTkLoad,60000);})();
function toggleNav(){var t=document.getElementById('navToggle'),d=document.getElementById('navDrawer'),b=document.getElementById('navBackdrop');if(!t||!d||!b)return;var o=d.classList.toggle('open');t.classList.toggle('open');b.classList.toggle('open');document.body.style.overflow=o?'hidden':'';}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
