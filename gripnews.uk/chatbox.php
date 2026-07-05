<?php
/**
 * GripNews.uk — Community Buzz & Sentiment
 * Migrated from chatbox/index.html
 */
require_once __DIR__ . '/includes/functions.php';

$page_title = "Community Buzz & Sentiment — GripNews";
$page_desc  = "Live positive gaming intelligence — player praise, community highlights, and trending buzz powered by GripAi.";
$nav_active = 'chatbox';

require_once __DIR__ . '/includes/header.php';
?>

<style>
/* ── Community Buzz & Sentiment Styles ───────────────────────────────────── */
:root {
  --bg-primary: #0B0E17;
  --bg-card: #141824;
  --bg-card-alt: #1a2030;
  --border: rgba(34, 197, 94, 0.2);
  --border-hover: rgba(34, 197, 94, 0.5);
  --text-primary: #e2e8f0;
  --text-secondary: #94a3b8;
  --text-muted: #64748b;
  --accent: #22c55e;
  --accent-glow: rgba(34, 197, 94, 0.12);
  --accent2: #a855f7;
  --red: #ef4444;
  --orange: #f97316;
  --yellow: #eab308;
  --green: #22c55e;
  --blue: #3b82f6;
  --cyan: #06b6d4;
  --purple: #a855f7;
  --gold: #f59e0b;
  --pink: #ec4899;
  --emerald: #10b981;
}
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: var(--bg-primary); color: var(--text-primary);
  min-height: 100vh; line-height: 1.5;
}

/* ─── Navigation ─── */
.ws-nav { display: flex; align-items: center; justify-content: center; gap: 8px; padding: 6px 16px; background: rgba(11,14,23,0.95); border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 12px; flex-wrap: wrap; }
.ws-nav span { color: var(--text-muted); }
.ws-nav a { color: var(--accent); text-decoration: none; transition: color 0.2s; }
.ws-nav a:hover { color: var(--text-primary); }
.ws-nav-toggle { display: none; background: none; border: 1px solid var(--border); border-radius: 6px; padding: 6px 8px; cursor: pointer; position: absolute; right: 16px; top: 6px; }
.ws-nav-toggle span { display: block; width: 16px; height: 2px; background: var(--text-secondary); margin: 3px 0; transition: 0.3s; }
.ws-nav-backdrop { display: none; }
.ws-nav-drawer { display: none; }
@media (max-width: 700px) {
  .ws-nav { display: none; }
  .ws-nav-toggle { display: block; }
  .ws-nav-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 998; }
  .ws-nav-backdrop.open { display: block; }
  .ws-nav-drawer { position: fixed; top: 0; right: -260px; width: 260px; height: 100%; background: var(--bg-card); z-index: 999; transition: right 0.3s; display: flex; flex-direction: column; padding: 60px 20px 20px; gap: 8px; border-left: 1px solid var(--border); }
  .ws-nav-drawer.open { right: 0; display: flex; }
  .ws-nav-drawer a { color: var(--text-secondary); text-decoration: none; padding: 10px 12px; border-radius: 8px; }
  .ws-nav-drawer a:hover, .ws-nav-drawer a.active { background: var(--accent-glow); color: var(--accent); }
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
.brand { display: flex; align-items: center; gap: 12px; text-decoration: none; }
.brand-icon { font-size: 28px; }
.brand h1 {
  font-size: 20px; font-weight: 700;
  background: linear-gradient(135deg, var(--green), var(--emerald));
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
.header-nav a.active { border-color: var(--accent); color: var(--accent); background: var(--accent-glow); }
.main { max-width: 1400px; margin: 0 auto; padding: 20px 24px 40px; }

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
.stat-card.positive .value { color: var(--green); }
.stat-card.analysed .value { color: var(--emerald); }
.stat-card.highlights .value { color: var(--cyan); }
.stat-card.viral .value { color: var(--pink); }
.stat-card.weekly .value { color: var(--purple); }
.stat-card.hype .value { color: var(--gold); }
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
.cat-bar-fill { height: 100%; border-radius: 3px; background: linear-gradient(90deg, var(--green), var(--emerald)); transition: width 0.8s ease; }
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
.pill-viral { background: rgba(236,72,153,0.15); color: var(--pink); }
.pill-hype { background: rgba(245,158,11,0.15); color: var(--gold); }
.pill-positive { background: rgba(34,197,94,0.15); color: var(--green); }
.pill-neutral { background: rgba(59,130,246,0.15); color: var(--blue); }
.pill-praise { background: rgba(168,85,247,0.15); color: var(--purple); }
.pill-enriched { background: rgba(34,197,94,0.15); color: var(--green); }
.pill-info { background: rgba(59,130,246,0.15); color: #60a5fa; }
.pill-highlight { background: rgba(6,182,212,0.15); color: var(--cyan); }

/* ─── Guide / Highlight Cards ─── */
.guides-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px; }
.guide-card {
  background: var(--bg-card-alt); border: 1px solid var(--border); border-radius: 10px;
  padding: 18px; transition: all 0.3s; cursor: pointer;
}
.guide-card:hover { border-color: var(--border-hover); transform: translateY(-2px); box-shadow: 0 6px 20px var(--accent-glow); }
.guide-top { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; flex-wrap: wrap; }
.guide-time { color: var(--text-muted); font-size: 11px; margin-left: auto; }
.guide-title { font-size: 14px; font-weight: 600; color: var(--text-primary); margin-bottom: 8px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.guide-excerpt { font-size: 13px; color: var(--text-secondary); line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 6; -webkit-box-orient: vertical; overflow: hidden; }
.guides-load-more {
  display: none; width: 100%; padding: 12px; margin-top: 16px;
  background: var(--bg-card-alt); border: 1px solid var(--border); border-radius: 8px;
  color: var(--accent); cursor: pointer; font-size: 13px; text-align: center; transition: 0.2s;
}
.guides-load-more:hover { border-color: var(--accent); background: var(--accent-glow); }

/* ─── Doughnut Chart ─── */
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

/* ─── Heatmap ─── */
.heatmap-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 8px; padding: 4px 0; }
.hm-tile {
  position: relative; border-radius: 10px; padding: 14px 12px 12px; cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s; overflow: hidden; min-height: 110px;
  display: flex; flex-direction: column; justify-content: space-between;
}
.hm-tile:hover { transform: translateY(-2px); box-shadow: 0 6px 24px rgba(0,0,0,0.4); }
.hm-tile::before { content: ''; position: absolute; inset: 0; border-radius: 10px; border: 1px solid rgba(255,255,255,0.06); pointer-events: none; }
.hm-tile .hm-name { font-size: 13px; font-weight: 700; color: #fff; line-height: 1.25; margin-bottom: 6px; text-shadow: 0 1px 4px rgba(0,0,0,0.5); word-break: break-word; }
.hm-tile .hm-cat { font-size: 10px; color: rgba(255,255,255,0.6); text-transform: uppercase; letter-spacing: 0.5px; }
.hm-bottom { margin-top: auto; }
.hm-meta { display: flex; align-items: center; gap: 6px; }
.hm-sev { font-size: 18px; font-weight: 700; color: #fff; }
.hm-trend { font-size: 11px; color: rgba(255,255,255,0.6); }
.hm-trend.up { color: var(--green); }
.hm-trend.down { color: var(--red); }
.hm-players { font-size: 10px; color: rgba(255,255,255,0.5); margin-top: 2px; }

/* Positive heatmap colors */
.hm-buzz-viral { background: linear-gradient(135deg, #ec4899, #be185d); }
.hm-buzz-hot { background: linear-gradient(135deg, #f59e0b, #d97706); }
.hm-buzz-warm { background: linear-gradient(135deg, #22c55e, #15803d); }
.hm-buzz-rising { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
.hm-buzz-chill { background: linear-gradient(135deg, #475569, #334155); }

/* ─── Modal ─── */
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center; padding: 20px; backdrop-filter: blur(4px); }
.modal-overlay.show { display: flex; }
.modal { background: var(--bg-card); border: 1px solid var(--border); border-radius: 16px; max-width: 600px; width: 100%; max-height: 80vh; overflow-y: auto; padding: 24px; animation: slideDown 0.3s ease; }
.modal-close { float: right; background: none; border: 1px solid var(--border); border-radius: 8px; color: var(--text-muted); cursor: pointer; padding: 4px 12px; font-size: 14px; }
.modal-close:hover { border-color: var(--red); color: var(--red); }
.modal h3 { font-size: 18px; margin-bottom: 12px; padding-right: 40px; line-height: 1.4; }
.modal-meta { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
.modal-body { font-size: 14px; line-height: 1.7; color: var(--text-secondary); }
.modal-body p { margin-bottom: 12px; }

/* Heatmap detail overlay */
.hm-detail-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px; opacity: 0; pointer-events: none; transition: opacity 0.25s; backdrop-filter: blur(4px); }
.hm-detail-overlay.open { opacity: 1; pointer-events: auto; }
.hm-detail-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 16px; width: 100%; max-width: 480px; padding: 28px 24px; position: relative; animation: slideDown 0.3s ease; }
.hm-d-close { position: absolute; top: 14px; right: 14px; background: none; border: 1px solid var(--border); border-radius: 8px; color: var(--text-muted); cursor: pointer; font-size: 18px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
.hm-d-close:hover { border-color: var(--red); color: var(--red); }
.hm-detail-card h3 { font-size: 18px; font-weight: 700; margin-bottom: 12px; padding-right: 40px; }
.hm-d-badges { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 16px; }
.hm-d-badge { padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
.hm-d-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 16px; }
.hm-d-stat { background: var(--bg-card-alt); border-radius: 8px; padding: 12px; text-align: center; }
.hm-d-stat .lab { font-size: 10px; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px; }
.hm-d-stat .val { font-size: 22px; font-weight: 700; color: var(--accent); }
.hm-d-summary { font-size: 13px; color: var(--text-secondary); line-height: 1.6; margin-bottom: 16px; padding: 12px; background: var(--bg-card-alt); border-radius: 8px; }
.hm-d-link { display: block; text-align: center; color: var(--accent); text-decoration: none; font-size: 13px; padding: 10px; border: 1px solid var(--border); border-radius: 8px; transition: 0.2s; }
.hm-d-link:hover { background: var(--accent-glow); border-color: var(--accent); }

/* ─── Agent Panel ─── */
/* Observer observation cards */
.obs-card {
  background: var(--bg-card-alt);
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 16px 20px;
  margin-bottom: 14px;
  transition: border-color .3s, transform .15s;
}
.obs-card:last-child { margin-bottom: 0; }
.obs-card:hover { border-color: var(--border-hover); transform: translateY(-1px); }
.obs-card-text {
  font-size: 14px;
  color: var(--text-primary);
  line-height: 1.7;
  font-weight: 400;
}
.obs-card-text strong { color: var(--accent); font-weight: 600; }
.obs-card-meta {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: 10px;
  font-size: 11px;
  color: var(--text-muted);
}
.obs-tag {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}
.obs-tag-trend { background: rgba(34,197,94,.12); color: var(--accent); }
.obs-tag-buzz { background: rgba(245,158,11,.12); color: var(--gold); }
.obs-tag-gem { background: rgba(236,72,153,.12); color: var(--pink); }
.obs-tag-mood { background: rgba(59,130,246,.12); color: var(--blue); }
.obs-tag-insight { background: rgba(168,85,247,.12); color: var(--purple); }
.obs-summary-line {
  font-size: 13px;
  color: var(--text-secondary);
  margin-bottom: 16px;
  padding-bottom: 12px;
  border-bottom: 1px solid rgba(255,255,255,.05);
}
.obs-summary-line strong { color: var(--text-primary); font-weight: 600; }
/* Mood ring */
.obs-mood-ring {
  position: relative;
  width: 120px;
  height: 120px;
  margin: 0 auto;
}
.obs-mood-val {
  position: absolute;
  top: 50%; left: 50%;
  transform: translate(-50%,-50%);
  text-align: center;
}
/* Insights grid - 3 columns */
.obs-insights-grid {
  grid-template-columns: 1fr 1fr 1fr !important;
}
@media(max-width:900px) {
  .obs-insights-grid { grid-template-columns: 1fr !important; }
}

.agent-panel { grid-column: 1 / -1; }
.agent-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; }
.agent-stat { background: var(--bg-card-alt); border-radius: 8px; padding: 14px; text-align: center; }
.agent-stat .val { font-size: 22px; font-weight: 700; color: var(--accent); }
.agent-stat .lbl { font-size: 11px; color: var(--text-muted); margin-top: 2px; }

/* ─── Footer ─── */
.footer {
  text-align: center; padding: 20px; color: var(--text-muted); font-size: 12px;
  border-top: 1px solid var(--border); max-width: 1400px; margin: 0 auto;
}
.footer a { color: var(--accent); text-decoration: none; }

/* ─── Loading ─── */
.loading { display: flex; align-items: center; justify-content: center; padding: 40px; color: var(--text-muted); }
.spinner { width: 20px; height: 20px; border: 2px solid var(--border); border-top-color: var(--accent); border-radius: 50%; animation: spin 0.8s linear infinite; margin-right: 10px; }
@keyframes spin { 100% { transform: rotate(360deg); } }

/* ─── Cookie Banner ─── */
.cookie-banner { display: none; position: fixed; bottom: 0; left: 0; right: 0; z-index: 200; background: var(--bg-card); border-top: 1px solid var(--border); padding: 14px 24px; text-align: center; font-size: 13px; }
.cookie-banner.show { display: block; }
.cookie-banner button { margin-left: 12px; padding: 6px 16px; border-radius: 6px; border: 1px solid var(--accent); background: var(--accent); color: #fff; cursor: pointer; font-size: 12px; }
.cookie-banner .btn-reject { background: transparent; color: var(--text-muted); border-color: var(--border); }

/* ─── Responsive ─── */
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
</style>


<main class="main">
  <h1 style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap">Community Buzz &amp; Sentiment — GripNews Gaming Intelligence</h1>

  <!-- Stat Cards -->
  <div class="stats-grid" id="statsGrid">
    <div class="stat-card positive" data-filter="all" onclick="drillDown('all')">
      <div class="label">Last 24 Hours</div>
      <div class="value" id="statTotal"><span class="spinner" style="display:inline-block;width:16px;height:16px;vertical-align:middle;margin:0"></span></div>
      <div class="sub">Discussions tracked</div>
      <div class="tap-hint">Tap to view all</div>
    </div>
    <div class="stat-card analysed" data-filter="games" onclick="drillDown('games')">
      <div class="label">Games Active</div>
      <div class="value" id="statGamesActive">—</div>
      <div class="sub">In last 24 hours</div>
      <div class="tap-hint">Tap to view games</div>
    </div>
    <div class="stat-card weekly" data-filter="weekly" onclick="drillDown('weekly')">
      <div class="label">This Week</div>
      <div class="value" id="statWeekly">—</div>
      <div class="sub">Discussions tracked</div>
      <div class="tap-hint">Tap to view recent</div>
    </div>
    <div class="stat-card viral" data-filter="viral" onclick="drillDown('viral')">
      <div class="label">Rising</div>
      <div class="value" id="statRising">—</div>
      <div class="sub">Gaining momentum</div>
      <div class="tap-hint">Tap to view rising</div>
    </div>
    <div class="stat-card highlights" data-filter="highlights" onclick="drillDown('highlights')">
      <div class="label">Highlights</div>
      <div class="value" id="statHighlights">—</div>
      <div class="sub">Community spotlights</div>
      <div class="tap-hint">Tap to view highlights</div>
    </div>
    <div class="stat-card hype" data-filter="hype" onclick="drillDown('hype')">
      <div class="label">Signals</div>
      <div class="value" id="statSignals">—</div>
      <div class="sub">Active intelligence</div>
      <div class="tap-hint">Tap to view signals</div>
    </div>
    <div class="stat-card games" data-filter="games" onclick="drillDown('games')">
      <div class="label">Total Tracked</div>
      <div class="value" id="statTotalAllTime">—</div>
      <div class="sub">Since crawling began</div>
      <div class="tap-hint">Tap to view games</div>
    </div>
    <div class="stat-card categories" data-filter="categories" onclick="drillDown('categories')">
      <div class="label">Categories</div>
      <div class="value" id="statCategories">—</div>
      <div class="sub">Types tracked</div>
      <div class="tap-hint">Tap to view breakdown</div>
    </div>
  </div>

  <!-- Drill-down Panel -->
  <div class="drill-panel" id="drillPanel">
    <div class="drill-header">
      <h2 id="drillTitle">Filtered Mentions</h2>
      <button class="drill-close" onclick="closeDrill()">✕ Close</button>
    </div>
    <div class="drill-body" id="drillBody">
      <div class="loading"><span class="spinner"></span>Loading…</div>
    </div>
  </div>

  <!-- Latest Highlights (like Fix Guides) -->
  <div class="panel" style="margin-bottom:24px;">
    <div class="panel-header">
      <h2>✨ Latest Community Highlights</h2>
      <span class="badge" id="highlightsCount">loading…</span>
    </div>
    <div class="panel-body" style="max-height:none;overflow:visible;padding-bottom:8px;">
      <div class="guides-grid" id="highlightsGrid">
        <div class="loading"><span class="spinner"></span>Loading highlights…</div>
      </div>
      <button class="guides-load-more" id="highlightsMore" onclick="loadMoreHighlights()">Load more highlights ▾</button>
    </div>
  </div>

  <!-- GripAi Observations (from observer API) -->
  <div class="panel" style="margin-bottom:24px;" id="observerPanel">
    <div class="panel-header">
      <h2>🧠 GripAi Observations</h2>
      <span class="badge" id="obsBadge">loading…</span>
    </div>
    <div class="panel-body" style="max-height:none;overflow:visible;" id="observerBody">
      <div class="loading"><span class="spinner"></span>Gathering observations…</div>
    </div>
  </div>

  <!-- Observer Insights Row: Mood + Hidden Gem + Biggest Trend -->
  <div class="content-grid obs-insights-grid" id="obsInsights" style="display:none;">
    <div class="panel">
      <div class="panel-header"><h2>💚 Community Mood</h2><span class="badge">live</span></div>
      <div class="panel-body" style="text-align:center;padding:24px 16px;">
        <div class="obs-mood-ring">
          <svg viewBox="0 0 120 120" width="120" height="120" style="transform:rotate(-90deg)">
            <circle cx="60" cy="60" r="52" fill="none" stroke="rgba(34,197,94,0.15)" stroke-width="8"/>
            <circle id="obsMoodArc" cx="60" cy="60" r="52" fill="none" stroke="var(--accent)" stroke-width="8" stroke-linecap="round"
              stroke-dasharray="326.7" stroke-dashoffset="326.7" style="transition:stroke-dashoffset 1.2s ease"/>
          </svg>
          <div class="obs-mood-val">
            <div id="obsMoodPct" style="font-size:28px;font-weight:700;color:var(--text-primary);line-height:1">—</div>
            <div style="font-size:10px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-top:2px">positive</div>
          </div>
        </div>
        <p id="obsMoodDesc" style="font-size:13px;color:var(--text-secondary);margin-top:12px;line-height:1.5;max-width:280px;margin-left:auto;margin-right:auto"></p>
      </div>
    </div>
    <div class="panel">
      <div class="panel-header"><h2>💎 Hidden Gem</h2><span class="badge">discovered</span></div>
      <div class="panel-body" style="text-align:center;padding:24px 16px;">
        <div id="obsGemName" style="font-size:18px;font-weight:700;color:var(--pink);margin-bottom:8px"></div>
        <p id="obsGemText" style="font-size:13px;color:var(--text-secondary);line-height:1.6"></p>
      </div>
    </div>
    <div class="panel">
      <div class="panel-header"><h2>📈 Biggest Trend</h2><span class="badge">trending</span></div>
      <div class="panel-body" style="text-align:center;padding:24px 16px;">
        <div id="obsTrendName" style="font-size:18px;font-weight:700;color:var(--accent);margin-bottom:8px"></div>
        <p id="obsTrendText" style="font-size:13px;color:var(--text-secondary);line-height:1.6"></p>
      </div>
    </div>
  </div>

  <!-- Two-column: Categories + Sentiment Doughnut -->
  <div class="content-grid">
    <div class="panel">
      <div class="panel-header">
        <h2>🏷️ Sentiment Categories</h2>
        <span class="badge" id="catCount">—</span>
      </div>
      <div class="panel-body" id="categoriesBody">
        <div class="loading"><span class="spinner"></span>Loading…</div>
      </div>
    </div>
    <div class="panel">
      <div class="panel-header">
        <h2>📊 Buzz Distribution</h2>
        <span class="badge">live</span>
      </div>
      <div class="panel-body" id="buzzChart">
        <div class="loading"><span class="spinner"></span>Loading…</div>
      </div>
    </div>
  </div>

  <!-- Game Buzz Heatmap -->
  <div class="panel" style="margin-bottom:24px;" id="heatmapPanel">
    <div class="panel-header">
      <h2>🔥 Game Buzz Heatmap</h2>
      <span class="badge" id="heatmapCount">loading…</span>
    </div>
    <div class="panel-body" id="heatmapBody">
      <div class="loading"><span class="spinner"></span>Loading heatmap…</div>
    </div>
  </div>

  <!-- Latest Positive Signals Table -->
  <div class="panel" style="margin-bottom:24px;">
    <div class="panel-header">
      <h2>💚 Latest Positive Signals</h2>
      <span class="badge" id="signalCount">—</span>
    </div>
    <div class="panel-body" id="signalsBody" style="max-height:600px;">
      <div class="loading"><span class="spinner"></span>Loading signals…</div>
    </div>
  </div>

  <!-- GripAi Agent Stats -->
  <div class="content-grid">
    <div class="panel agent-panel">
      <div class="panel-header"><h2>🤖 GripAi Sentiment Agent</h2><span class="badge">live</span></div>
      <div class="panel-body">
        <div class="agent-grid" id="agentGrid">
          <div class="agent-stat"><div class="val" id="agentSources">142</div><div class="lbl">Sources Crawled</div></div>
          <div class="agent-stat"><div class="val" id="agentProcessed">—</div><div class="lbl">Posts Processed</div></div>
          <div class="agent-stat"><div class="val" id="agentPositive">—</div><div class="lbl">Positive Detected</div></div>
          <div class="agent-stat"><div class="val" id="agentAccuracy">94%</div><div class="lbl">Sentiment Accuracy</div></div>
          <div class="agent-stat"><div class="val" id="agentUptime">24/7</div><div class="lbl">Monitoring</div></div>
          <div class="agent-stat"><div class="val" id="agentLatency">&lt;2m</div><div class="lbl">Detection Latency</div></div>
        </div>
      </div>
    </div>
  </div>

</main>

<!-- Heatmap Detail Overlay -->
<div class="hm-detail-overlay" id="hmDetailOverlay" onclick="if(event.target===this)closeHmDetail()">
  <div class="hm-detail-card" id="hmDetailContent"></div>
</div>

<!-- Guide/Highlight Detail Modal -->
<div class="modal-overlay" id="modalOverlay" onclick="if(event.target===this)closeModal()">
  <div class="modal" id="modalContent"></div>
</div>


<!-- Cookie Banner -->
<div class="cookie-banner" id="cookieBanner">
  We use cookies for analytics.
  <button onclick="acceptCookies()">Accept</button>
  <button class="btn-reject" onclick="rejectCookies()">Reject</button>
</div>


<script>
/* ── Community Buzz & Sentiment Logic ────────────────────────────────────── */
// ─── Config ───
const API = "https://gripai.uk/api";
const HOTSPOTS_API = "/proxy.php?endpoint=hotspots&limit=200";

// Positive sentiment category icons (flipped from Sandbox's negative ones)
const CATEGORY_ICONS = {
  praise: '🌟', recommendation: '👍', hype: '🔥', community: '🤝',
  achievement: '🏆', update_love: '💖', visual_praise: '🎨', gameplay_love: '🎮',
  nostalgia: '💭', funny: '😂', creative: '🎨', esports: '⚡',
  // Fallbacks from original categories
  crash:'💥', gameplay:'🎮', audio:'🔊', network:'🌐', account:'👤', unknown:'🔍',
  performance:'⚡', graphics:'🖥️', input:'🕹️', security:'🔒', visual:'👁️', freeze:'🧊',
  stability:'🔧', save_data:'💾'
};

// Positive sentiment labels (replaces severity)
const BUZZ_ORDER = ['viral','hype','positive','neutral'];
const BUZZ_COLORS = { viral:'#ec4899', hype:'#f59e0b', positive:'#22c55e', neutral:'#3b82f6' };

let refreshTimer, highlightsPage = 1, allHighlightsCache = [], allSignalsCache = [], highlightCount = 0;

// ─── Utilities ───
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

function buzzLabel(val) {
  const n = parseFloat(val);
  if (n >= 0.7) return 'viral';
  if (n >= 0.5) return 'hype';
  if (n >= 0.3) return 'positive';
  return 'neutral';
}

function prettifyGameName(slug) {
  return slug.replace(/-/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
}

// ─── Update Clock ───
function updateClock() {
  const now = new Date();
  const el = document.getElementById('lastRefresh');
  if (el) el.textContent = 'Updated: ' + now.toTimeString().split(' ')[0];
}

// ─── Load All Data ───
async function loadAllData() {
  updateClock();

  const [issuesData, blogData] = await Promise.all([
    fetchJSON(API + '/issues?limit=200'),
    fetchJSON(API + '/blog?limit=30')
  ]);

  if (issuesData) {
    allSignalsCache = issuesData.issues || [];
    // Stats are populated by loadObservations() using honest observer data.
    // Here we just compute what we can from the returned issues for detail panels.
    const catSet = new Set(allSignalsCache.map(i => i.category));
    document.getElementById('statCategories').textContent = fmt(catSet.size);

    // Agent stats — use returned sample size
    document.getElementById('agentProcessed').textContent = fmt(allSignalsCache.length);
    document.getElementById('agentPositive').textContent = fmt(allSignalsCache.filter(i => i.pipeline_status === 'enriched').length);

    renderCategories(allSignalsCache);
    renderBuzzChart(allSignalsCache);
    renderSignalsTable(allSignalsCache.slice(0, 50));
    loadHeatmap();
  }

  if (blogData) {
    allHighlightsCache = blogData.posts || [];
    highlightCount = allHighlightsCache.length;
    document.getElementById('statHighlights').textContent = fmt(highlightCount);
    renderHighlights(allHighlightsCache.slice(0, 12));
    document.getElementById('highlightsCount').textContent = highlightCount + ' latest';
    if (allHighlightsCache.length > 12) {
      document.getElementById('highlightsMore').style.display = 'block';
    }
  }

  // Refresh every 30s
  clearTimeout(refreshTimer);
  refreshTimer = setTimeout(loadAllData, 30000);
}

// ─── Categories ───
function renderCategories(signals) {
  const catMap = {};
  signals.forEach(i => { catMap[i.category] = (catMap[i.category] || 0) + 1; });
  const cats = Object.entries(catMap).sort((a, b) => b[1] - a[1]);
  const maxCount = cats.length ? cats[0][1] : 1;
  const el = document.getElementById('categoriesBody');
  document.getElementById('catCount').textContent = cats.length + ' types';
  el.innerHTML = cats.map(([cat, cnt]) => {
    const pct = (cnt / maxCount * 100);
    const icon = CATEGORY_ICONS[cat] || '💬';
    return `<div class="cat-row">
      <span class="cat-icon">${icon}</span>
      <div class="cat-info"><div class="cat-name">${cat.replace(/_/g, ' ')}</div><div class="cat-bar-track"><div class="cat-bar-fill" style="width:${pct}%"></div></div></div>
      <span class="cat-count">${cnt}</span>
    </div>`;
  }).join('');
}

// ─── Buzz Doughnut ───
function renderBuzzChart(signals) {
  const el = document.getElementById('buzzChart');
  const counts = { viral: 0, hype: 0, positive: 0, neutral: 0 };
  signals.forEach(i => { counts[buzzLabel(i.severity)]++; });
  const total = counts.viral + counts.hype + counts.positive + counts.neutral;
  el.innerHTML = `<div class="chart-container"><div class="doughnut-wrap"><canvas id="doughnutCanvas" width="160" height="160"></canvas>
    <div class="doughnut-center"><div class="big">${total}</div><div class="small">SIGNALS</div></div></div>
    <div class="legend">${BUZZ_ORDER.map(s => `<div class="legend-item">
      <span class="legend-dot" style="background:${BUZZ_COLORS[s]}"></span><span>${s.charAt(0).toUpperCase()+s.slice(1)}</span>
      <span class="legend-count" style="color:${BUZZ_COLORS[s]}">${counts[s]}</span></div>`).join('')}</div></div>`;
  const canvas = document.getElementById('doughnutCanvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d'), cx = 80, cy = 80, r = 60, w = 16;
  let start = -Math.PI / 2;
  BUZZ_ORDER.forEach(s => {
    const angle = total > 0 ? (counts[s] / total) * Math.PI * 2 : 0;
    if (angle > 0) { ctx.beginPath(); ctx.arc(cx, cy, r, start, start + angle); ctx.lineWidth = w; ctx.strokeStyle = BUZZ_COLORS[s]; ctx.lineCap = 'round'; ctx.stroke(); start += angle + 0.03; }
  });
}

// ─── Signals Table ───
function renderSignalsTable(signals) {
  const el = document.getElementById('signalsBody');
  document.getElementById('signalCount').textContent = signals.length + ' latest';
  if (!signals.length) { el.innerHTML = '<p style="color:var(--text-muted);text-align:center;padding:20px">No signals found</p>'; return; }
  el.innerHTML = `<table class="report-table">
    <thead><tr><th>Buzz</th><th>Game</th><th>Category</th><th>Confidence</th><th>Detected</th></tr></thead>
    <tbody>${signals.map((r, idx) => {
      const buzz = buzzLabel(r.severity);
      const icon = CATEGORY_ICONS[r.category] || '💬';
      return `<tr onclick="showSignalDetail(${idx})" style="cursor:pointer">
        <td><span class="pill pill-${buzz}">${buzz}</span></td>
        <td class="report-title" title="${esc(r.game_name)}">${esc(r.game_name)}</td>
        <td style="font-size:12px;color:var(--text-secondary)">${icon} ${(r.category||'—').replace(/_/g,' ')}</td>
        <td style="font-size:12px;color:var(--text-secondary)">${(parseFloat(r.confidence)*100).toFixed(0)}%</td>
        <td style="color:var(--text-muted);font-size:11px;white-space:nowrap">${r.created_at ? timeAgo(r.created_at) : '—'}</td>
      </tr>`;
    }).join('')}</tbody></table>`;
}

// ─── Signal Detail Modal ───
function showSignalDetail(idx) {
  const signals = allSignalsCache.slice(0, 50);
  const r = signals[idx];
  if (!r) return;
  const buzz = buzzLabel(r.severity);
  const icon = CATEGORY_ICONS[r.category] || '💬';
  const overlay = document.getElementById('modalOverlay');
  const content = document.getElementById('modalContent');
  let summary = (r.summary || r.title || 'Positive signal detected').replace(/\[crawl:[^\]]+\]\s*/g, '');

  content.innerHTML = `
    <button class="modal-close" onclick="closeModal()">&times;</button>
    <h3>${icon} ${esc(r.game_name)}</h3>
    <div class="modal-meta">
      <span class="pill pill-${buzz}">${buzz}</span>
      <span class="pill pill-highlight">${(r.category||'unknown').replace(/_/g,' ')}</span>
      <span style="color:var(--text-muted);font-size:11px;">${r.created_at ? timeAgo(r.created_at) : ''}</span>
    </div>
    <div class="modal-body">
      <p>${esc(summary)}</p>
      <p><strong>Confidence:</strong> ${(parseFloat(r.confidence)*100).toFixed(0)}% · <strong>Buzz Score:</strong> ${parseFloat(r.severity).toFixed(2)}</p>
    </div>
  `;
  overlay.classList.add('show');
}

function closeModal() {
  document.getElementById('modalOverlay').classList.remove('show');
}

// ─── Highlights (like Fix Guides) ───
function highlightPillLabel(slug) {
  if (slug && slug.startsWith('crawl-digest')) return 'intel digest';
  return 'highlight';
}
function highlightPillClass(slug) {
  if (slug && slug.startsWith('crawl-digest')) return 'pill-info';
  return 'pill-praise';
}
function highlightLink(slug) {
  if (slug && slug.startsWith('crawl-digest')) return 'https://gripnews.uk';
  return 'https://gripai.uk';
}
function highlightLinkLabel(slug) {
  if (slug && slug.startsWith('crawl-digest')) return 'View on GripNews →';
  return 'View on GripAi →';
}

function renderHighlights(highlights) {
  const grid = document.getElementById('highlightsGrid');
  grid.innerHTML = highlights.map((g, i) => {
    const link = highlightLink(g.slug);
    const label = highlightLinkLabel(g.slug);
    const pill = highlightPillLabel(g.slug);
    const pillCls = highlightPillClass(g.slug);
    return `<div class="guide-card" onclick="showHighlightDetail(${i})">
      <div class="guide-top">
        <span class="pill ${pillCls}">${pill}</span>
        <span class="guide-time">${g.created_at ? timeAgo(g.created_at) : ''}</span>
      </div>
      <div class="guide-title">${esc(g.title)}</div>
      <div class="guide-excerpt">${esc(g.excerpt)}</div>

    </div>`;
  }).join('');
}

let highlightsShown = 12;
function loadMoreHighlights() {
  highlightsShown += 12;
  renderHighlights(allHighlightsCache.slice(0, highlightsShown));
  if (highlightsShown >= allHighlightsCache.length) {
    document.getElementById('highlightsMore').style.display = 'none';
  }
}

function showHighlightDetail(idx) {
  const g = allHighlightsCache[idx];
  if (!g) return;
  const overlay = document.getElementById('modalOverlay');
  const content = document.getElementById('modalContent');
  const pill = highlightPillLabel(g.slug);
  const pillCls = highlightPillClass(g.slug);
  const link = highlightLink(g.slug);

  content.innerHTML = `
    <button class="modal-close" onclick="closeModal()">&times;</button>
    <h3>${esc(g.title)}</h3>
    <div class="modal-meta">
      <span class="pill ${pillCls}">${pill}</span>
      <span style="color:var(--text-muted);font-size:11px">${g.created_at ? timeAgo(g.created_at) : ''}</span>
    </div>
    <div class="modal-body">
      <p>${esc(g.excerpt || g.content || 'No details available.')}</p>

    </div>
  `;
  overlay.classList.add('show');
}

// ─── Heatmap ───
let hotspotCache = [];

function hmBuzzClass(severity, heat) {
  if (heat && heat.includes('Escalating')) return 'hm-buzz-viral';
  if (severity >= 6.0) return 'hm-buzz-hot';
  if (severity >= 4.5) return 'hm-buzz-warm';
  if (severity >= 3.0) return 'hm-buzz-rising';
  return 'hm-buzz-chill';
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

async function loadHeatmap() {
  try {
    const res = await fetch(HOTSPOTS_API, { cache: 'no-store' });
    if (!res.ok) throw new Error(res.status);
    hotspotCache = await res.json();
  } catch(e) {
    console.warn('Heatmap fetch failed:', e);
    document.getElementById('heatmapBody').innerHTML = '<div style="padding:16px;color:var(--text-muted);">Unable to load buzz data</div>';
    return;
  }

  const gameMap = {};
  hotspotCache.forEach(h => {
    if (h.game_id === 'unknown' || !h.game_name) return;
    const existing = gameMap[h.game_id];
    if (!existing || h.severity > existing.severity) gameMap[h.game_id] = h;
  });

  const games = Object.values(gameMap).sort((a, b) => b.severity - a.severity);
  document.getElementById('heatmapCount').textContent = games.length + ' games';

  if (games.length === 0) {
    document.getElementById('heatmapBody').innerHTML = '<div style="padding:16px;color:var(--text-muted);">No active buzz detected</div>';
    return;
  }

  let html = '<div class="heatmap-grid">';
  games.forEach((g, i) => {
    const cls = hmBuzzClass(g.severity, g.heat_level);
    const name = prettifyGameName(g.game_name);
    const trend = hmTrendIcon(g.trend);
    html += `<div class="hm-tile ${cls}" onclick="showHmDetail(${i})" title="${esc(name)} — Buzz ${g.severity}">
      <div>
        <div class="hm-name">${esc(name)}</div>
        <div class="hm-cat">${esc(g.category)}</div>
      </div>
      <div class="hm-bottom">
        <div class="hm-meta"><span class="hm-sev">${g.severity.toFixed(1)}</span>${trend}</div>
        <div class="hm-players">${g.players_affected ? g.players_affected + ' mentions' : ''}</div>
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
  const cls = hmBuzzClass(g.severity, g.heat_level);
  const heatText = (g.heat_level || '').replace(/[^\w\s]/g, '').trim();
  const movText = g.severity_movement ? g.severity_movement.charAt(0).toUpperCase() + g.severity_movement.slice(1) : '—';
  let summary = (g.summary || 'Positive buzz detected for this title.').replace(/\[crawl:[^\]]+\]\s*/g, '');
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
      <div class="hm-d-stat"><div class="lab">Buzz Score</div><div class="val">${g.severity.toFixed(1)}</div></div>
      <div class="hm-d-stat"><div class="lab">PSI</div><div class="val">${g.psi ? g.psi.toFixed(1) : '—'}</div></div>
      <div class="hm-d-stat"><div class="lab">Trend</div><div class="val" style="font-size:16px;">${g.trend || '—'} ${esc(movText)}</div></div>
    </div>
    <div class="hm-d-stats" style="grid-template-columns:1fr 1fr;">
      <div class="hm-d-stat"><div class="lab">Mentions</div><div class="val">${g.players_affected || '—'}</div></div>
      <div class="hm-d-stat"><div class="lab">Verified Positive</div><div class="val" style="font-size:16px;">${g.verified_fix ? '✅ Yes' : '⏳ Pending'}</div></div>
    </div>
    <div class="hm-d-summary">${esc(summary)}</div>
    
  `;
  overlay.classList.add('open');
}

function closeHmDetail() {
  document.getElementById('hmDetailOverlay').classList.remove('open');
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
      filtered = allSignalsCache;
      titleText = '💬 All Positive Mentions';
      break;
    case 'analysed':
      filtered = allSignalsCache.filter(i => i.pipeline_status === 'enriched');
      titleText = '🤖 AI-Analysed Signals';
      break;
    case 'highlights':
      titleText = '✨ Community Highlights';
      body.innerHTML = allHighlightsCache.length ? `<table class="report-table">
        <thead><tr><th>Title</th><th>Type</th><th>Posted</th></tr></thead>
        <tbody>${allHighlightsCache.slice(0,50).map(g => `<tr>
          <td class="report-title" title="${esc(g.title)}">${esc(g.title)}</td>
          <td><span class="pill pill-praise">${g.status||'published'}</span></td>
          <td style="color:var(--text-muted);font-size:11px;white-space:nowrap">${g.created_at ? timeAgo(g.created_at) : '—'}</td>
        </tr>`).join('')}</tbody></table>
        <p style="color:var(--text-muted);font-size:11px;text-align:right;margin-top:8px">${fmt(highlightCount)} total highlights</p>` : '<p style="color:var(--text-muted);text-align:center;padding:20px">No highlights found</p>';
      title.innerHTML = titleText;
      panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      return;
    case 'viral':
      filtered = allSignalsCache.filter(i => parseFloat(i.severity) >= 0.7);
      titleText = '🔥 Viral Signals';
      break;
    case 'weekly':
      const weekAgo = Date.now() - 7 * 86400000;
      filtered = allSignalsCache.filter(i => new Date(i.created_at + (i.created_at.includes('Z') ? '' : 'Z')).getTime() > weekAgo);
      titleText = '📅 This Week\'s Buzz';
      break;
    case 'hype':
      filtered = allSignalsCache.filter(i => { const s = parseFloat(i.severity); return s >= 0.5 && s < 0.7; });
      titleText = '🚀 Hype Signals';
      break;
    case 'games':
      titleText = '🎮 Games with Positive Chatter';
      const gameMap = {};
      allSignalsCache.forEach(i => { gameMap[i.game_name] = (gameMap[i.game_name] || 0) + 1; });
      const games = Object.entries(gameMap).sort((a, b) => b[1] - a[1]);
      body.innerHTML = '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px">' +
        games.map(([name, cnt]) => `<div style="background:var(--bg-card-alt);border-radius:8px;padding:14px;text-align:center">
          <div style="font-size:14px;font-weight:600">${esc(name)}</div>
          <div style="font-size:24px;font-weight:700;color:var(--accent);margin:4px 0">${cnt}</div>
          <div style="font-size:11px;color:var(--text-muted)">positive mentions</div>
        </div>`).join('') + '</div>';
      title.innerHTML = titleText;
      panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      return;
    case 'categories':
      titleText = '🏷️ Category Breakdown';
      const catMap = {};
      allSignalsCache.forEach(i => { catMap[i.category] = (catMap[i.category] || 0) + 1; });
      const total = allSignalsCache.length;
      const cats = Object.entries(catMap).sort((a, b) => b[1] - a[1]);
      body.innerHTML = '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px">' +
        cats.map(([cat, cnt]) => {
          const icon = CATEGORY_ICONS[cat] || '💬';
          const pct = total > 0 ? (cnt/total*100).toFixed(1) : 0;
          return `<div style="background:var(--bg-card-alt);border-radius:8px;padding:14px;text-align:center">
            <div style="font-size:28px;margin-bottom:6px">${icon}</div>
            <div style="font-size:14px;font-weight:600;text-transform:capitalize">${cat.replace(/_/g,' ')}</div>
            <div style="font-size:24px;font-weight:700;color:var(--accent);margin:4px 0">${cnt}</div>
            <div style="font-size:11px;color:var(--text-muted)">${pct}% of signals</div>
          </div>`;
        }).join('') + '</div>';
      title.innerHTML = titleText;
      panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      return;
  }

  title.innerHTML = titleText;
  if (filtered.length === 0) {
    body.innerHTML = '<p style="color:var(--text-muted);text-align:center;padding:20px">No signals found for this filter</p>';
  } else {
    body.innerHTML = `<table class="report-table">
      <thead><tr><th>Buzz</th><th>Game</th><th>Category</th><th>Confidence</th><th>Detected</th></tr></thead>
      <tbody>${filtered.slice(0,100).map(r => {
        const buzz = buzzLabel(r.severity);
        const icon = CATEGORY_ICONS[r.category] || '💬';
        return `<tr>
          <td><span class="pill pill-${buzz}">${buzz}</span></td>
          <td class="report-title" title="${esc(r.game_name)}">${esc(r.game_name)}</td>
          <td style="font-size:12px;color:var(--text-secondary)">${icon} ${(r.category||'—').replace(/_/g,' ')}</td>
          <td style="font-size:12px;color:var(--text-secondary)">${(parseFloat(r.confidence)*100).toFixed(0)}%</td>
          <td style="color:var(--text-muted);font-size:11px;white-space:nowrap">${r.created_at ? timeAgo(r.created_at) : '—'}</td>
        </tr>`;
      }).join('')}</tbody></table>
      <p style="color:var(--text-muted);font-size:11px;text-align:right;margin-top:8px">Showing ${Math.min(filtered.length, 100)} of ${filtered.length} signals</p>`;
  }
  panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function closeDrill() {
  document.getElementById('drillPanel').classList.remove('show');
  document.querySelectorAll('.stat-card').forEach(c => c.classList.remove('active'));
}

// ─── Cookie Consent ───
function acceptCookies() {
  document.cookie = 'gg_consent=1;max-age=31536000;path=/';
  document.getElementById('cookieBanner').classList.remove('show');
  loadGA();
}
function rejectCookies() {
  document.cookie = 'gg_consent=0;max-age=31536000;path=/';
  document.getElementById('cookieBanner').classList.remove('show');
}
if (document.cookie.indexOf('gg_consent') === -1) {
  document.getElementById('cookieBanner').classList.add('show');
}

// ─── Observer API ───
const OBSERVER_API = "https://gripai.uk/chatter/observations";
const OBSERVER_FALLBACK = "/proxy.php?endpoint=observations";

async function loadObservations() {
  let data;
  try {
    data = await fetchJSON(OBSERVER_API);
  } catch(e) {}
  if (!data || !data.observations) {
    try { data = await fetchJSON(OBSERVER_FALLBACK); } catch(e) {}
  }
  if (!data || !data.observations) {
    document.getElementById('observerBody').innerHTML = '<p style="color:var(--text-muted);text-align:center;padding:16px">Observations unavailable — will retry shortly</p>';
    return;
  }

  const tagCls = { trend:'obs-tag-trend', buzz:'obs-tag-buzz', gem:'obs-tag-gem', mood:'obs-tag-mood', insight:'obs-tag-insight' };

  // Summary
  let summaryHtml = '';
  if (data.summary) {
    summaryHtml = '<div class="obs-summary-line">' + data.summary + '</div>';
  }

  // Observation cards
  const cardsHtml = (data.observations || []).map(o => {
    const cls = tagCls[o.tag] || 'obs-tag-insight';
    return `<div class="obs-card">
      <div class="obs-card-text">${o.text}</div>
      <div class="obs-card-meta">
        <span class="obs-tag ${cls}">${esc(o.tagLabel || o.tag || 'note')}</span>
        <span>${esc(o.time || '')}</span>
      </div>
    </div>`;
  }).join('');

  document.getElementById('observerBody').innerHTML = summaryHtml + cardsHtml;
  document.getElementById('obsBadge').textContent = (data.observations || []).length + ' observations';

  // Populate stats grid with honest observer numbers
  const snap = data.dataSnapshot || {};
  const recent = snap.last24h || {};
  if (recent.issues) document.getElementById('statTotal').textContent = fmt(recent.issues);
  if (recent.games) document.getElementById('statGamesActive').textContent = fmt(recent.games);
  if (recent.rising) document.getElementById('statRising').textContent = fmt(recent.rising);
  if (snap.totalIssues) document.getElementById('statTotalAllTime').textContent = fmt(snap.totalIssues);
  const weekly = snap.last7d || {};
  if (weekly.issues) document.getElementById('statWeekly').textContent = fmt(weekly.issues);
  if (snap.activeSignals !== undefined) document.getElementById('statSignals').textContent = fmt(snap.activeSignals);

  // Mood ring
  const mood = data.mood || {};
  if (mood.pct) {
    document.getElementById('obsMoodPct').textContent = mood.pct + '%';
    document.getElementById('obsMoodDesc').textContent = mood.description || '';
    const circ = 2 * Math.PI * 52; // ~326.7
    const offset = circ - (mood.pct / 100) * circ;
    setTimeout(() => { document.getElementById('obsMoodArc').style.strokeDashoffset = offset; }, 200);
  }

  // Hidden gem
  const gem = data.hiddenGem || {};
  if (gem.name) {
    document.getElementById('obsGemName').textContent = gem.name;
    document.getElementById('obsGemText').innerHTML = gem.text || '';
  }

  // Biggest trend
  const trend = data.biggestTrend || {};
  if (trend.name) {
    document.getElementById('obsTrendName').textContent = trend.name;
    document.getElementById('obsTrendText').innerHTML = trend.text || '';
  }

  // Show insights row
  document.getElementById('obsInsights').style.display = '';
}

// ─── Init ───
document.addEventListener('DOMContentLoaded', function() {
  loadAllData();
  loadObservations();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
