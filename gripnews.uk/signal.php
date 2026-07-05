<?php
/**
 * GripNews.uk — Signal Detail Page (Phase 11)
 * Displays individual signal details with linked entities.
 */
require_once __DIR__ . '/includes/functions.php';

$signal_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$signal_id) { header('Location: /signals'); exit; }

$page_title = "Signal #{$signal_id} — GripNews";
$page_desc  = "Detailed signal intelligence — severity, confidence, affected games, and evidence.";
$nav_active = '';

require_once __DIR__ . '/includes/header.php';
?>

<style>
  .signal-hero { padding: 40px 0 24px; border-bottom: 1px solid var(--border); margin-bottom: 32px; }
  .signal-title { font-size: 1.6em; font-weight: 800; color: var(--text); letter-spacing: -0.5px; line-height: 1.3; }
  .signal-badges { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 12px; }
  .signal-badge { display: inline-block; padding: 4px 14px; border-radius: 20px; font-size: 0.75em;
    font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; }
  .badge-critical { background: rgba(239,68,68,0.15); color: #ef4444; }
  .badge-warning { background: rgba(245,158,11,0.15); color: #f59e0b; }
  .badge-alert { background: rgba(168,85,247,0.15); color: #a855f7; }
  .badge-info { background: rgba(59,130,246,0.15); color: #3b82f6; }
  .badge-active { background: rgba(34,197,94,0.15); color: #22c55e; }
  .badge-resolved { background: rgba(100,116,139,0.15); color: #94a3b8; }
  .badge-expired { background: rgba(100,116,139,0.1); color: #64748b; }
  .badge-type { background: rgba(6,182,212,0.15); color: #06b6d4; }
  .badge-trust-strong { background: rgba(34,197,94,0.15); color: #22c55e; }
  .badge-trust-developing { background: rgba(245,158,11,0.15); color: #f59e0b; }
  .badge-trust-weak { background: rgba(100,116,139,0.1); color: #64748b; }

  .signal-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 12px; margin: 24px 0 32px; }
  .sig-stat { background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px; padding: 16px; text-align: center; }
  .sig-stat-val { font-size: 1.6em; font-weight: 800; color: var(--accent); font-family: var(--mono); }
  .sig-stat-label { font-size: 0.72em; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin-top: 4px; }

  .signal-section-title { font-size: 1.05em; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px;
    color: var(--accent); margin: 32px 0 16px; padding-bottom: 8px; border-bottom: 1px solid var(--border); }
  .signal-description { font-size: 0.95em; line-height: 1.7; color: var(--text-muted); background: var(--bg-card);
    border: 1px solid var(--border); border-radius: 10px; padding: 20px; }

  .affected-games-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; }
  .affected-game { background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px;
    padding: 14px 16px; text-decoration: none; color: var(--text); transition: border-color 0.2s; display: block; }
  .affected-game:hover { border-color: var(--accent); color: var(--text); }
  .affected-game-name { font-weight: 600; font-size: 0.92em; }
  .affected-game-genre { font-size: 0.75em; color: var(--text-muted); margin-top: 2px; }

  .loader-signal { text-align: center; padding: 60px 0; color: var(--text-muted); }
  .loader-signal .pulse { display: inline-block; width: 10px; height: 10px; border-radius: 50%;
    background: var(--accent); animation: pulse 1s infinite; }
  @keyframes pulse { 0%,100% { opacity: 0.4; } 50% { opacity: 1; } }
  .back-link { color: var(--accent); text-decoration: none; font-size: 0.85em; display: inline-block; margin-bottom: 8px; }
  .back-link:hover { text-decoration: underline; }

  .timeline-row { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.04); }
  .timeline-row:last-child { border-bottom: none; }
  .timeline-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
  .timeline-dot.detected { background: var(--accent); }
  .timeline-dot.reinforced { background: var(--green); }
  .timeline-dot.expires { background: var(--amber); }
  .timeline-date { font-family: var(--mono); font-size: 0.82em; color: var(--text-muted); min-width: 140px; }
  .timeline-label { font-size: 0.88em; color: var(--text); }
</style>

<section class="hero" style="padding-bottom: 0;">
  <a href="/signals" class="back-link">← Back to Signals</a>
  <div id="signal-loading" class="loader-signal">
    <span class="pulse"></span> Loading signal #<?= $signal_id ?>…
  </div>
  <div id="signal-content" style="display:none;">
    <div class="signal-hero">
      <h1 class="signal-title" id="signal-title"></h1>
      <div class="signal-badges" id="signal-badges"></div>
    </div>
  </div>
</section>

<section class="content-wrapper" id="signal-body" style="display:none; padding-top:0;">
  <div class="signal-stats" id="signal-stats"></div>

  <div id="description-section" style="display:none;">
    <div class="signal-section-title">📋 Description</div>
    <div class="signal-description" id="signal-desc"></div>
  </div>

  <div id="affected-section" style="display:none;">
    <div class="signal-section-title">🎮 Affected Games</div>
    <div class="affected-games-grid" id="affected-games"></div>
  </div>

  <div id="timeline-section" style="display:none;">
    <div class="signal-section-title">📅 Signal Timeline</div>
    <div id="signal-timeline"></div>
  </div>
</section>

<script>
var SIGNAL_ID = <?= $signal_id ?>;
var API = "https://gripai.uk";

function esc(s) {
  var d = document.createElement("div"); d.textContent = s || ""; return d.innerHTML;
}

// Fetch signal from direct DB-backed API or via /api/signals
fetch(API + "/v2/signal/" + SIGNAL_ID)
  .then(function(r) { return r.ok ? r.json() : Promise.reject("not found"); })
  .then(renderSignal)
  .catch(function() {
    // Fallback: try alternate endpoint
    fetch(API + "/api/intelligence/signals/" + SIGNAL_ID)
      .then(function(r) { return r.ok ? r.json() : Promise.reject("not found"); })
      .then(renderSignal)
      .catch(function() {
        document.getElementById("signal-loading").innerHTML =
          '<p style="color:var(--amber);">Signal #' + SIGNAL_ID + ' not found. <a href="/signals" style="color:var(--accent);">View all signals →</a></p>';
      });
  });

function renderSignal(data) {
  var sig = data.signal || data;
  document.getElementById("signal-loading").style.display = "none";
  document.getElementById("signal-content").style.display = "block";
  document.getElementById("signal-body").style.display = "block";

  // Title
  document.getElementById("signal-title").textContent = sig.title || "Signal #" + SIGNAL_ID;

  // Badges
  var badges = "";
  var sev = (sig.severity || "info").toLowerCase();
  badges += '<span class="signal-badge badge-' + sev + '">' + sev.toUpperCase() + '</span>';
  var status = (sig.status || "active").toLowerCase();
  badges += '<span class="signal-badge badge-' + status + '">' + status.toUpperCase() + '</span>';
  var type = (sig.signal_type || "").replace(/_/g, " ");
  if (type) badges += '<span class="signal-badge badge-type">' + type + '</span>';
  var trust = (sig.trust_tier || "developing").toLowerCase();
  badges += '<span class="signal-badge badge-trust-' + trust + '">Trust: ' + trust + '</span>';
  document.getElementById("signal-badges").innerHTML = badges;

  // Stats
  var conf = sig.confidence ? (parseFloat(sig.confidence) * 100).toFixed(0) + "%" : "—";
  var evCount = sig.evidence_count || 0;
  var gamesAff = sig.games_affected || 0;
  document.getElementById("signal-stats").innerHTML =
    '<div class="sig-stat"><div class="sig-stat-val">' + conf + '</div><div class="sig-stat-label">Confidence</div></div>' +
    '<div class="sig-stat"><div class="sig-stat-val">' + evCount + '</div><div class="sig-stat-label">Evidence</div></div>' +
    '<div class="sig-stat"><div class="sig-stat-val">' + gamesAff + '</div><div class="sig-stat-label">Games Affected</div></div>' +
    '<div class="sig-stat"><div class="sig-stat-val">' + esc(sig.category || "—") + '</div><div class="sig-stat-label">Category</div></div>';

  // Description
  if (sig.description) {
    document.getElementById("description-section").style.display = "block";
    document.getElementById("signal-desc").textContent = sig.description;
  }

  // Affected Games
  var games = sig.affected_games || [];
  if (typeof games === "string") { try { games = JSON.parse(games); } catch(e) { games = []; } }
  if (sig.game_name) {
    var slug = (sig.game_name || "").toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/^-|-$/g, "");
    games.unshift({ name: sig.game_name, slug: slug });
  }
  if (games.length) {
    document.getElementById("affected-section").style.display = "block";
    var gHtml = "";
    games.forEach(function(g) {
      var name = g.name || g;
      var slug = g.slug || (typeof g === "string" ? g.toLowerCase().replace(/[^a-z0-9]+/g, "-") : "");
      gHtml += '<a class="affected-game" href="/game/' + slug + '">' +
        '<div class="affected-game-name">' + esc(name) + '</div></a>';
    });
    document.getElementById("affected-games").innerHTML = gHtml;
  }

  // Timeline
  var hasTimeline = sig.first_detected || sig.last_reinforced || sig.expires_at;
  if (hasTimeline) {
    document.getElementById("timeline-section").style.display = "block";
    var tlHtml = "";
    if (sig.first_detected) {
      tlHtml += '<div class="timeline-row"><div class="timeline-dot detected"></div>' +
        '<div class="timeline-date">' + esc(sig.first_detected.split("T")[0]) + '</div>' +
        '<div class="timeline-label">First detected</div></div>';
    }
    if (sig.last_reinforced) {
      tlHtml += '<div class="timeline-row"><div class="timeline-dot reinforced"></div>' +
        '<div class="timeline-date">' + esc(sig.last_reinforced.split("T")[0]) + '</div>' +
        '<div class="timeline-label">Last reinforced</div></div>';
    }
    if (sig.expires_at) {
      tlHtml += '<div class="timeline-row"><div class="timeline-dot expires"></div>' +
        '<div class="timeline-date">' + esc(sig.expires_at.split("T")[0]) + '</div>' +
        '<div class="timeline-label">Expires</div></div>';
    }
    document.getElementById("signal-timeline").innerHTML = tlHtml;
  }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
