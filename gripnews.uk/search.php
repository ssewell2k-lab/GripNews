<?php
/**
 * GripNews.uk — Universal Search (Phase 11)
 * Searches across the intelligence graph: games, studios, publishers, genres, and news events.
 */
require_once __DIR__ . '/includes/functions.php';

$query = trim($_GET['q'] ?? '');
$page_title = $query ? "Search: {$query} — GripNews" : "Search — GripNews";
$page_desc = "Search the GripNews intelligence graph. Find games, studios, publishers, signals, and news across the gaming ecosystem.";
$page_canonical = SITE_URL . '/search' . ($query ? '?q=' . urlencode($query) : '');
$nav_active = 'search';

require_once __DIR__ . '/includes/header.php';
?>

<section class="search-hero">
  <h1>&#128269; Search Intelligence</h1>
  <p class="search-sub">Find games, studios, publishers, signals &amp; news across the ecosystem.</p>
  <form class="search-form" action="/search" method="GET" id="search-form">
    <div class="search-input-wrap">
      <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" name="q" id="search-input" value="<?= e($query) ?>" placeholder="Search for a game, studio, publisher..." autocomplete="off" autofocus>
      <button type="submit" class="search-btn">Search</button>
    </div>
    <div class="search-suggestions" id="search-suggestions" style="display:none;"></div>
  </form>
</section>

<?php if ($query): ?>
<section class="search-results" id="search-results">
  <div class="search-loading" id="search-loader">
    <div class="loader-pulse"></div>
    <p>Searching intelligence graph&hellip;</p>
  </div>
</section>
<?php else: ?>
<section class="search-empty">
  <div class="search-quick-links">
    <h2 class="section-title">Quick Links</h2>
    <div class="quick-grid">
      <a href="/rankings" class="quick-card">
        <span class="quick-icon">&#128200;</span>
        <span class="quick-label">Top 100 Games</span>
      </a>
      <a href="/studios" class="quick-card">
        <span class="quick-icon">&#127916;</span>
        <span class="quick-label">Studios</span>
      </a>
      <a href="/trends" class="quick-card">
        <span class="quick-icon">&#128202;</span>
        <span class="quick-label">Trends</span>
      </a>
      <a href="/signals" class="quick-card">
        <span class="quick-icon">&#128225;</span>
        <span class="quick-label">Signals</span>
      </a>
      <a href="/watchlist" class="quick-card">
        <span class="quick-icon">&#128065;</span>
        <span class="quick-label">Watchlist</span>
      </a>
      <a href="/about" class="quick-card">
        <span class="quick-icon">&#128161;</span>
        <span class="quick-label">About</span>
      </a>
    </div>
  </div>

  <div class="search-graph-stats" id="graph-stats"></div>
</section>
<?php endif; ?>

<style>
/* ── Search Hero ──────────────────────────────────────────── */
.search-hero {
  max-width: 900px;
  margin: 0 auto;
  padding: 48px 20px 0;
  text-align: center;
}
.search-hero h1 {
  font-size: 1.8em;
  font-weight: 700;
  margin-bottom: 8px;
}
.search-sub {
  color: var(--text-muted);
  font-size: 0.95em;
  margin-bottom: 28px;
}

/* ── Search Form ──────────────────────────────────────────── */
.search-form {
  position: relative;
  max-width: 640px;
  margin: 0 auto;
}
.search-input-wrap {
  display: flex;
  align-items: center;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 4px;
  transition: border-color 0.2s;
}
.search-input-wrap:focus-within {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px var(--accent-dim);
}
.search-icon {
  margin: 0 12px;
  color: var(--text-dim);
  flex-shrink: 0;
}
.search-input-wrap input {
  flex: 1;
  background: none;
  border: none;
  color: var(--text);
  font-size: 1em;
  padding: 12px 8px;
  outline: none;
  font-family: var(--font);
}
.search-input-wrap input::placeholder {
  color: var(--text-dim);
}
.search-btn {
  background: var(--accent);
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 10px 20px;
  font-weight: 600;
  font-size: 0.9em;
  cursor: pointer;
  transition: background 0.2s;
  font-family: var(--font);
}
.search-btn:hover {
  background: var(--accent-hover);
}

/* ── Live Suggestions ──────────────────────────────────────── */
.search-suggestions {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 0 0 12px 12px;
  border-top: none;
  max-height: 320px;
  overflow-y: auto;
  z-index: 50;
}
.suggestion-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 16px;
  cursor: pointer;
  transition: background 0.15s;
  text-decoration: none;
  color: var(--text);
}
.suggestion-item:hover {
  background: var(--bg-card-hover);
  color: var(--text);
}
.suggestion-type {
  font-size: 0.7em;
  text-transform: uppercase;
  letter-spacing: 1px;
  padding: 2px 8px;
  border-radius: 4px;
  font-weight: 600;
  flex-shrink: 0;
}
.suggestion-type.game { background: var(--accent-dim); color: var(--accent); }
.suggestion-type.studio { background: var(--purple-dim); color: var(--purple); }
.suggestion-type.publisher { background: var(--cyan-dim); color: var(--cyan); }
.suggestion-type.genre { background: var(--green-dim); color: var(--green); }

/* ── Search Results ──────────────────────────────────────── */
.search-results {
  max-width: 900px;
  margin: 32px auto;
  padding: 0 20px;
}
.search-loading {
  text-align: center;
  padding: 60px 20px;
  color: var(--text-dim);
}
.loader-pulse {
  width: 40px;
  height: 40px;
  margin: 0 auto 16px;
  border-radius: 50%;
  background: var(--accent-dim);
  animation: pulse 1.5s ease-in-out infinite;
}
@keyframes pulse {
  0%, 100% { transform: scale(0.8); opacity: 0.4; }
  50% { transform: scale(1.2); opacity: 1; }
}
.result-section {
  margin-bottom: 32px;
}
.result-section-title {
  font-size: 0.8em;
  text-transform: uppercase;
  letter-spacing: 2px;
  color: var(--text-dim);
  margin-bottom: 12px;
  font-weight: 600;
}
.result-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 12px;
  margin-bottom: 8px;
  transition: border-color 0.2s, background 0.2s;
  text-decoration: none;
  color: var(--text);
}
.result-card:hover {
  border-color: var(--border-hover);
  background: var(--bg-card-hover);
  color: var(--text);
}
.result-badge {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.4em;
  flex-shrink: 0;
}
.result-badge.game { background: var(--accent-dim); }
.result-badge.studio { background: var(--purple-dim); }
.result-badge.publisher { background: var(--cyan-dim); }
.result-badge.genre { background: var(--green-dim); }
.result-badge.news { background: var(--amber-dim); }
.result-info {
  flex: 1;
  min-width: 0;
}
.result-name {
  font-weight: 600;
  font-size: 1em;
  margin-bottom: 2px;
}
.result-meta {
  font-size: 0.8em;
  color: var(--text-muted);
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}
.result-tag {
  font-size: 0.72em;
  padding: 2px 8px;
  border-radius: 4px;
  font-family: var(--mono);
  background: var(--bg);
  border: 1px solid var(--border);
  color: var(--text-muted);
}
.result-momentum {
  font-family: var(--mono);
  font-size: 0.85em;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 6px;
  flex-shrink: 0;
}
.result-momentum.rising { background: var(--green-dim); color: var(--green); }
.result-momentum.stable { background: var(--blue-dim); color: var(--blue); }
.result-momentum.falling { background: var(--red-dim); color: var(--red); }

/* ── No Results ──────────────────────────────────────────── */
.no-results {
  text-align: center;
  padding: 60px 20px;
  color: var(--text-dim);
}
.no-results h2 {
  font-size: 1.2em;
  margin-bottom: 8px;
  color: var(--text-muted);
}

/* ── Quick Links (empty state) ───────────────────────────── */
.search-empty {
  max-width: 900px;
  margin: 40px auto;
  padding: 0 20px;
}
.section-title {
  font-size: 0.8em;
  text-transform: uppercase;
  letter-spacing: 2px;
  color: var(--text-dim);
  margin-bottom: 16px;
  font-weight: 600;
}
.quick-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: 12px;
  margin-bottom: 40px;
}
.quick-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 20px 12px;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 12px;
  text-decoration: none;
  color: var(--text);
  transition: border-color 0.2s, background 0.2s;
}
.quick-card:hover {
  border-color: var(--border-hover);
  background: var(--bg-card-hover);
  color: var(--text);
}
.quick-icon { font-size: 1.6em; }
.quick-label { font-size: 0.85em; color: var(--text-muted); font-weight: 500; }

/* ── Graph Stats ─────────────────────────────────────────── */
.graph-stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  gap: 12px;
}
.graph-stat {
  text-align: center;
  padding: 20px 12px;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 12px;
}
.graph-stat-num {
  font-size: 1.6em;
  font-weight: 700;
  font-family: var(--mono);
  color: var(--accent);
}
.graph-stat-label {
  font-size: 0.75em;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: var(--text-dim);
  margin-top: 4px;
}

/* ── News result ─────────────────────────────────────────── */
.news-date {
  font-family: var(--mono);
  font-size: 0.75em;
  color: var(--text-dim);
}
.news-category {
  font-size: 0.72em;
  padding: 2px 8px;
  border-radius: 4px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
}
.news-category.patch { background: var(--accent-dim); color: var(--accent); }
.news-category.industry { background: var(--purple-dim); color: var(--purple); }
.news-category.esports { background: var(--cyan-dim); color: var(--cyan); }
.news-category.release { background: var(--green-dim); color: var(--green); }
.news-category.rumour { background: var(--amber-dim); color: var(--amber); }

@media (max-width: 600px) {
  .search-hero h1 { font-size: 1.3em; }
  .quick-grid { grid-template-columns: repeat(3, 1fr); }
  .graph-stats-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>

<script>
(function() {
  var API = "https://gripai.uk/v2";
  var query = <?= json_encode($query) ?>;
  var input = document.getElementById("search-input");
  var sugBox = document.getElementById("search-suggestions");
  var debounceTimer = null;

  // ── Live suggestions (typeahead) ──
  if (input) {
    input.addEventListener("input", function() {
      clearTimeout(debounceTimer);
      var val = input.value.trim();
      if (val.length < 2) { sugBox.style.display = "none"; return; }
      debounceTimer = setTimeout(function() {
        fetch(API + "/graph/search?q=" + encodeURIComponent(val))
          .then(function(r) { return r.json(); })
          .then(function(data) {
            var html = "";
            var results = data.results || {};
            var items = [];
            (results.games || []).forEach(function(g) {
              items.push({ type: "game", name: g.name, slug: g.slug, genre: g.genre, momentum: g.latest_momentum });
            });
            (results.studios || []).forEach(function(s) {
              items.push({ type: "studio", name: s.name, slug: s.slug });
            });
            (results.publishers || []).forEach(function(p) {
              items.push({ type: "publisher", name: p.name, slug: p.slug });
            });
            (results.genres || []).forEach(function(g) {
              items.push({ type: "genre", name: g.name, slug: g.slug });
            });
            if (items.length === 0) {
              sugBox.style.display = "none";
              return;
            }
            items.slice(0, 8).forEach(function(item) {
              var href = "/" + item.type + "/" + item.slug;
              var meta = item.genre ? " &middot; " + item.genre : "";
              if (item.momentum && parseFloat(item.momentum) > 0) meta += " &middot; &#128200; " + parseFloat(item.momentum).toFixed(1);
              html += '<a class="suggestion-item" href="' + href + '">' +
                '<span class="suggestion-type ' + item.type + '">' + item.type + '</span>' +
                '<span>' + item.name + '<span style="color:var(--text-dim);font-size:0.8em;">' + meta + '</span></span></a>';
            });
            sugBox.innerHTML = html;
            sugBox.style.display = "block";
          })
          .catch(function() { sugBox.style.display = "none"; });
      }, 250);
    });

    document.addEventListener("click", function(e) {
      if (!e.target.closest(".search-form")) sugBox.style.display = "none";
    });
  }

  // ── Full search results ──
  if (query) {
    Promise.all([
      fetch(API + "/graph/search?q=" + encodeURIComponent(query)).then(function(r) { return r.json(); }),
      fetch(API + "/news/search?q=" + encodeURIComponent(query)).then(function(r) { return r.json(); })
    ]).then(function(responses) {
      var graph = responses[0];
      var news = responses[1];
      var container = document.getElementById("search-results");
      var html = "";
      var totalResults = (graph.total || 0) + (news.count || 0);

      html += '<div style="margin-bottom:20px;color:var(--text-muted);font-size:0.9em;">' +
        totalResults + ' result' + (totalResults !== 1 ? 's' : '') + ' for <strong style="color:var(--text);">"' + escHtml(query) + '"</strong></div>';

      // Games
      var games = (graph.results && graph.results.games) || [];
      if (games.length > 0) {
        html += '<div class="result-section"><div class="result-section-title">&#127918; Games (' + games.length + ')</div>';
        games.forEach(function(g) {
          var mom = parseFloat(g.latest_momentum) || 0;
          var momClass = mom > 20 ? "rising" : mom > 5 ? "stable" : "falling";
          html += '<a class="result-card" href="/game/' + g.slug + '">' +
            '<div class="result-badge game">&#127918;</div>' +
            '<div class="result-info"><div class="result-name">' + escHtml(g.name) + '</div>' +
            '<div class="result-meta">' +
            (g.genre ? '<span class="result-tag">' + g.genre + '</span>' : '') +
            (g.studio ? '<span class="result-tag" style="background:rgba(168,85,247,0.12);color:#a855f7;">&#127916; ' + escHtml(g.studio) + '</span>' : '') +
            (g.issue_count ? '<span class="result-tag" style="background:rgba(239,68,68,0.12);color:#ef4444;">' + g.issue_count + ' issues</span>' : '') +
            '</div></div>' +
            (mom > 0 ? '<span class="result-momentum ' + momClass + '">&#128200; ' + mom.toFixed(1) + '</span>' : '') +
            '</a>';
        });
        html += '</div>';
      }

      // Studios
      var studios = (graph.results && graph.results.studios) || [];
      if (studios.length > 0) {
        html += '<div class="result-section"><div class="result-section-title">&#127916; Studios (' + studios.length + ')</div>';
        studios.forEach(function(s) {
          html += '<a class="result-card" href="/studio/' + s.slug + '">' +
            '<div class="result-badge studio">&#127916;</div>' +
            '<div class="result-info"><div class="result-name">' + escHtml(s.name) + '</div>' +
            '<div class="result-meta">' +
            (s.game_count ? '<span class="result-tag">' + s.game_count + ' games</span>' : '') +
            (s.description ? '<span style="color:var(--text-dim);font-size:0.8em;">' + escHtml(s.description).substring(0,60) + '</span>' : '') +
            '</div></div>' +
            (s.avg_momentum > 0 ? '<span class="result-momentum">&#128200; ' + parseFloat(s.avg_momentum).toFixed(1) + '</span>' : '') +
            '</a>';
        });
        html += '</div>';
      }

      // Publishers
      var publishers = (graph.results && graph.results.publishers) || [];
      if (publishers.length > 0) {
        html += '<div class="result-section"><div class="result-section-title">&#128218; Publishers (' + publishers.length + ')</div>';
        publishers.forEach(function(p) {
          html += '<a class="result-card" href="/publisher/' + p.slug + '">' +
            '<div class="result-badge publisher">&#128218;</div>' +
            '<div class="result-info"><div class="result-name">' + escHtml(p.name) + '</div>' +
            '<div class="result-meta">' +
            (p.game_count ? '<span class="result-tag">' + p.game_count + ' games</span>' : '') +
            (p.hq_country ? '<span style="color:var(--text-dim);font-size:0.8em;">📍 ' + escHtml(p.hq_country) + '</span>' : '') +
            '</div></div></a>';
        });
        html += '</div>';
      }

      // Genres
      var genres = (graph.results && graph.results.genres) || [];
      if (genres.length > 0) {
        html += '<div class="result-section"><div class="result-section-title">&#127991; Genres (' + genres.length + ')</div>';
        genres.forEach(function(g) {
          html += '<a class="result-card" href="/genre/' + g.slug + '">' +
            '<div class="result-badge genre">&#127991;</div>' +
            '<div class="result-info"><div class="result-name">' + escHtml(g.name) + '</div>' +
            '<div class="result-meta">' +
            (g.game_count ? '<span class="result-tag">' + g.game_count + ' games</span>' : '') +
            '</div></div></a>';
        });
        html += '</div>';
      }

      // News events
      var events = (news && news.events) || [];
      if (events.length > 0) {
        html += '<div class="result-section"><div class="result-section-title">&#128240; News (' + events.length + ')</div>';
        events.forEach(function(ev) {
          var cat = (ev.category || "").toLowerCase();
          html += '<a class="result-card" href="/story/' + (ev.event_date || "") + '/' + (ev.event_id || "") + '">' +
            '<div class="result-badge news">&#128240;</div>' +
            '<div class="result-info"><div class="result-name">' + escHtml(ev.title || "Untitled") + '</div>' +
            '<div class="result-meta">' +
            '<span class="news-date">' + (ev.event_date || "") + '</span>' +
            '<span class="news-category ' + cat + '">' + (ev.category || "") + '</span>' +
            (ev.game_slug ? '<span class="result-tag">' + ev.game_slug + '</span>' : '') +
            '</div></div></a>';
        });
        html += '</div>';
      }

      if (totalResults === 0) {
        html = '<div class="no-results"><h2>No results found</h2><p>Try a different search term — game name, studio, publisher, or genre.</p></div>';
      }

      container.innerHTML = html;
    }).catch(function(err) {
      document.getElementById("search-results").innerHTML = '<div class="no-results"><h2>Search temporarily unavailable</h2><p>Please try again in a moment.</p></div>';
    });
  }

  // ── Graph stats (empty state) ──
  var statsEl = document.getElementById("graph-stats");
  if (statsEl) {
    fetch(API + "/graph/stats")
      .then(function(r) { return r.json(); })
      .then(function(data) {
        var g = data.graph || {};
        statsEl.innerHTML =
          '<h2 class="section-title">Intelligence Graph</h2>' +
          '<div class="graph-stats-grid">' +
          stat(g.total_games, "Games") +
          stat(g.total_studios, "Studios") +
          stat(g.total_publishers, "Publishers") +
          stat(g.total_genres, "Genres") +
          stat(g.total_edges, "Connections") +
          stat(g.total_event_links, "Event Links") +
          '</div>';
      }).catch(function() {});
  }

  function stat(n, label) {
    return '<div class="graph-stat"><div class="graph-stat-num">' + (n || 0).toLocaleString() + '</div><div class="graph-stat-label">' + label + '</div></div>';
  }
  function escHtml(s) {
    var d = document.createElement("div"); d.textContent = s; return d.innerHTML;
  }
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
