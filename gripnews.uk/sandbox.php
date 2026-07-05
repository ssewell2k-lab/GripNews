<?php
/**
 * GripNews.uk — Bug Tracker & Fix Guides
 * Migrated from sandbox/index.html
 */
require_once __DIR__ . '/includes/functions.php';

$page_title = "Bug Tracker & Fix Guides — GripNews";
$page_desc  = "AI-powered game crash fixes, bug tracking, and diagnostics. Community-verified fix guides across 170+ games.";
$nav_active = 'sandbox';

require_once __DIR__ . '/includes/header.php';
?>

<style>
/* ── Bug Tracker & Fix Guides Styles ───────────────────────────────────── */
:root {
  --bg-primary: #0B0E17;
  --bg-card: #141824;
  --bg-card-alt: #1a2030;
  --border: rgba(59, 130, 246, 0.2);
  --border-hover: rgba(59, 130, 246, 0.5);
  --text-primary: #e2e8f0;
  --text-secondary: #94a3b8;
  --text-muted: #64748b;
  --accent: #3b82f6;
  --accent-glow: rgba(59, 130, 246, 0.12);
  --accent2: #06b6d4;
  --red: #ef4444;
  --orange: #f97316;
  --yellow: #eab308;
  --green: #22c55e;
  --blue: #3b82f6;
  --cyan: #06b6d4;
  --purple: #a855f7;
}
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: var(--bg-primary); color: var(--text-primary);
  min-height: 100vh; line-height: 1.5;
}

/* ─── Header ─── */
.header {
  background: linear-gradient(135deg, #0f1525 0%, #0B0E17 100%);
  border-bottom: 1px solid var(--border);
  padding: 16px 24px; position: sticky; top: 0; z-index: 100;
  backdrop-filter: blur(10px);
}
.header-inner {
  max-width: 1400px; margin: 0 auto;
  display: flex; align-items: center; justify-content: space-between;
  flex-wrap: wrap; gap: 12px;
}
.brand { display: flex; align-items: center; gap: 12px; }
.brand-icon { font-size: 28px; }
.brand h1 {
  font-size: 20px; font-weight: 700;
  background: linear-gradient(135deg, var(--accent), var(--cyan));
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
}
.brand small { color: var(--text-muted); font-size: 12px; display: block; font-weight: 400; -webkit-text-fill-color: var(--text-muted); }
.header-meta { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
.live-dot {
  width: 8px; height: 8px; border-radius: 50%; background: var(--green);
  animation: pulse-dot 2s infinite; display: inline-block;
}
@keyframes pulse-dot {
  0%, 100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.6); }
  50% { box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
}
.status-badge {
  display: flex; align-items: center; gap: 6px;
  padding: 4px 12px; border-radius: 20px;
  background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.3);
  font-size: 12px; color: var(--green);
}
.refresh-info { color: var(--text-muted); font-size: 11px; }
.powered-by { color: var(--text-muted); font-size: 11px; }
.powered-by a { color: var(--accent); text-decoration: none; }
.header-nav { display: flex; gap: 6px; }
.header-nav a {
  color: var(--text-secondary); text-decoration: none; font-size: 12px;
  padding: 4px 10px; border-radius: 6px; border: 1px solid transparent;
  transition: all 0.2s;
}
.header-nav a:hover { border-color: var(--border); color: var(--text-primary); }
.main { max-width: 1400px; margin: 0 auto; padding: 20px 24px 40px; }


  background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px;
  padding: 12px 20px; margin-bottom: 20px; overflow: hidden; position: relative;
}
  content: ''; position: absolute; top: 0; bottom: 0; width: 60px; z-index: 2; pointer-events: none;
}
  
  gap: 40px; white-space: nowrap;
}
.sev-critical { background: rgba(239,68,68,0.2); color: var(--red); }
.sev-high { background: rgba(249,115,22,0.2); color: var(--orange); }
.sev-medium { background: rgba(234,179,8,0.2); color: var(--yellow); }
.sev-low { background: rgba(34,197,94,0.2); color: var(--green); }

/* ─── Stat Cards ─── */
.stats-grid {
  display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px; margin-bottom: 24px;
}
.stat-card {
  background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px;
  padding: 20px; transition: all 0.3s ease; cursor: pointer; position: relative;
  user-select: none;
}
.stat-card::after {
  content: '▶'; position: absolute; bottom: 10px; right: 14px;
  font-size: 10px; color: var(--text-muted); opacity: 0; transition: opacity 0.3s;
}
.stat-card:hover::after { opacity: 1; }
.stat-card:hover {
  border-color: var(--border-hover); transform: translateY(-2px);
  box-shadow: 0 8px 25px var(--accent-glow);
}
.stat-card:active { transform: translateY(0); }
.stat-card.active {
  border-color: var(--accent); box-shadow: 0 0 20px var(--accent-glow);
}
.stat-card .label { color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
.stat-card .value { font-size: 32px; font-weight: 700; font-variant-numeric: tabular-nums; }
.stat-card .sub { color: var(--text-secondary); font-size: 12px; margin-top: 4px; }
.stat-card .tap-hint { color: var(--text-muted); font-size: 10px; margin-top: 6px; opacity: 0; transition: opacity 0.3s; }
.stat-card:hover .tap-hint { opacity: 1; }
.stat-card.critical .value { color: var(--red); }
.stat-card.high .value { color: var(--orange); }
.stat-card.enriched .value { color: var(--green); }
.stat-card.total .value { color: var(--accent); }
.stat-card.guides .value { color: var(--cyan); }
.stat-card.weekly .value { color: var(--purple); }
.stat-card.games .value { color: var(--yellow); }
.stat-card.categories .value { color: var(--blue); }

/* ─── Drill-down Panel ─── */
.drill-panel {
  background: var(--bg-card); border: 1px solid var(--accent);
  border-radius: 12px; margin-bottom: 24px;
  overflow: hidden; display: none;
  animation: slideDown 0.3s ease;
  box-shadow: 0 4px 30px var(--accent-glow);
}
.drill-panel.show { display: block; }
@keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
.drill-header {
  padding: 16px 20px; border-bottom: 1px solid var(--border);
  display: flex; align-items: center; justify-content: space-between;
}
.drill-header h2 { font-size: 15px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
.drill-close {
  background: none; border: 1px solid var(--border); border-radius: 8px;
  color: var(--text-muted); cursor: pointer; padding: 4px 12px; font-size: 12px;
  transition: all 0.2s;
}
.drill-close:hover { border-color: var(--red); color: var(--red); }
.drill-body { padding: 16px 20px; max-height: 500px; overflow-y: auto; }
.drill-body::-webkit-scrollbar { width: 4px; }
.drill-body::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

/* ─── Content Grid ─── */
.content-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; }
@media (max-width: 900px) { .content-grid { grid-template-columns: 1fr; } }

/* ─── Panels ─── */
.panel { background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
.panel-header {
  padding: 16px 20px; border-bottom: 1px solid var(--border);
  display: flex; align-items: center; justify-content: space-between;
}
.panel-header h2 { font-size: 15px; font-weight: 600; }
.panel-header .badge {
  padding: 2px 10px; border-radius: 10px; background: var(--accent-glow);
  border: 1px solid var(--border); font-size: 11px; color: var(--accent);
}
.panel-body { padding: 16px 20px; max-height: 420px; overflow-y: auto; }
.panel-body::-webkit-scrollbar { width: 4px; }
.panel-body::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

/* ─── Category Bars ─── */
.cat-row { display: flex; align-items: center; gap: 12px; padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,0.03); }
.cat-row:last-child { border-bottom: none; }
.cat-icon { font-size: 18px; width: 28px; text-align: center; }
.cat-info { flex: 1; min-width: 0; }
.cat-name { font-size: 13px; font-weight: 500; text-transform: capitalize; }
.cat-bar-track { height: 6px; background: rgba(255,255,255,0.05); border-radius: 3px; margin-top: 4px; }
.cat-bar-fill { height: 100%; border-radius: 3px; background: linear-gradient(90deg, var(--accent), var(--cyan)); transition: width 0.8s ease; }
.cat-count { font-size: 13px; font-weight: 600; color: var(--accent); min-width: 30px; text-align: right; }

/* ─── Report Table ─── */
.report-table { width: 100%; border-collapse: collapse; }
.report-table th {
  text-align: left; padding: 10px 12px; font-size: 11px; text-transform: uppercase;
  color: var(--text-muted); letter-spacing: 0.5px; border-bottom: 1px solid var(--border);
  position: sticky; top: 0; background: var(--bg-card);
}
.report-table td { padding: 10px 12px; font-size: 13px; border-bottom: 1px solid rgba(255,255,255,0.03); color: var(--text-secondary); }
.report-table tr:hover td { background: var(--accent-glow); }
.report-title { max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--text-primary); }
.pill { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
.pill-critical { background: rgba(239,68,68,0.15); color: var(--red); }
.pill-high { background: rgba(249,115,22,0.15); color: var(--orange); }
.pill-medium { background: rgba(234,179,8,0.15); color: var(--yellow); }
.pill-low { background: rgba(34,197,94,0.15); color: var(--green); }
.pill-enriched { background: rgba(34,197,94,0.15); color: var(--green); }
.pill-info { background: rgba(59,130,246,0.15); color: #60a5fa; }

/* ─── Fix Guide Cards ─── */
.guides-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 16px;
}
.guide-card {
  background: var(--bg-card-alt); border: 1px solid var(--border); border-radius: 10px;
  padding: 18px; transition: all 0.3s ease; cursor: pointer;
}
.guide-card:hover {
  border-color: var(--border-hover); transform: translateY(-2px);
  box-shadow: 0 6px 20px var(--accent-glow);
}
.guide-top { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; flex-wrap: wrap; }
.guide-time { color: var(--text-muted); font-size: 11px; margin-left: auto; }
.guide-title { font-size: 14px; font-weight: 600; color: var(--text-primary); margin-bottom: 8px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.guide-excerpt { font-size: 13px; color: var(--text-secondary); line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 6; -webkit-box-orient: vertical; overflow: hidden; }
.guides-load-more {
  display: block; width: 100%; padding: 12px; margin-top: 16px;
  background: var(--bg-card-alt); border: 1px solid var(--border); border-radius: 8px;
  color: var(--accent); cursor: pointer; font-size: 13px; text-align: center;
  transition: all 0.2s;
}
.guides-load-more:hover { border-color: var(--accent); background: var(--accent-glow); }

/* ─── Issue Detail Modal ─── */
.modal-overlay {
  display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center;
  padding: 20px; backdrop-filter: blur(4px);
}
.modal-overlay.show { display: flex; }
.modal {
  background: var(--bg-card); border: 1px solid var(--border); border-radius: 16px;
  max-width: 600px; width: 100%; max-height: 80vh; overflow-y: auto;
  padding: 24px; animation: slideDown 0.3s ease;
}
.modal-close {
  float: right; background: none; border: 1px solid var(--border); border-radius: 8px;
  color: var(--text-muted); cursor: pointer; padding: 4px 12px; font-size: 14px;
}
.modal-close:hover { border-color: var(--red); color: var(--red); }
.modal h3 { font-size: 18px; margin-bottom: 12px; padding-right: 40px; line-height: 1.4; }
.modal-meta { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
.modal-body { font-size: 14px; line-height: 1.7; color: var(--text-secondary); }
.modal-body p { margin-bottom: 12px; }

/* ─── Severity Doughnut ─── */
.chart-container { display: flex; align-items: center; justify-content: center; gap: 24px; padding: 10px 0; flex-wrap: wrap; }
.doughnut-wrap { position: relative; width: 160px; height: 160px; }
.doughnut-wrap canvas { width: 160px; height: 160px; }
.doughnut-center { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; }
.doughnut-center .big { font-size: 28px; font-weight: 700; color: var(--text-primary); }
.doughnut-center .small { font-size: 10px; color: var(--text-muted); }
.legend { display: flex; flex-direction: column; gap: 6px; }
.legend-item { display: flex; align-items: center; gap: 8px; font-size: 13px; }
.legend-dot { width: 12px; height: 12px; border-radius: 3px; }
.legend-count { margin-left: auto; font-weight: 600; padding-left: 12px; }

/* ─── Agent Panel ─── */
.agent-panel { grid-column: 1 / -1; }
.agent-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; }
.agent-stat { background: var(--bg-card-alt); border-radius: 8px; padding: 14px; text-align: center; }
.agent-stat .val { font-size: 22px; font-weight: 700; color: var(--accent); }
.agent-stat .lbl { font-size: 11px; color: var(--text-muted); margin-top: 2px; }

/* ─── Footer ─── */
.footer { text-align: center; padding: 20px; color: var(--text-muted); font-size: 12px; border-top: 1px solid var(--border); max-width: 1400px; margin: 0 auto; }
.footer a { color: var(--accent); text-decoration: none; }

/* ─── Utilities ─── */
.loading { display: flex; align-items: center; justify-content: center; padding: 40px; color: var(--text-muted); }
.spinner { width: 20px; height: 20px; border: 2px solid var(--border); border-top-color: var(--accent); border-radius: 50%; animation: spin 0.8s linear infinite; margin-right: 10px; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ─── Cookie banner ─── */
.cookie-banner {
  display: none; position: fixed; bottom: 0; left: 0; right: 0; z-index: 200;
  background: var(--bg-card); border-top: 1px solid var(--border);
  padding: 14px 24px; text-align: center; font-size: 13px;
}
.cookie-banner.show { display: block; }
.cookie-banner button {
  margin-left: 12px; padding: 6px 16px; border-radius: 6px;
  border: 1px solid var(--accent); background: var(--accent); color: #fff;
  cursor: pointer; font-size: 12px;
}
.cookie-banner .btn-reject { background: transparent; color: var(--text-muted); border-color: var(--border); }

@media (max-width: 600px) {
  .header { padding: 12px 16px; }
  .main { padding: 12px 16px 30px; }
  .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
  .stat-card .value { font-size: 24px; }
  .stat-card { padding: 14px; }
  .brand h1 { font-size: 17px; }
  .guides-grid { grid-template-columns: 1fr; }
  .header-nav { display: none; }
  .report-table th:nth-child(4), .report-table td:nth-child(4),
  .report-table th:nth-child(5), .report-table td:nth-child(5) { display: none; }
}

/* ─── Heatmap ─── */
.heatmap-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:8px;padding:4px 0}
.hm-tile{position:relative;border-radius:10px;padding:14px 12px 12px;cursor:pointer;transition:transform 0.2s,box-shadow 0.2s;overflow:hidden;min-height:110px;display:flex;flex-direction:column;justify-content:space-between}
.hm-tile:hover{transform:translateY(-2px);box-shadow:0 6px 24px rgba(0,0,0,0.4)}
.hm-tile::before{content:'';position:absolute;inset:0;border-radius:10px;border:1px solid rgba(255,255,255,0.06);pointer-events:none}
.hm-tile .hm-name{font-size:13px;font-weight:700;color:#fff;line-height:1.25;margin-bottom:6px;text-shadow:0 1px 4px rgba(0,0,0,0.5);word-break:break-word}
.hm-tile .hm-meta{display:flex;align-items:center;gap:6px;flex-wrap:wrap}
.hm-tile .hm-sev{font-size:22px;font-weight:800;color:#fff;text-shadow:0 2px 6px rgba(0,0,0,0.4);font-variant-numeric:tabular-nums}
.hm-tile .hm-cat{font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;color:rgba(255,255,255,0.75);background:rgba(0,0,0,0.25);padding:2px 7px;border-radius:4px}
.hm-tile .hm-trend{font-size:11px;font-weight:600;color:rgba(255,255,255,0.85)}
.hm-tile .hm-trend.up{color:#ff6b6b}.hm-tile .hm-trend.down{color:#51cf66}
.hm-tile .hm-bottom{display:flex;align-items:center;justify-content:space-between;margin-top:auto;padding-top:6px}
.hm-tile .hm-psi{font-size:10px;color:rgba(255,255,255,0.6);font-weight:500}
.hm-tile .hm-players{font-size:10px;color:rgba(255,255,255,0.6);font-weight:500}
.hm-tile .hm-heat{font-size:10px;font-weight:600;padding:2px 6px;border-radius:3px;background:rgba(0,0,0,0.3);color:rgba(255,255,255,0.8)}

/* Severity color grades */
.hm-sev-escalating{background:linear-gradient(135deg,#b91c1c 0%,#dc2626 40%,#ef4444 100%)}
.hm-sev-elevated{background:linear-gradient(135deg,#c2410c 0%,#ea580c 40%,#f97316 100%)}
.hm-sev-warm{background:linear-gradient(135deg,#a16207 0%,#ca8a04 40%,#eab308 100%)}
.hm-sev-stabilizing{background:linear-gradient(135deg,#166534 0%,#16a34a 40%,#22c55e 100%)}
.hm-sev-low{background:linear-gradient(135deg,#1e3a5f 0%,#1d4ed8 40%,#3b82f6 100%)}

/* Heatmap detail modal */
.hm-detail-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:9999;display:flex;align-items:center;justify-content:center;opacity:0;visibility:hidden;transition:all 0.3s}
.hm-detail-overlay.open{opacity:1;visibility:visible}
.hm-detail-card{background:var(--bg-card,#161927);border:1px solid var(--border,#1e2235);border-radius:14px;max-width:520px;width:92%;max-height:80vh;overflow-y:auto;padding:28px 24px 20px;position:relative}
.hm-detail-card h3{margin:0 0 6px;font-size:18px;color:#fff}
.hm-detail-card .hm-d-badges{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px}
.hm-detail-card .hm-d-badge{font-size:11px;font-weight:600;padding:3px 10px;border-radius:6px;text-transform:uppercase;letter-spacing:0.3px}
.hm-detail-card .hm-d-stats{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:16px}
.hm-detail-card .hm-d-stat{background:rgba(255,255,255,0.04);border-radius:8px;padding:10px 12px;text-align:center}
.hm-detail-card .hm-d-stat .lab{font-size:10px;color:var(--text-muted,#4a4a6a);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px}
.hm-detail-card .hm-d-stat .val{font-size:20px;font-weight:700;color:#fff;font-variant-numeric:tabular-nums}
.hm-detail-card .hm-d-summary{font-size:13px;color:var(--text-secondary,#9b9bb8);line-height:1.6;max-height:200px;overflow-y:auto;padding:12px;background:rgba(0,0,0,0.15);border-radius:8px;border:1px solid rgba(255,255,255,0.04)}
.hm-detail-card .hm-d-close{position:absolute;top:12px;right:14px;background:none;border:none;color:var(--text-muted,#4a4a6a);font-size:22px;cursor:pointer;padding:4px 8px;border-radius:6px}
.hm-detail-card .hm-d-close:hover{background:rgba(255,255,255,0.06);color:#fff}
.hm-detail-card .hm-d-link{display:inline-block;margin-top:12px;font-size:13px;color:var(--accent,#4dabf7);text-decoration:none;font-weight:600}
.hm-detail-card .hm-d-link:hover{text-decoration:underline}

@media(max-width:600px){
  .heatmap-grid{grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:6px}
  .hm-tile{padding:10px 10px 10px;min-height:90px}
  .hm-tile .hm-sev{font-size:18px}
  .hm-tile .hm-name{font-size:12px}
}

.ws-nav{background:rgba(11,14,23,0.95);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border-bottom:1px solid rgba(255,255,255,0.06);padding:0 2rem;position:relative;z-index:10001}
.ws-nav-inner{max-width:1200px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;height:56px}
.ws-nav-brand{display:flex;align-items:center;gap:0.5rem;text-decoration:none;color:#e8e8f0}
.ws-nav-icon{width:28px;height:28px;background:linear-gradient(135deg,#4dabf7,#845ef7);border-radius:6px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:0.85rem;color:#fff}
.ws-nav-title{font-weight:700;font-size:0.95rem}
.ws-nav-badge{background:rgba(255,145,0,0.15);color:#ff9100;font-size:0.65rem;font-weight:600;padding:2px 6px;border-radius:4px;margin-left:0.5rem}
.ws-nav-links{display:flex;gap:0.5rem}
.ws-nav-links a{color:#6b6b8a;text-decoration:none;font-size:0.82rem;font-weight:500;padding:6px 10px;border-radius:6px;transition:all .2s;white-space:nowrap}
.ws-nav-links a:hover,.ws-nav-links a.active{color:#e8e8f0;background:rgba(255,255,255,0.06)}
.ws-nav-toggle{display:none;background:none;border:none;cursor:pointer;width:30px;height:22px;position:relative;padding:0}
.ws-nav-toggle span{display:block;width:100%;height:2px;background:#e8e8f0;border-radius:2px;position:absolute;left:0;transition:all .3s}
.ws-nav-toggle span:nth-child(1){top:0}
.ws-nav-toggle span:nth-child(2){top:10px}
.ws-nav-toggle span:nth-child(3){top:20px}
.ws-nav-toggle.open span:nth-child(1){transform:translateY(10px) rotate(45deg)}
.ws-nav-toggle.open span:nth-child(2){opacity:0}
.ws-nav-toggle.open span:nth-child(3){transform:translateY(-10px) rotate(-45deg)}
.ws-nav-drawer{position:fixed;top:0;right:-320px;width:300px;max-width:85vw;height:100vh;background:#0B0E17;border-left:1px solid rgba(255,255,255,0.06);z-index:10001;transition:right .3s;padding:1rem 0;overflow-y:auto}
.ws-nav-drawer.open{right:0}
.ws-nav-drawer a{display:block;padding:0.8rem 1rem;color:#6b6b8a;text-decoration:none;font-size:0.95rem;font-weight:600;border-radius:6px;transition:all .2s}
.ws-nav-drawer a:hover{color:#e8e8f0;background:rgba(255,255,255,0.04)}
.ws-nav-backdrop{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:10000}
.ws-nav-backdrop.open{display:block}
@media(max-width:768px){.ws-nav{padding:0 1rem}.ws-nav-links{display:none}.ws-nav-toggle{display:block}}
</style>


<main class="main">
  <h1 style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap">Bug Tracker &amp; Fix Guides — GripNews Gaming Intelligence</h1>

  <!-- Stat Cards -->
  <div class="stats-grid" id="statsGrid">
    <div class="stat-card total" data-filter="all" onclick="drillDown('all')">
      <div class="label">Issues Tracked</div><div class="value" id="statTotal">—</div><div class="sub">All time</div>
      <div class="tap-hint">Tap to view all</div>
    </div>
    <div class="stat-card enriched" data-filter="enriched" onclick="drillDown('enriched')">
      <div class="label">Enriched</div><div class="value" id="statEnriched">—</div><div class="sub">AI-analysed</div>
      <div class="tap-hint">Tap to view enriched</div>
    </div>
    <div class="stat-card guides" data-filter="guides" onclick="drillDown('guides')">
      <div class="label">Fix Guides</div><div class="value" id="statGuides">—</div><div class="sub">Community verified</div>
      <div class="tap-hint">Tap to view guides</div>
    </div>
    <div class="stat-card critical" data-filter="critical" onclick="drillDown('critical')">
      <div class="label">Critical</div><div class="value" id="statCritical">—</div><div class="sub">Severity ≥ 0.7</div>
      <div class="tap-hint">Tap to view critical</div>
    </div>
    <div class="stat-card weekly" data-filter="weekly" onclick="drillDown('weekly')">
      <div class="label">This Week</div><div class="value" id="statWeekly">—</div><div class="sub">New issues</div>
      <div class="tap-hint">Tap to view recent</div>
    </div>
    <div class="stat-card high" data-filter="high" onclick="drillDown('high')">
      <div class="label">High Severity</div><div class="value" id="statHigh">—</div><div class="sub">Severity 0.5 – 0.7</div>
      <div class="tap-hint">Tap to view high</div>
    </div>
    <div class="stat-card games" data-filter="games" onclick="drillDown('games')">
      <div class="label">Games Monitored</div><div class="value" id="statGames">—</div><div class="sub">Unique titles</div>
      <div class="tap-hint">Tap to view games</div>
    </div>
    <div class="stat-card categories" data-filter="categories" onclick="drillDown('categories')">
      <div class="label">Categories</div><div class="value" id="statCategories">—</div><div class="sub">Issue types tracked</div>
      <div class="tap-hint">Tap to view breakdown</div>
    </div>
  </div>

  <!-- Drill-down Panel -->
  <div class="drill-panel" id="drillPanel">
    <div class="drill-header">
      <h2 id="drillTitle">Filtered Issues</h2>
      <button class="drill-close" onclick="closeDrill()">✕ Close</button>
    </div>
    <div class="drill-body" id="drillBody">
      <div class="loading"><span class="spinner"></span>Loading…</div>
    </div>
  </div>

  <!-- Fix Guides -->
  <div class="panel" style="margin-bottom:24px;">
    <div class="panel-header">
      <h2>📝 Latest Fix Guides</h2>
      <span class="badge" id="guidesCount">—</span>
    </div>
    <div class="panel-body" style="max-height:none;overflow:visible;padding-bottom:8px;">
      <div class="guides-grid" id="guidesGrid">
        <div class="loading"><span class="spinner"></span>Loading fix guides…</div>
      </div>
      <button class="guides-load-more" id="guidesMore" onclick="loadMoreGuides()" style="display:none">Load more guides ▾</button>
    </div>
  </div>


  <!-- Game Heatmap -->
  <div class="panel" style="margin-bottom:24px;" id="heatmapPanel">
    <div class="panel-header"><h2>🗺️ Game Severity Heatmap</h2><span class="badge" id="heatmapCount">—</span></div>
    <div class="panel-body" id="heatmapBody"><div class="loading"><span class="spinner"></span>Loading heatmap…</div></div>
  </div>

  <!-- Two columns: Categories + Severity -->
  <div class="content-grid">
    <div class="panel">
      <div class="panel-header"><h2>📊 Categories</h2><span class="badge" id="catCount">—</span></div>
      <div class="panel-body" id="categoriesBody"><div class="loading"><span class="spinner"></span>Loading…</div></div>
    </div>
    <div class="panel">
      <div class="panel-header"><h2>🎯 Severity Breakdown</h2></div>
      <div class="panel-body"><div class="chart-container" id="severityChart"><div class="loading"><span class="spinner"></span>Loading…</div></div></div>
    </div>
  </div>

  <!-- Latest Issues Table -->
  <div class="panel" style="margin-bottom:24px;">
    <div class="panel-header"><h2>🔍 Latest Issues</h2><span class="badge" id="issueCount">—</span></div>
    <div class="panel-body" style="max-height:500px;" id="issuesBody"><div class="loading"><span class="spinner"></span>Loading…</div></div>
  </div>

  <!-- Pipeline Status -->
  <div class="panel agent-panel">
    <div class="panel-header"><h2>🤖 Intelligence Pipeline</h2><span class="badge" id="pipelineBadge">—</span></div>
    <div class="panel-body" id="pipelineBody"><div class="loading"><span class="spinner"></span>Loading…</div></div>
  </div>

</main>

<!-- Issue Detail Modal -->
<div class="modal-overlay" id="issueModal" onclick="if(event.target===this)closeModal()">
  <div class="modal">
    <button class="modal-close" onclick="closeModal()">✕</button>
    <h3 id="modalTitle"></h3>
    <div class="modal-meta" id="modalMeta"></div>
    <div class="modal-body" id="modalBody"></div>
  </div>
</div>


<!-- Heatmap Detail Modal -->
<div class="hm-detail-overlay" id="hmDetailOverlay" onclick="if(event.target===this)closeHmDetail()">
  <div class="hm-detail-card" id="hmDetailContent"></div>
</div>

<!-- Cookie Consent -->
<div class="cookie-banner" id="cookieBanner">
  We use cookies for analytics. <button onclick="acceptCookies()">Accept</button> <button class="btn-reject" onclick="rejectCookies()">Reject</button>
</div>



<script>
/* ── Bug Tracker & Fix Guides Logic ────────────────────────────────────── */
function wsToggleNav(){
var d=document.getElementById('wsNavDrawer'),b=document.getElementById('wsNavBackdrop'),t=document.getElementById('wsNavToggle');
d.classList.toggle('open');b.classList.toggle('open');t.classList.toggle('open');
}
const API = "https://gripai.uk/api";
const CATEGORY_ICONS = {
  crash:'💥', gameplay:'🎮', audio:'🔊', network:'🌐', account:'👤', unknown:'🔍',
  performance:'⚡', graphics:'🖥️', input:'🕹️', security:'🔒', visual:'👁️', freeze:'🧊'
};
const SEV_ORDER = ['critical','high','medium','low'];
const SEV_COLORS = { critical:'#ef4444', high:'#f97316', medium:'#eab308', low:'#22c55e' };

let refreshTimer, guidesPage = 1, allGuidesCache = [], allIssuesCache = [], blogCount = 0;

async function fetchJSON(url) {
  try { const r = await fetch(url, { cache: 'no-store' }); if (!r.ok) throw new Error(r.status); return await r.json(); }
  catch(e) { console.warn('Fetch:', url, e); return null; }
}
function fmt(n) { n = Number(n); return isNaN(n) ? '—' : n.toLocaleString('en-GB'); }
function timeAgo(d) {
  if (!d) return '';
  const utc = d.includes('Z') || d.includes('+') ? d : d + 'Z';
  const s = Math.floor((Date.now() - new Date(utc)) / 1000);
  if (s < 0) return 'just now';
  if (s < 60) return s + 's ago'; if (s < 3600) return Math.floor(s/60) + 'm ago';
  if (s < 86400) return Math.floor(s/3600) + 'h ago'; return Math.floor(s/86400) + 'd ago';
}
function esc(s) { return (s||'').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

function sevLabel(val) {
  const n = parseFloat(val);
  if (n >= 0.7) return 'critical';
  if (n >= 0.5) return 'high';
  if (n >= 0.3) return 'medium';
  return 'low';
}


// ─── Heatmap ───
const HOTSPOTS_API = 'https://api.gripai.uk/v1/live/hotspots?limit=200';

function prettifyGameName(slug) {
  return slug.replace(/-/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
}

function hmSevClass(severity, heat) {
  if (heat && heat.includes('Escalating')) return 'hm-sev-escalating';
  if (severity >= 6.0) return 'hm-sev-elevated';
  if (severity >= 4.5) return 'hm-sev-warm';
  if (severity >= 3.0) return 'hm-sev-stabilizing';
  return 'hm-sev-low';
}

function hmTrendIcon(trend) {
  if (!trend) return '';
  const val = trend.replace('%','').trim();
  const num = parseFloat(val);
  if (isNaN(num)) return '';
  if (num > 0) return `<span class="hm-trend up">▲ ${trend}</span>`;
  if (num < 0) return `<span class="hm-trend down">▼ ${trend}</span>`;
  return `<span class="hm-trend">— ${trend}</span>`;
}

let hotspotCache = [];

async function loadHeatmap() {
  try {
    const res = await fetch(HOTSPOTS_API, { cache: 'no-store' });
    if (!res.ok) throw new Error(res.status);
    hotspotCache = await res.json();
  } catch(e) {
    console.warn('Heatmap fetch failed:', e);
    document.getElementById('heatmapBody').innerHTML = '<div style="padding:16px;color:var(--text-muted);">Unable to load heatmap data</div>';
    return;
  }

  // Filter out "unknown" games, deduplicate by game_id (keep highest severity)
  const gameMap = {};
  hotspotCache.forEach(h => {
    if (h.game_id === 'unknown' || !h.game_name) return;
    const existing = gameMap[h.game_id];
    if (!existing || h.severity > existing.severity) {
      gameMap[h.game_id] = h;
    }
  });

  const games = Object.values(gameMap).sort((a, b) => b.severity - a.severity);
  document.getElementById('heatmapCount').textContent = games.length + ' games';

  if (games.length === 0) {
    document.getElementById('heatmapBody').innerHTML = '<div style="padding:16px;color:var(--text-muted);">No active hotspots</div>';
    return;
  }

  let html = '<div class="heatmap-grid">';
  games.forEach((g, i) => {
    const cls = hmSevClass(g.severity, g.heat_level);
    const name = prettifyGameName(g.game_name);
    const trend = hmTrendIcon(g.trend);
    html += `<div class="hm-tile ${cls}" onclick="showHmDetail(${i})" title="${esc(name)} — Severity ${g.severity}">
      <div>
        <div class="hm-name">${esc(name)}</div>
        <div class="hm-cat">${esc(g.category)}</div>
      </div>
      <div class="hm-bottom">
        <div class="hm-meta"><span class="hm-sev">${g.severity.toFixed(1)}</span>${trend}</div>
        <div class="hm-players">${g.players_affected ? g.players_affected + ' players' : ''}</div>
      </div>
    </div>`;
  });
  html += '</div>';
  document.getElementById('heatmapBody').innerHTML = html;
}

function showHmDetail(idx) {
  const gameMap = {};
  hotspotCache.forEach(h => {
    if (h.game_id === 'unknown' || !h.game_name) return;
    const existing = gameMap[h.game_id];
    if (!existing || h.severity > existing.severity) gameMap[h.game_id] = h;
  });
  const games = Object.values(gameMap).sort((a, b) => b.severity - a.severity);
  const g = games[idx];
  if (!g) return;

  const name = prettifyGameName(g.game_name);
  const cls = hmSevClass(g.severity, g.heat_level);
  const heatText = (g.heat_level || '').replace(/[^\w\s]/g, '').trim();
  const movText = g.severity_movement ? g.severity_movement.charAt(0).toUpperCase() + g.severity_movement.slice(1) : '—';

  // Clean summary (remove crawl refs)
  let summary = (g.summary || 'No summary available').replace(/\[crawl:[^\]]+\]\s*/g, '');
  if (summary.length > 500) summary = summary.slice(0, 497) + '…';

  const overlay = document.getElementById('hmDetailOverlay');
  document.getElementById('hmDetailContent').innerHTML = `
    <button class="hm-d-close" onclick="closeHmDetail()">&times;</button>
    <h3>${esc(name)}</h3>
    <div class="hm-d-badges">
      <span class="hm-d-badge ${cls}" style="color:#fff">${esc(g.category)}</span>
      <span class="hm-d-badge" style="background:rgba(255,255,255,0.06);color:var(--text-secondary)">${esc(heatText)}</span>
      <span class="hm-d-badge" style="background:rgba(255,255,255,0.06);color:var(--text-secondary)">${esc(g.platform)}</span>
    </div>
    <div class="hm-d-stats">
      <div class="hm-d-stat"><div class="lab">Severity</div><div class="val">${g.severity.toFixed(1)}</div></div>
      <div class="hm-d-stat"><div class="lab">PSI</div><div class="val">${g.psi ? g.psi.toFixed(1) : '—'}</div></div>
      <div class="hm-d-stat"><div class="lab">Trend</div><div class="val" style="font-size:16px;">${g.trend || '—'} ${esc(movText)}</div></div>
    </div>
    <div class="hm-d-stats" style="grid-template-columns:1fr 1fr;">
      <div class="hm-d-stat"><div class="lab">Players Affected</div><div class="val">${g.players_affected || '—'}</div></div>
      <div class="hm-d-stat"><div class="lab">Verified Fix</div><div class="val" style="font-size:16px;">${g.verified_fix ? '✅ Yes' : '❌ No'}</div></div>
    </div>
    <div class="hm-d-summary">${esc(summary)}</div>
    <a class="hm-d-link" href="https://api.gripai.uk/" target="_blank">View on API Dashboard →</a>
  `;
  overlay.classList.add('open');
}

function closeHmDetail() {
  document.getElementById('hmDetailOverlay').classList.remove('open');
}


// ─── Load All Data ───
async function loadAllData() {
  const [issuesData, blogData] = await Promise.all([
    fetchJSON(API + '/issues?limit=200'),
    fetchJSON(API + '/blog?limit=30')
  ]);

  if (issuesData) {
    allIssuesCache = issuesData.issues || [];
    const totalCount = issuesData.count || allIssuesCache.length;
    const enrichedCount = issuesData.enriched || 0;
    const criticalCount = issuesData.critical || 0;

    // Compute stats from sample
    const now = Date.now();
    const weekAgo = now - 7 * 86400000;
    const weeklyCount = allIssuesCache.filter(i => new Date(i.created_at + (i.created_at.includes('Z') ? '' : 'Z')).getTime() > weekAgo).length;
    const highCount = allIssuesCache.filter(i => { const s = parseFloat(i.severity); return s >= 0.5 && s < 0.7; }).length;
    const gamesSet = new Set(allIssuesCache.map(i => i.game_name));
    const catSet = new Set(allIssuesCache.map(i => i.category));

    // Populate stats
    document.getElementById('statTotal').textContent = fmt(totalCount);
    document.getElementById('statEnriched').textContent = fmt(enrichedCount);
    document.getElementById('statCritical').textContent = fmt(criticalCount);
    document.getElementById('statWeekly').textContent = fmt(weeklyCount);
    document.getElementById('statHigh').textContent = fmt(highCount);
    document.getElementById('statGames').textContent = fmt(gamesSet.size);
    document.getElementById('statCategories').textContent = fmt(catSet.size);

    // Render categories
    renderCategories(allIssuesCache);
    // Render severity
    renderSeverity(allIssuesCache);
    // Render issues table
    renderIssuesTable(allIssuesCache.slice(0, 50));
        // Render pipeline
    renderPipeline(issuesData, allIssuesCache);
    loadHeatmap();
  }

  if (blogData) {
    blogCount = blogData.count || 0;
    document.getElementById('statGuides').textContent = fmt(blogCount);
    allGuidesCache = blogData.posts || [];
    renderGuides(allGuidesCache.slice(0, 12));
    document.getElementById('guidesCount').textContent = blogCount + ' guides';
    if (allGuidesCache.length > 12) {
      document.getElementById('guidesMore').style.display = 'block';
    }
  }
}


// ─── Categories ───
function renderCategories(issues) {
  const catMap = {};
  issues.forEach(i => { catMap[i.category] = (catMap[i.category] || 0) + 1; });
  const cats = Object.entries(catMap).sort((a, b) => b[1] - a[1]);
  const maxCount = cats.length ? cats[0][1] : 1;
  const el = document.getElementById('categoriesBody');
  document.getElementById('catCount').textContent = cats.length + ' types';
  el.innerHTML = cats.map(([cat, cnt]) => {
    const pct = (cnt / maxCount * 100);
    const icon = CATEGORY_ICONS[cat] || '❓';
    return `<div class="cat-row">
      <span class="cat-icon">${icon}</span>
      <div class="cat-info"><div class="cat-name">${cat.replace(/_/g, ' ')}</div><div class="cat-bar-track"><div class="cat-bar-fill" style="width:${pct}%"></div></div></div>
      <span class="cat-count">${cnt}</span>
    </div>`;
  }).join('');
}

// ─── Severity Doughnut ───
function renderSeverity(issues) {
  const el = document.getElementById('severityChart');
  const counts = { critical: 0, high: 0, medium: 0, low: 0 };
  issues.forEach(i => { counts[sevLabel(i.severity)]++; });
  const total = counts.critical + counts.high + counts.medium + counts.low;
  el.innerHTML = `<div class="doughnut-wrap"><canvas id="doughnutCanvas" width="160" height="160"></canvas>
    <div class="doughnut-center"><div class="big">${total}</div><div class="small">ISSUES</div></div></div>
    <div class="legend">${SEV_ORDER.map(s => `<div class="legend-item">
      <span class="legend-dot" style="background:${SEV_COLORS[s]}"></span><span>${s.charAt(0).toUpperCase()+s.slice(1)}</span>
      <span class="legend-count" style="color:${SEV_COLORS[s]}">${counts[s]}</span></div>`).join('')}</div>`;
  const canvas = document.getElementById('doughnutCanvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d'), cx = 80, cy = 80, r = 60, w = 16;
  let start = -Math.PI / 2;
  SEV_ORDER.forEach(s => {
    const angle = total > 0 ? (counts[s] / total) * Math.PI * 2 : 0;
    if (angle > 0) { ctx.beginPath(); ctx.arc(cx, cy, r, start, start + angle); ctx.lineWidth = w; ctx.strokeStyle = SEV_COLORS[s]; ctx.lineCap = 'round'; ctx.stroke(); start += angle + 0.03; }
  });
}

// ─── Issues Table ───
function renderIssuesTable(issues) {
  const el = document.getElementById('issuesBody');
  document.getElementById('issueCount').textContent = issues.length + ' latest';
  if (!issues.length) { el.innerHTML = '<p style="color:var(--text-muted);text-align:center;padding:20px">No issues found</p>'; return; }
  el.innerHTML = `<table class="report-table">
    <thead><tr><th>Severity</th><th>Game</th><th>Category</th><th>Confidence</th><th>Discovered</th></tr></thead>
    <tbody>${issues.map((r, idx) => {
      const sev = sevLabel(r.severity);
      const icon = CATEGORY_ICONS[r.category] || '❓';
      return `<tr onclick="showIssueDetail(${idx})" style="cursor:pointer">
        <td><span class="pill pill-${sev}">${sev}</span></td>
        <td class="report-title" title="${esc(r.game_name)}">${esc(r.game_name)}</td>
        <td style="font-size:12px;color:var(--text-secondary)">${icon} ${(r.category||'—').replace(/_/g,' ')}</td>
        <td style="font-size:12px;color:var(--text-secondary)">${(parseFloat(r.confidence)*100).toFixed(0)}%</td>
        <td style="color:var(--text-muted);font-size:11px;white-space:nowrap">${r.created_at ? timeAgo(r.created_at) : '—'}</td>
      </tr>`;
    }).join('')}</tbody></table>`;
}

// ─── Game slug extractor ───
// Slug pattern: {game-slug}-{category}-{issueId}  e.g. "among-us-crash-13471"
const ISSUE_CATEGORIES = ['crash','network','unknown','performance','visual','freeze','audio','input','launch','memory','save','update'];
function gameSlugFromPost(slug) {
  if (!slug) return null;
  const noId = slug.replace(/-\d+$/, '');        // strip trailing issue id
  for (const cat of ISSUE_CATEGORIES) {
    if (noId.endsWith('-' + cat)) return noId.slice(0, -(cat.length + 1));
  }
  return null; // not a game guide (e.g. crawl digest)
}
function isCrawlDigest(slug) { return slug && slug.startsWith('crawl-digest'); }
function gameLink(slug) {
  if (isCrawlDigest(slug)) return 'https://gripnews.uk';
  const gs = gameSlugFromPost(slug);
  return gs ? `https://gripnews.uk/game/${gs}` : 'https://gripai.uk';
}
function guideLinkLabel(slug) {
  if (isCrawlDigest(slug)) return 'View on GripNews →';
  return 'View on GripNews →';
}
function guidePillLabel(slug) {
  if (isCrawlDigest(slug)) return 'intel digest';
  return 'fix guide';
}
function guidePillClass(slug) {
  if (isCrawlDigest(slug)) return 'pill-info';
  return 'pill-enriched';
}

// ─── Fix Guides Cards ───
function renderGuides(guides) {
  const grid = document.getElementById('guidesGrid');
  grid.innerHTML = guides.map((g, i) => {
    const link = gameLink(g.slug);
    const label = guideLinkLabel(g.slug);
    const pill = guidePillLabel(g.slug);
    const pillCls = guidePillClass(g.slug);
    return `<div class="guide-card" onclick="showGuideDetail(${i})">
      <div class="guide-top">
        <span class="pill ${pillCls}">${pill}</span>
        <span class="guide-time">${g.created_at ? timeAgo(g.created_at) : ''}</span>
      </div>
      <div class="guide-title">${esc(g.title)}</div>
      <div class="guide-excerpt">${esc(g.excerpt)}</div>
      <div style="margin-top:8px;font-size:11px"><a href="${link}" target="_blank" style="color:var(--accent);text-decoration:none" onclick="event.stopPropagation()">${label}</a></div>
    </div>`;
  }).join('');
}

let guidesShown = 12;
function loadMoreGuides() {
  guidesShown += 12;
  renderGuides(allGuidesCache.slice(0, guidesShown));
  if (guidesShown >= allGuidesCache.length) {
    document.getElementById('guidesMore').style.display = 'none';
  }
}

// ─── Drill-down ───
function drillDown(filter) {
  document.querySelectorAll('.stat-card').forEach(c => c.classList.remove('active'));
  const card = document.querySelector(`.stat-card[data-filter="${filter}"]`);
  if (card) card.classList.add('active');

  const panel = document.getElementById('drillPanel');
  const title = document.getElementById('drillTitle');
  const body = document.getElementById('drillBody');
  panel.classList.add('show');

  let filtered = [], titleText = '';
  switch(filter) {
    case 'all':
      filtered = allIssuesCache;
      titleText = '📋 All Issues';
      break;
    case 'enriched':
      filtered = allIssuesCache.filter(i => i.pipeline_status === 'enriched');
      titleText = '✅ Enriched Issues';
      break;
    case 'guides':
      // Show guides list
      titleText = '📝 Fix Guides';
      body.innerHTML = allGuidesCache.length ? `<table class="report-table">
        <thead><tr><th>Title</th><th>Status</th><th>Created</th></tr></thead>
        <tbody>${allGuidesCache.slice(0,50).map(g => `<tr>
          <td class="report-title" title="${esc(g.title)}">${esc(g.title)}</td>
          <td><span class="pill pill-enriched">${g.status||'published'}</span></td>
          <td style="color:var(--text-muted);font-size:11px;white-space:nowrap">${g.created_at ? timeAgo(g.created_at) : '—'}</td>
        </tr>`).join('')}</tbody></table>
        <p style="color:var(--text-muted);font-size:11px;text-align:right;margin-top:8px">${fmt(blogCount)} total guides</p>` : '<p style="color:var(--text-muted);text-align:center;padding:20px">No guides found</p>';
      title.innerHTML = titleText;
      panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      return;
    case 'critical':
      filtered = allIssuesCache.filter(i => parseFloat(i.severity) >= 0.7);
      titleText = '🔴 Critical Issues';
      break;
    case 'weekly':
      const weekAgo = Date.now() - 7 * 86400000;
      filtered = allIssuesCache.filter(i => new Date(i.created_at + (i.created_at.includes('Z') ? '' : 'Z')).getTime() > weekAgo);
      titleText = '📅 This Week\'s Issues';
      break;
    case 'high':
      filtered = allIssuesCache.filter(i => { const s = parseFloat(i.severity); return s >= 0.5 && s < 0.7; });
      titleText = '🟠 High Severity Issues';
      break;
    case 'games':
      // Show game breakdown
      titleText = '🎮 Games Monitored';
      const gameMap = {};
      allIssuesCache.forEach(i => { gameMap[i.game_name] = (gameMap[i.game_name] || 0) + 1; });
      const games = Object.entries(gameMap).sort((a, b) => b[1] - a[1]);
      body.innerHTML = '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px">' +
        games.map(([name, cnt]) => `<div style="background:var(--bg-card-alt);border-radius:8px;padding:14px;text-align:center">
          <div style="font-size:14px;font-weight:600">${esc(name)}</div>
          <div style="font-size:24px;font-weight:700;color:var(--accent);margin:4px 0">${cnt}</div>
          <div style="font-size:11px;color:var(--text-muted)">issues</div>
        </div>`).join('') + '</div>';
      title.innerHTML = titleText;
      panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      return;
    case 'categories':
      titleText = '📊 Category Breakdown';
      const catMap = {};
      allIssuesCache.forEach(i => { catMap[i.category] = (catMap[i.category] || 0) + 1; });
      const total = allIssuesCache.length;
      const cats = Object.entries(catMap).sort((a, b) => b[1] - a[1]);
      body.innerHTML = '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px">' +
        cats.map(([cat, cnt]) => {
          const icon = CATEGORY_ICONS[cat] || '❓';
          const pct = total > 0 ? (cnt/total*100).toFixed(1) : 0;
          return `<div style="background:var(--bg-card-alt);border-radius:8px;padding:14px;text-align:center">
            <div style="font-size:28px;margin-bottom:6px">${icon}</div>
            <div style="font-size:14px;font-weight:600;text-transform:capitalize">${cat.replace(/_/g,' ')}</div>
            <div style="font-size:24px;font-weight:700;color:var(--accent);margin:4px 0">${cnt}</div>
            <div style="font-size:11px;color:var(--text-muted)">${pct}% of sample</div>
          </div>`;
        }).join('') + '</div>';
      title.innerHTML = titleText;
      panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      return;
  }

  title.innerHTML = titleText;
  if (filtered.length === 0) {
    body.innerHTML = '<p style="color:var(--text-muted);text-align:center;padding:20px">No matching issues</p>';
  } else {
    body.innerHTML = `<table class="report-table">
      <thead><tr><th>Severity</th><th>Game</th><th>Category</th><th>Confidence</th><th>Discovered</th></tr></thead>
      <tbody>${filtered.slice(0,50).map(r => {
        const sev = sevLabel(r.severity);
        const icon = CATEGORY_ICONS[r.category] || '❓';
        return `<tr>
          <td><span class="pill pill-${sev}">${sev}</span></td>
          <td class="report-title" title="${esc(r.game_name)}">${esc(r.game_name)}</td>
          <td style="font-size:12px;color:var(--text-secondary)">${icon} ${(r.category||'—').replace(/_/g,' ')}</td>
          <td style="font-size:12px;color:var(--text-secondary)">${(parseFloat(r.confidence)*100).toFixed(0)}%</td>
          <td style="color:var(--text-muted);font-size:11px;white-space:nowrap">${r.created_at ? timeAgo(r.created_at) : '—'}</td>
        </tr>`;
      }).join('')}</tbody></table>
      <p style="color:var(--text-muted);font-size:11px;text-align:right;margin-top:8px">Showing ${Math.min(filtered.length,50)} of ${filtered.length} issues</p>`;
  }
  panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function closeDrill() {
  document.getElementById('drillPanel').classList.remove('show');
  document.querySelectorAll('.stat-card').forEach(c => c.classList.remove('active'));
}

// ─── Issue Detail Modal ───
function showIssueDetail(idx) {
  const i = allIssuesCache[idx];
  if (!i) return;
  const sev = sevLabel(i.severity);
  const icon = CATEGORY_ICONS[i.category] || '❓';
  document.getElementById('modalTitle').textContent = i.game_name + ' — ' + (i.category || 'Issue');
  document.getElementById('modalMeta').innerHTML = `
    <span class="pill pill-${sev}">${sev}</span>
    <span class="pill pill-enriched">${i.pipeline_status||'enriched'}</span>
    <span style="font-size:16px">${icon}</span>
    <span style="color:var(--text-muted);font-size:12px">${i.created_at ? new Date(i.created_at).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'}) : ''}</span>
  `;
  let bodyHTML = `<p><strong>Game:</strong> ${esc(i.game_name)}</p>`;
  bodyHTML += `<p><strong>Category:</strong> ${icon} ${(i.category||'unknown').replace(/_/g,' ')}</p>`;
  bodyHTML += `<p><strong>Severity Score:</strong> ${i.severity} (${sev})</p>`;
  bodyHTML += `<p><strong>Confidence:</strong> ${(parseFloat(i.confidence)*100).toFixed(1)}%</p>`;
  bodyHTML += `<p><strong>Crawl Count:</strong> ${i.crawl_count}</p>`;
  bodyHTML += `<p><strong>Pipeline Status:</strong> ${i.pipeline_status}</p>`;
  bodyHTML += `<p style="margin-top:16px;padding-top:12px;border-top:1px solid var(--border);font-size:12px;color:var(--text-muted)">Issue ID: ${i.id} · Tracked by <a href="https://gripai.uk" style="color:var(--accent)">GripAi</a> · Enriched by GripAi</p>`;
  document.getElementById('modalBody').innerHTML = bodyHTML;
  document.getElementById('issueModal').classList.add('show');
  document.body.style.overflow = 'hidden';
}


function showGuideDetail(idx) {
  const g = allGuidesCache[idx];
  if (!g) return;
  document.getElementById('modalTitle').textContent = g.title;
  document.getElementById('modalMeta').innerHTML = `
    <span class="pill pill-enriched">${g.status||'published'}</span>
    <span style="color:var(--text-muted);font-size:12px">${g.created_at ? new Date(g.created_at).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'}) : ''}</span>
  `;
  let bodyHTML = `<p>${esc(g.excerpt)}</p>`;
  if (g.slug) {
    const gLink = gameLink(g.slug);
    const gLabel = isCrawlDigest(g.slug) ? 'View on GripNews →' : 'View game on GripNews →';
    bodyHTML += `<p style="margin-top:16px;padding-top:12px;border-top:1px solid var(--border);font-size:13px"><a href="${gLink}" style="color:var(--accent);font-weight:600" target="_blank">${gLabel}</a></p>`;
  }
  bodyHTML += `<p style="font-size:12px;color:var(--text-muted)">Guide ID: ${g.id} · <a href="https://gripnews.uk/archive" style="color:var(--accent)" target="_blank">Browse all guides on GripNews →</a></p>`;
  document.getElementById('modalBody').innerHTML = bodyHTML;
  document.getElementById('issueModal').classList.add('show');
  document.body.style.overflow = 'hidden';
}

function closeModal() {
  document.getElementById('issueModal').classList.remove('show');
  document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeModal(); closeDrill(); } });

// ─── Pipeline Status ───
function renderPipeline(apiData, issues) {
  const el = document.getElementById('pipelineBody');
  const totalCount = apiData.count || 0;
  const enrichedCount = apiData.enriched || 0;
  const criticalCount = apiData.critical || 0;
  const enrichPct = totalCount > 0 ? ((enrichedCount / totalCount) * 100).toFixed(1) : 0;

  // Game breakdown
  const gameMap = {};
  issues.forEach(i => { gameMap[i.game_name] = (gameMap[i.game_name] || 0) + 1; });
  const topGames = Object.entries(gameMap).sort((a, b) => b[1] - a[1]).slice(0, 8);

  document.getElementById('pipelineBadge').textContent = 'Active';
  document.getElementById('pipelineBadge').style.color = 'var(--green)';

  el.innerHTML = `<div class="agent-grid" style="margin-bottom:16px">
    <div class="agent-stat"><div class="val">${fmt(totalCount)}</div><div class="lbl">Total Issues</div></div>
    <div class="agent-stat"><div class="val">${fmt(enrichedCount)}</div><div class="lbl">Enriched</div></div>
    <div class="agent-stat"><div class="val">${enrichPct}%</div><div class="lbl">Enrichment Rate</div></div>
    <div class="agent-stat"><div class="val">${fmt(criticalCount)}</div><div class="lbl">Critical</div></div>
    <div class="agent-stat"><div class="val">${fmt(blogCount)}</div><div class="lbl">Fix Guides</div></div>
    <div class="agent-stat"><div class="val">${Object.keys(gameMap).length}</div><div class="lbl">Games</div></div>
  </div>
  <h3 style="font-size:13px;color:var(--text-muted);margin-bottom:10px;text-transform:uppercase;letter-spacing:0.5px">Top Games by Issues</h3>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:10px">
    ${topGames.map(([name, cnt]) => `<div style="background:var(--bg-card-alt);border-radius:8px;padding:12px 14px;display:flex;align-items:center;gap:10px">
      <span style="width:10px;height:10px;border-radius:50%;background:var(--green);flex-shrink:0"></span>
      <span style="font-size:13px;font-weight:500;flex:1">${esc(name)}</span>
      <span style="font-size:12px;color:var(--text-muted)">${cnt} issues</span>
    </div>`).join('')}
  </div>
  <p style="color:var(--text-muted);font-size:11px;margin-top:12px;text-align:right">Powered by GripAi Intelligence</p>`;
}

// ─── Cookie Consent ───
function acceptCookies() {
  document.cookie = 'gg_consent=1;max-age=31536000;path=/;SameSite=Lax';
  document.getElementById('cookieBanner').classList.remove('show');
  loadGA();
}
function rejectCookies() {
  document.cookie = 'gg_consent=0;max-age=31536000;path=/;SameSite=Lax';
  document.getElementById('cookieBanner').classList.remove('show');
}
if (document.cookie.indexOf('gg_consent=') === -1) {
  document.getElementById('cookieBanner').classList.add('show');
}

// ─── Init ───
async function refreshAll() {
  await loadAllData();
  document.getElementById('lastRefresh').textContent = 'Updated: ' + new Date().toLocaleTimeString('en-GB');
}
refreshAll();
refreshTimer = setInterval(refreshAll, 30000);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
