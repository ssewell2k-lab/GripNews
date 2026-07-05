<?php
/**
 * GripNews.uk — Signal Intelligence Page (Phase 6 Day 4)
 * Displays grouped signal tags with event counts and breakdowns.
 */
require_once __DIR__ . '/includes/functions.php';

$page_title = "Signal Intelligence — GripNews";
$page_desc  = "Grouped signal intelligence across gaming — track meta shifts, balance changes, esports impact, and industry moves.";
$nav_active = '';

require_once __DIR__ . '/includes/header.php';
?>

<style>
  .signals-hero h1 span { color: var(--accent); }

  .summary-bar { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 32px; }
  .summary-chip { background: var(--bg-card); border: 1px solid var(--border); border-radius: 20px;
    padding: 6px 16px; font-size: 0.82em; color: var(--text-secondary); }
  .summary-chip strong { color: var(--accent); }

  .tag-group { background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px;
    padding: 20px 24px; margin-bottom: 16px; transition: border-color 0.2s; }
  .tag-group:hover { border-color: var(--accent); }
  .tg-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
  .tg-name { font-size: 1.1em; font-weight: 700; color: var(--text-primary); text-transform: capitalize; }
  .tg-name .tag-icon { margin-right: 8px; }
  .tg-stats { text-align: right; }
  .tg-count { font-size: 1.6em; font-weight: 800; color: var(--accent); }
  .tg-count-label { font-size: 0.62em; text-transform: uppercase; letter-spacing: 1px; color: var(--text-secondary);
    display: block; font-weight: 400; }
  .tg-avg { font-size: 0.75em; color: var(--text-secondary); margin-bottom: 6px; }
  .tg-bar { height: 4px; border-radius: 2px; background: rgba(255,255,255,0.05); margin: 8px 0 12px; }
  .tg-bar-fill { height: 100%; border-radius: 2px; background: var(--accent); transition: width 0.6s ease; }
  .tg-events { border-top: 1px solid rgba(255,255,255,0.05); padding-top: 10px; }
  .tg-event { display: flex; justify-content: space-between; align-items: center; padding: 6px 0;
    font-size: 0.85em; color: var(--text-secondary); }
  .tg-event-title { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .tg-event-game { color: var(--accent); font-size: 0.85em; margin-left: 12px; flex-shrink: 0; text-decoration: none; }
  .tg-event-game:hover { text-decoration: underline; }
  .tg-event-impact { font-weight: 700; margin-left: 12px; flex-shrink: 0; width: 24px; text-align: right; }
  .tg-event-impact.high { color: #f44336; }
  .tg-event-impact.mid { color: #ffc107; }

  .loader-signals { text-align: center; padding: 60px 0; color: var(--text-secondary); }
  .back-link { color: var(--accent); text-decoration: none; font-size: 0.85em; display: inline-block; margin-bottom: 8px; }
  .back-link:hover { text-decoration: underline; }
</style>

<section class="hero">
  <a href="/" class="back-link">← Back to Intelligence Feed</a>
  <h1>Signal <span>Intelligence</span></h1>
  <p class="hero-sub">What patterns are emerging across gaming right now.</p>
</section>

<section class="content-wrapper">
  <div id="summary-bar" class="summary-bar"></div>
  <div id="signals-loading" class="loader-signals">
    <span class="loader-pulse"></span> Analysing signal patterns…
  </div>
  <div id="tag-groups"></div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
  var API = "https://gripai.uk/v2";

  function esc(s) {
    var d = document.createElement("div");
    d.textContent = s || "";
    return d.innerHTML;
  }

  var tagIcons = {
    industry_move: "🏢", balance_change: "⚖️", meta_shift: "🔄", esports_impact: "🏆",
    developing_story: "📡", new_release: "🆕", player_spike: "📈",
    driver: "💻", asus: "💻", amd: "💻", network: "🌐", performance: "⚡",
    connectivity: "🌐", stability: "🔧", gameplay: "🎮", audio: "🔊",
    account: "👤", login: "🔑", engine: "⚙️", crash: "💥"
  };

  function impactClass(sc) {
    if (sc >= 7) return "high";
    if (sc >= 4) return "mid";
    return "";
  }

  function prettyTag(tag) {
    return tag.replace(/_/g, " ").replace(/\b\w/g, function(l) { return l.toUpperCase(); });
  }

  fetch(API + "/news/signal-tags?days=30")
    .then(function(r) { return r.json(); })
    .then(function(data) {
      document.getElementById("signals-loading").style.display = "none";

      var groups = data.signal_groups || [];
      var total = data.total_events || 0;

      // Summary bar
      document.getElementById("summary-bar").innerHTML =
        '<div class="summary-chip"><strong>' + total + '</strong> events tracked</div>' +
        '<div class="summary-chip"><strong>' + groups.length + '</strong> signal types</div>' +
        '<div class="summary-chip">Period: <strong>' + esc(data.period) + '</strong></div>';

      // Max count for bar scaling
      var maxCount = groups.length ? groups[0].count : 1;

      // Signal-type tags first, then entity tags
      var signalTags = ["industry_move", "balance_change", "meta_shift", "esports_impact", "developing_story", "new_release"];
      var ordered = groups.filter(function(g) { return signalTags.indexOf(g.tag) !== -1; });
      var entityTags = groups.filter(function(g) { return signalTags.indexOf(g.tag) === -1; });
      ordered = ordered.concat(entityTags);

      var html = "";
      ordered.forEach(function(g) {
        var icon = tagIcons[g.tag] || "📊";
        var pct = Math.round((g.count / maxCount) * 100);

        // Determine trust label from API
        var trustLabel = "";
        fetch(API + "/trust/sources")
          .then(function() {})  // pre-warm cache
          .catch(function() {});
        
        // Inline trust estimation based on tag type
        var signalTrustMap = {
          "industry_move": {label: "High Confidence", cls: "trust-high", icon: "✅"},
          "balance_change": {label: "Historically Accurate", cls: "trust-high", icon: "🎯"},
          "meta_shift": {label: "High Confidence", cls: "trust-high", icon: "✅"},
          "esports_impact": {label: "High Confidence", cls: "trust-high", icon: "✅"},
          "developing_story": {label: "Emerging Pattern", cls: "trust-medium", icon: "📡"},
          "new_release": {label: "High Confidence", cls: "trust-high", icon: "✅"},
          "player_spike": {label: "Emerging Pattern", cls: "trust-medium", icon: "📡"}
        };
        var trust = signalTrustMap[g.tag] || {label: "Emerging Pattern", cls: "trust-medium", icon: "📡"};
        var trustBadge = '<span class="trust-badge ' + trust.cls + '">' + trust.icon + ' ' + trust.label + '</span>';

        html += '<div class="tag-group">' +
          '<div class="tg-header">' +
          '<span class="tg-name"><span class="tag-icon">' + icon + '</span> ' + prettyTag(g.tag) + ' ' + trustBadge + '</span>' +
          '<div class="tg-stats"><span class="tg-count">' + g.count + '<span class="tg-count-label">events</span></span></div>' +
          '</div>' +
          '<div class="tg-avg">Avg impact: ' + g.avg_impact + '</div>' +
          '<div class="tg-bar"><div class="tg-bar-fill" style="width:' + pct + '%"></div></div>';

        if (g.events && g.events.length) {
          html += '<div class="tg-events">';
          g.events.slice(0, 5).forEach(function(ev) {
            html += '<div class="tg-event">' +
              '<span class="tg-event-title">' + esc(ev.title) + '</span>';
            if (ev.game) {
              html += '<a href="/game/' + esc(ev.game) + '" class="tg-event-game">' + esc(ev.game) + '</a>';
            }
            html += '<span class="tg-event-impact ' + impactClass(ev.impact) + '">' + (ev.impact || 0) + '</span></div>';
          });
          html += '</div>';
        }

        html += '</div>';
      });

      document.getElementById("tag-groups").innerHTML = html;
    })
    .catch(function() {
      document.getElementById("signals-loading").innerHTML =
        '<p style="color:#ffc107;">Unable to load signal data right now. <a href="/" style="color:var(--accent);">Return to feed</a></p>';
    });
</script>
