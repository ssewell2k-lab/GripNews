<?php
/**
 * GripNews.uk — Homepage (Phase 6: Live Mode)
 * Fetches signals in real-time from the v2 API.
 * Falls back to static data if API is unreachable.
 *
 * Fixed: Added server-side fallback rendering so content is always visible.
 */
require_once __DIR__ . '/includes/functions.php';

$page_title = "GripNews — Gaming Intelligence Feed";
$page_desc  = "Live gaming intelligence signals. Not just news — real-time insights, trends, and pattern detection powered by GripAI.";
$nav_active = 'home';

// ── Load fallback data from latest available data file ────────
$fallback_dates = get_available_dates(1);
$fallback_date = $fallback_dates[0] ?? '';
$fallback_signals = $fallback_date ? load_signals($fallback_date) : [];
$has_fallback = !empty($fallback_signals);

require_once __DIR__ . '/includes/header.php';
?>

  <section class="hero">
    <div class="hero-date" id="hero-date"><?= strtoupper(date('l, j F Y')) ?></div>
    <h1>Gaming Intelligence <span class="count">Feed</span></h1>
    <p class="hero-sub">What's actually happening in gaming right now. Ranked by impact.</p>
    <div style="margin-top:12px;">
      <span class="live-badge" id="live-badge">
        <span class="live-dot"></span> <span id="live-status">Connecting to signal feed…</span>
      </span>
    </div>
  </section>

  <!-- Live signals — populated by JS from /v2/news/top10 -->
  <section class="signals" id="signals-live">
    <div class="signal-loading" id="signal-loader">
      <div class="loader-pulse"></div>
      <p>Loading live signals…</p>
    </div>
  </section>

  <!-- Server-side fallback — shown if API doesn't respond within 5s -->
  <?php if ($has_fallback): ?>
  <section class="signals" id="signals-fallback" style="display:none;">
    <?php
      $rank = 1;
      foreach (array_slice($fallback_signals, 0, 10) as $s) {
        echo render_signal_card($s, $rank, $fallback_date);
        $rank++;
      }
    ?>
  </section>
  <?php if (count($fallback_signals) > 10): ?>
  <section class="signals-more" id="signals-fallback-more" style="display:none;">
    <div class="more-divider">
      <span class="more-label">+ <?= count($fallback_signals) - 10 ?> more signals</span>
    </div>
    <?php
      foreach (array_slice($fallback_signals, 10) as $s) {
        echo render_signal_card($s, $rank, $fallback_date);
        $rank++;
      }
    ?>
  </section>
  <?php endif; ?>
  <?php endif; ?>

  <!-- Overflow signals (for live API data) -->
  <section class="signals-more" id="signals-more" style="display:none;">
    <div class="more-divider">
      <span class="more-label" id="more-label"></span>
    </div>
    <div id="signals-more-list"></div>
  </section>

  <!-- Daily Intelligence Report Widget -->
  <section class="v2-report-widget" id="daily-report" style="max-width:900px;margin:40px auto;padding:0 20px;">
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:28px 24px;position:relative;overflow:hidden;">
      <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--accent),var(--cyan),var(--purple));"></div>
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:8px;">
        <div>
          <div style="font-size:0.7em;text-transform:uppercase;letter-spacing:2px;color:var(--accent);font-weight:600;margin-bottom:4px;">&#128202; Daily Intelligence Report</div>
          <h2 style="font-size:1.3em;font-weight:700;" id="report-title">Loading…</h2>
        </div>
        <a href="https://gripai.uk/news" target="_blank" style="font-size:0.8em;color:var(--accent);border:1px solid var(--border);padding:6px 14px;border-radius:8px;text-decoration:none;white-space:nowrap;">View Full Dashboard &#8594;</a>
      </div>
      <div id="report-content" style="color:var(--text-muted);font-size:0.92em;line-height:1.7;">
        <div style="text-align:center;padding:20px;color:var(--text-dim);">Fetching latest intelligence…</div>
      </div>
    </div>
  </section>

  <section class="home-categories">
    <h2 class="section-title">Browse by Category</h2>
    <div class="category-grid">
      <?php foreach (get_categories() as $slug => $cat): ?>
        <a href="/<?= $slug ?>" class="category-card">
          <span class="cat-icon"><?= $cat['icon'] ?></span>
          <span class="cat-name"><?= $cat['label'] ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </section>

<style>
/* ── Live Mode Additions ─────────────────────────────────── */
.signal-loading {
  text-align: center; padding: 60px 20px;
  color: var(--text-dim); font-size: 0.95em;
}
.loader-pulse {
  width: 40px; height: 40px; margin: 0 auto 16px;
  border-radius: 50%;
  background: var(--accent-dim);
  animation: pulse 1.5s ease-in-out infinite;
}
@keyframes pulse {
  0%, 100% { transform: scale(0.8); opacity: 0.4; }
  50% { transform: scale(1.2); opacity: 1; }
}
.signal-card { cursor: pointer; }

/* Impact breakdown bar */
.impact-breakdown {
  display: flex; gap: 6px; margin-top: 8px; flex-wrap: wrap;
}
.impact-chip {
  font-size: 0.7em; padding: 2px 8px; border-radius: 6px;
  font-family: var(--mono); letter-spacing: 0.3px;
  background: var(--bg); border: 1px solid var(--border);
  color: var(--text-muted);
}
.impact-chip.high { border-color: var(--red); color: var(--red); }
.impact-chip.mid  { border-color: var(--amber); color: var(--amber); }

/* Ask GripAI button (wired in Step 4) */
.ask-gripai-btn {
  display: inline-flex; align-items: center; gap: 6px;
  margin-top: 10px; padding: 6px 14px;
  font-size: 0.78em; font-weight: 600;
  color: var(--cyan); background: var(--cyan-dim);
  border: 1px solid rgba(6,182,212,0.25);
  border-radius: 8px; cursor: pointer;
  transition: all 0.2s;
  text-decoration: none;
}
.ask-gripai-btn:hover {
  background: rgba(6,182,212,0.2);
  border-color: var(--cyan);
  color: #fff;
}

/* Depth panel (expanded reasoning) */
.depth-panel {
  margin-top: 12px; padding: 16px;
  background: rgba(6,182,212,0.06);
  border: 1px solid rgba(6,182,212,0.15);
  border-radius: 10px;
  font-size: 0.88em; line-height: 1.7;
  color: var(--text-muted);
  display: none;
}
.depth-panel.open { display: block; }
.depth-panel h4 {
  font-size: 0.75em; text-transform: uppercase;
  letter-spacing: 1.5px; color: var(--cyan);
  margin-bottom: 8px;
}
.depth-detail p { margin-bottom: 8px; }
.depth-prediction {
  margin-top: 12px; padding: 10px 14px;
  background: var(--purple-dim);
  border-left: 3px solid var(--purple);
  border-radius: 0 8px 8px 0;
  font-size: 0.9em; color: var(--text);
}

/* Live badge pulse */
.live-badge { animation: none; }
.live-badge.connected .live-dot {
  background: var(--green);
  box-shadow: 0 0 6px var(--green);
}

/* Error state */
.signal-error {
  text-align: center; padding: 40px 20px;
  color: var(--amber);
}
.signal-error a { color: var(--accent); }

/* Game entity link */
.entity-link {
  display: inline-block; margin: 2px 4px 2px 0;
  padding: 2px 10px; border-radius: 6px;
  font-size: 0.72em; font-weight: 600;
  background: var(--accent-dim);
  color: var(--accent); text-decoration: none;
  border: 1px solid rgba(59,130,246,0.2);
  transition: all 0.2s;
}
.entity-link:hover {
  background: rgba(59,130,246,0.2);
  border-color: var(--accent);
}

/* Signal Strength Badge (Day 6) */
.signal-strength-badge {
  display: inline-block; padding: 2px 10px;
  border-radius: 12px; font-size: 0.62em;
  font-weight: 700; letter-spacing: 0.8px;
  text-transform: uppercase; margin-left: 8px;
  vertical-align: middle;
}
.strength-strong {
  background: rgba(0,230,118,0.12); color: #00e676;
  border: 1px solid rgba(0,230,118,0.3);
}
.strength-developing {
  background: rgba(255,193,7,0.12); color: #ffc107;
  border: 1px solid rgba(255,193,7,0.3);
}
.strength-weak {
  background: rgba(255,255,255,0.06); color: var(--text-dim);
  border: 1px solid rgba(255,255,255,0.1);
}

/* Fallback notice banner */
.fallback-notice {
  text-align: center; padding: 10px 20px; margin-bottom: 16px;
  background: rgba(255,193,7,0.08); border: 1px solid rgba(255,193,7,0.2);
  border-radius: 10px; font-size: 0.82em; color: var(--amber);
}
</style>

<script>
(function() {
  var API = "https://gripai.uk/v2";
  var _signalsLoaded = false;
  var _hasFallback = <?= $has_fallback ? 'true' : 'false' ?>;
  var _fallbackDate = "<?= e($fallback_date) ?>";
  var _todayStr = "<?= date('Y-m-d') ?>";

  // ── Helpers ──────────────────────────────────────────
  function esc(s) {
    var d = document.createElement("div");
    d.textContent = s || "";
    return d.innerHTML;
  }

  function scoreClass(sc) {
    if (sc >= 7) return "high";
    if (sc >= 4) return "mid";
    return "low";
  }

  function catClass(cat) {
    var map = {
      industry: "cat-industry", patch: "cat-patch", rumor: "cat-rumour",
      rumour: "cat-rumour", esports: "cat-esports", indie: "cat-indie",
      release: "cat-release", meta_shift: "cat-patch"
    };
    return map[(cat || "").toLowerCase()] || "cat-industry";
  }

  function confClass(c) {
    return "conf-" + (c || "confirmed");
  }

  function maxImpact(imp) {
    if (!imp) return 0;
    return Math.max(imp.player || 0, imp.dev || 0, imp.esports || 0, imp.industry || 0);
  }

  function impactChips(imp) {
    if (!imp) return "";
    var parts = [];
    var labels = { player: "Player", dev: "Dev", esports: "Esports", industry: "Industry" };
    for (var k in labels) {
      var v = imp[k] || 0;
      if (v > 0) {
        var cls = v >= 7 ? "high" : (v >= 4 ? "mid" : "");
        parts.push('<span class="impact-chip ' + cls + '">' + labels[k] + ' ' + v + '</span>');
      }
    }
    return '<div class="impact-breakdown">' + parts.join("") + '</div>';
  }

  function tagsHtml(tags) {
    if (!tags || !tags.length) return "";
    var html = '<div class="signal-tags">';
    tags.slice(0, 4).forEach(function(t) {
      html += '<span class="signal-tag">' + esc(t) + '</span>';
    });
    html += '</div>';
    return html;
  }

  function entitiesHtml(entities, gameSlug) {
    if (!entities || !entities.length) return "";
    var html = '<div style="margin-top:6px;">';
    entities.slice(0, 3).forEach(function(e) {
      var slug = e.toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/^-|-$/g, "");
      html += '<a href="/game/' + slug + '" class="entity-link" onclick="event.stopPropagation();">' + esc(e) + '</a>';
    });
    html += '</div>';
    return html;
  }

  // ── Render a signal card ────────────────────────────
  function renderCard(s, rank) {
    var imp = s.impact || {};
    var sc = maxImpact(imp);
    var scCls = scoreClass(sc);
    var fillH = Math.min((sc / 10) * 100, 100);
    var cat = s.category || s.type || "Update";
    var slug = (s.title || "").toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/^-|-$/g, "");
    var date = (s.date || "").split("T")[0];

    return '<div class="signal-card" data-event-id="' + esc(s.event_id) + '">' +
      '<div class="signal-rank">' + rank + '</div>' +
      '<div class="signal-body">' +
        '<div class="signal-meta">' +
          '<span class="signal-category ' + catClass(cat) + '">' + esc(cat) + '</span>' +
          '<span class="signal-confidence ' + confClass(s.confidence) + '">&bull; ' + esc(s.confidence || "confirmed") + '</span>' +
          (s.signal_strength ? '<span class="signal-strength-badge strength-' + s.signal_strength + '">' + s.signal_strength + '</span>' : '') +
        '</div>' +
        '<div class="signal-title">' + esc(s.title) + '</div>' +
        '<div class="signal-summary">' + esc(s.summary) + '</div>' +
        (s.why_it_matters ? '<div class="signal-why">&rarr; ' + esc(s.why_it_matters) + '</div>' : '') +
        impactChips(imp) +
        entitiesHtml(s.entities, s.game_slug) +
        '<button class="ask-gripai-btn" onclick="event.stopPropagation();askGripAI(\'' + esc(s.event_id) + '\', this);" title="Get deeper analysis from GripAI">' +
          '🧠 Ask GripAI' +
        '</button>' +
        '<div class="depth-panel" id="depth-' + esc(s.event_id) + '"></div>' +
      '</div>' +
      '<div class="signal-score">' +
        '<div class="score-value score-' + scCls + '">' + sc + '</div>' +
        '<div class="score-label">impact</div>' +
        '<div class="score-bar"><div class="score-fill score-fill-' + scCls + '" style="height:' + fillH + '%"></div></div>' +
      '</div>' +
    '</div>';
  }

  // ── Show fallback content ───────────────────────────
  function showFallback() {
    if (_signalsLoaded || !_hasFallback) return;
    var fb = document.getElementById("signals-fallback");
    var fbMore = document.getElementById("signals-fallback-more");
    var live = document.getElementById("signals-live");
    if (fb) {
      live.style.display = "none";
      fb.style.display = "block";
      // Add notice banner before fallback
      var notice = document.createElement("div");
      notice.className = "fallback-notice";
      notice.innerHTML = "📡 Live feed is reconnecting. Showing recent signals from " + _fallbackDate + ".";
      fb.insertBefore(notice, fb.firstChild);
    }
    if (fbMore) fbMore.style.display = "block";
    // Update status badge
    var status = document.getElementById("live-status");
    status.textContent = "Cached · signals from " + _fallbackDate;
  }

  // ── Ask GripAI (depth expansion) ────────────────────
  window.askGripAI = function(eventId, btn) {
    var panel = document.getElementById("depth-" + eventId);
    if (!panel) return;

    // Toggle if already loaded
    if (panel.dataset.loaded === "1") {
      panel.classList.toggle("open");
      return;
    }

    btn.textContent = "⏳ Analysing…";
    btn.disabled = true;

    fetch(API + "/news/event/" + eventId)
      .then(function(r) { return r.json(); })
      .then(function(data) {
        var ev = data.event || {};
        var detail = ev.detail || [];
        var html = '<h4>🔍 Deep Analysis</h4>';

        if (detail.length) {
          html += '<div class="depth-detail">';
          detail.forEach(function(d) { html += '<p>' + esc(d) + '</p>'; });
          html += '</div>';
        }

        if (ev.gripai_insight) {
          html += '<div class="depth-prediction"><strong>🧠 GripAI Insight:</strong> ' + esc(ev.gripai_insight) + '</div>';
        }

        // Show impact breakdown
        var imp = ev.impact || {};
        html += '<div style="margin-top:12px;">';
        html += '<h4>📊 Impact Breakdown</h4>';
        html += '<div class="impact-breakdown">';
        ["player","dev","esports","industry"].forEach(function(k) {
          var v = imp[k] || 0;
          var cls = v >= 7 ? "high" : (v >= 4 ? "mid" : "");
          html += '<span class="impact-chip ' + cls + '">' + k.charAt(0).toUpperCase() + k.slice(1) + ': ' + v + '/10</span>';
        });
        html += '</div></div>';

        // Sources — only show real URLs (skip example.com placeholders)
        if (ev.sources && ev.sources.length) {
          var realSources = ev.sources.filter(function(src) {
            return src.url && src.url.indexOf("example.com") === -1;
          });
          if (realSources.length) {
            html += '<div style="margin-top:12px;"><h4>📎 Sources</h4>';
            realSources.forEach(function(src) {
              html += '<a href="' + esc(src.url) + '" target="_blank" style="color:var(--accent);font-size:0.85em;display:block;margin-top:4px;">' + esc(src.name || src.url) + ' →</a>';
            });
            html += '</div>';
          }
        }

        panel.innerHTML = html;
        panel.dataset.loaded = "1";
        panel.classList.add("open");
        btn.textContent = "🧠 GripAI Analysis ▾";
        btn.disabled = false;
      })
      .catch(function() {
        panel.innerHTML = '<p style="color:var(--amber);">Unable to load analysis right now.</p>';
        panel.classList.add("open");
        btn.textContent = "🧠 Ask GripAI";
        btn.disabled = false;
      });
  };

  // ── Load signals from API ────────────────────────────
  function loadSignals() {
    fetch(API + "/news/top10")
      .then(function(r) {
        if (!r.ok) throw new Error("API " + r.status);
        return r.json();
      })
      .then(function(data) {
        var signals = data.signals || [];
        var total = data.total_events || signals.length;

        _signalsLoaded = true;

        // Hide fallback if it was shown
        var fb = document.getElementById("signals-fallback");
        if (fb) fb.style.display = "none";
        var fbMore = document.getElementById("signals-fallback-more");
        if (fbMore) fbMore.style.display = "none";
        document.getElementById("signals-live").style.display = "block";

        if (!signals.length) {
          document.getElementById("signals-live").innerHTML =
            '<div class="signal-error">' +
              '<div style="font-size:2em;margin-bottom:12px;">📡</div>' +
              '<h2>No signals detected today</h2>' +
              '<p>The intelligence feed is processing. Check back soon.</p>' +
            '</div>';
          return;
        }

        // Render top 10
        var top10 = signals.slice(0, 10);
        var rest = signals.slice(10);
        var html = "";
        top10.forEach(function(s, i) { html += renderCard(s, i + 1); });
        document.getElementById("signals-live").innerHTML = html;

        // Render overflow
        if (rest.length) {
          var moreHtml = "";
          rest.forEach(function(s, i) { moreHtml += renderCard(s, i + 11); });
          document.getElementById("more-label").textContent = "+ " + rest.length + " more signals today";
          document.getElementById("signals-more-list").innerHTML = moreHtml;
          document.getElementById("signals-more").style.display = "block";
        }

        // Update live badge
        var badge = document.getElementById("live-badge");
        badge.classList.add("connected");
        var status = document.getElementById("live-status");
        var now = new Date();
        status.textContent = "Live · " + total + " signals · Updated " +
          now.getUTCHours().toString().padStart(2, "0") + ":" +
          now.getUTCMinutes().toString().padStart(2, "0") + " UTC";

        // Only update date from API if it's today (prevents stale date override)
        if (data.date && data.date === _todayStr) {
          var d = new Date(data.date + "T12:00:00Z");
          var days = ["SUNDAY","MONDAY","TUESDAY","WEDNESDAY","THURSDAY","FRIDAY","SATURDAY"];
          var months = ["JANUARY","FEBRUARY","MARCH","APRIL","MAY","JUNE","JULY","AUGUST","SEPTEMBER","OCTOBER","NOVEMBER","DECEMBER"];
          document.getElementById("hero-date").textContent =
            days[d.getUTCDay()] + ", " + d.getUTCDate() + " " + months[d.getUTCMonth()] + " " + d.getUTCFullYear();
        }
      })
      .catch(function(err) {
        console.error("Signal feed error:", err);
        // Show fallback content instead of error
        showFallback();
        // If no fallback either, show error
        if (!_hasFallback) {
          document.getElementById("signals-live").innerHTML =
            '<div class="signal-error">' +
              '<div style="font-size:2em;margin-bottom:12px;">⚡</div>' +
              '<h2>Signal feed temporarily unavailable</h2>' +
              '<p>The intelligence system is processing. <a href="https://gripai.uk/news" target="_blank">View Signals Dashboard →</a></p>' +
            '</div>';
        }
        document.getElementById("live-status").textContent = "Reconnecting…";
        // Retry in 30s
        setTimeout(loadSignals, 30000);
      });
  }

  // ── Load daily report ────────────────────────────────
  function loadReport() {
    fetch(API + "/reports/daily/latest")
      .then(function(r) { return r.json(); })
      .then(function(data) {
        var reports = data.reports || [];
        if (!reports.length) throw new Error("No reports");
        var rpt = reports[0];
        document.getElementById("report-title").textContent = rpt.title || "Daily Report";
        var html = "";
        if (rpt.summary) {
          html += '<p style="margin-bottom:16px;">' + rpt.summary + '</p>';
        }
        if (rpt.generated_at) {
          html += '<div style="margin-top:16px;font-size:0.75em;color:var(--text-dim);text-align:right;">Generated ' + new Date(rpt.generated_at).toLocaleString() + '</div>';
        }
        document.getElementById("report-content").innerHTML = html || "<p>Report available.</p>";
      })
      .catch(function() {
        document.getElementById("report-content").innerHTML =
          '<div style="text-align:center;padding:12px;"><a href="https://gripai.uk/news" target="_blank" style="color:var(--accent);">View latest intelligence on the Signals Dashboard →</a></div>';
        document.getElementById("report-title").textContent = "Intelligence Dashboard";
      });
  }

  // ── Init ────────────────────────────────────────────
  loadSignals();
  loadReport();

  // Fallback timer: if signals haven't loaded in 5 seconds, show cached data
  setTimeout(function() {
    if (!_signalsLoaded) showFallback();
  }, 5000);

  // Auto-refresh every 5 minutes
  setInterval(loadSignals, 300000);
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>