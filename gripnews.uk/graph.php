<?php
/**
 * GripNews.uk — Intelligence Graph Explorer (Phase 12)
 * Visual exploration of the gaming intelligence network: games ↔ studios ↔ publishers ↔ genres.
 */
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Intelligence Graph — GripNews';
$page_desc = 'Explore the gaming intelligence network. Visual map of games, studios, publishers, and genres with live connections.';
$page_canonical = SITE_URL . '/graph';
$nav_active = 'graph';

require_once __DIR__ . '/includes/header.php';
?>

<style>
  .graph-hero { text-align: center; padding: 48px 0 24px; }
  .graph-hero h1 { font-size: 2.2em; font-weight: 800; margin-bottom: 8px; }
  .graph-hero h1 .accent { color: var(--accent); }
  .graph-hero .sub { color: var(--text-muted); max-width: 600px; margin: 0 auto; }

  .graph-stats { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; margin: 28px 0 36px; }
  .gs-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px;
    padding: 18px 24px; text-align: center; min-width: 110px; transition: border-color 0.2s; cursor: pointer; }
  .gs-card:hover { border-color: var(--accent); }
  .gs-card.active { border-color: var(--accent); background: rgba(59,130,246,0.06); }
  .gs-val { font-size: 2em; font-weight: 900; color: var(--accent); }
  .gs-lbl { font-size: 0.7em; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin-top: 4px; }

  .graph-container { max-width: 1100px; margin: 0 auto; padding: 0 16px; }

  /* Search */
  .graph-search { max-width: 500px; margin: 0 auto 32px; position: relative; }
  .graph-search input { width: 100%; padding: 12px 16px 12px 40px; border-radius: 10px; border: 1px solid var(--border);
    background: var(--bg-card); color: var(--text); font-size: 0.95em; outline: none; }
  .graph-search input:focus { border-color: var(--accent); }
  .graph-search .icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-dim); }

  /* Entity panels */
  .graph-panels { display: grid; grid-template-columns: 280px 1fr; gap: 16px; margin-bottom: 40px; min-height: 500px; }
  @media (max-width: 768px) { .graph-panels { grid-template-columns: 1fr; } }

  .graph-sidebar { background: var(--bg-card); border: 1px solid var(--border); border-radius: 14px;
    padding: 20px; max-height: 600px; overflow-y: auto; }
  .sidebar-title { font-size: 0.72em; text-transform: uppercase; letter-spacing: 1.5px; color: var(--text-muted);
    margin-bottom: 12px; font-weight: 700; }
  .sidebar-item { padding: 10px 12px; border-radius: 8px; cursor: pointer; transition: background 0.15s;
    display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px; }
  .sidebar-item:hover { background: rgba(59,130,246,0.06); }
  .sidebar-item.active { background: rgba(59,130,246,0.1); border-left: 3px solid var(--accent); }
  .si-name { font-weight: 600; font-size: 0.9em; }
  .si-count { font-size: 0.75em; color: var(--text-dim); background: rgba(255,255,255,0.05);
    padding: 2px 8px; border-radius: 10px; }

  /* Main panel */
  .graph-main { background: var(--bg-card); border: 1px solid var(--border); border-radius: 14px; padding: 24px; }
  .gm-loading { text-align: center; padding: 60px 0; color: var(--text-muted); }
  .gm-loading .pulse { display: inline-block; width: 10px; height: 10px; border-radius: 50%;
    background: var(--accent); animation: pulse 1s infinite; }
  @keyframes pulse { 0%,100% { opacity: 0.3; } 50% { opacity: 1; } }

  .entity-header { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; padding-bottom: 16px;
    border-bottom: 1px solid var(--border); }
  .entity-icon { font-size: 1.8em; }
  .entity-name { font-size: 1.6em; font-weight: 800; }
  .entity-type { font-size: 0.7em; text-transform: uppercase; letter-spacing: 1px; padding: 3px 10px;
    border-radius: 4px; font-weight: 700; margin-left: 8px; }
  .type-publisher { background: rgba(168,85,247,0.15); color: #a855f7; }
  .type-studio { background: rgba(59,130,246,0.15); color: var(--accent); }
  .type-game { background: rgba(0,230,118,0.15); color: #00e676; }
  .type-genre { background: rgba(245,158,11,0.15); color: #f59e0b; }
  .entity-desc { color: var(--text-muted); font-size: 0.9em; margin-bottom: 20px; line-height: 1.6; }

  .conn-section { margin-bottom: 24px; }
  .conn-title { font-size: 0.78em; text-transform: uppercase; letter-spacing: 1.5px; color: var(--accent);
    font-weight: 700; margin-bottom: 10px; display: flex; align-items: center; gap: 6px; }
  .conn-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 8px; }
  .conn-card { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); border-radius: 8px;
    padding: 10px 14px; cursor: pointer; transition: all 0.15s; text-decoration: none; display: block; }
  .conn-card:hover { border-color: var(--accent); background: rgba(59,130,246,0.04); }
  .cc-name { font-weight: 600; font-size: 0.88em; color: var(--text); }
  .cc-meta { font-size: 0.72em; color: var(--text-dim); margin-top: 2px; }
  .cc-momentum { font-weight: 800; color: var(--accent); font-size: 0.85em; }

  .entity-stats { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; }
  .es-item { background: rgba(255,255,255,0.03); border-radius: 8px; padding: 10px 16px; text-align: center; }
  .es-val { font-size: 1.3em; font-weight: 800; color: var(--accent); }
  .es-lbl { font-size: 0.65em; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin-top: 2px; }

  .empty-state { text-align: center; padding: 80px 20px; color: var(--text-muted); }
  .empty-state h2 { font-size: 1.2em; margin-bottom: 8px; color: var(--text); }
  .empty-state p { font-size: 0.9em; }

  .view-page-link { display: inline-block; margin-top: 12px; padding: 6px 16px; border-radius: 6px;
    background: var(--accent); color: #fff; text-decoration: none; font-size: 0.82em; font-weight: 600;
    transition: opacity 0.2s; }
  .view-page-link:hover { opacity: 0.85; }
</style>

<div class="graph-container">
  <div class="graph-hero">
    <h1>🕸️ Intelligence <span class="accent">Graph</span></h1>
    <p class="sub">Explore the gaming intelligence network — see how games, studios, publishers, and genres connect.</p>
  </div>

  <div class="graph-stats" id="graph-stats"></div>

  <div class="graph-search">
    <span class="icon">🔍</span>
    <input type="text" id="graph-input" placeholder="Search the graph — type a game, studio, publisher, or genre..." autocomplete="off">
  </div>

  <div class="graph-panels">
    <div class="graph-sidebar" id="sidebar">
      <div class="sidebar-title" id="sidebar-title">Top Publishers</div>
      <div id="sidebar-list"></div>
    </div>
    <div class="graph-main" id="main-panel">
      <div class="empty-state">
        <h2>Select an entity to explore</h2>
        <p>Click a publisher, studio, or genre from the sidebar — or search above.</p>
      </div>
    </div>
  </div>
</div>

<script>
(function() {
  var API = 'https://gripai.uk/v2';
  var statsEl = document.getElementById('graph-stats');
  var sidebar = document.getElementById('sidebar-list');
  var sidebarTitle = document.getElementById('sidebar-title');
  var mainPanel = document.getElementById('main-panel');
  var searchInput = document.getElementById('graph-input');
  var graphData = {};
  var activeView = 'publishers';

  function esc(s) { var d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; }

  // ── Load graph stats ──
  fetch(API + '/graph/stats').then(r => r.json()).then(data => {
    var g = data.graph || {};
    graphData.stats = g;
    graphData.topGenres = data.top_genres || [];
    graphData.topPublishers = data.top_publishers || [];

    var items = [
      { val: g.total_games, lbl: 'Games', view: 'games' },
      { val: g.total_studios, lbl: 'Studios', view: 'studios' },
      { val: g.total_publishers, lbl: 'Publishers', view: 'publishers' },
      { val: g.total_genres, lbl: 'Genres', view: 'genres' },
      { val: g.total_edges, lbl: 'Connections', view: null },
    ];
    statsEl.innerHTML = items.map(function(i) {
      return '<div class="gs-card' + (i.view === 'publishers' ? ' active' : '') + '"' +
        (i.view ? ' data-view="' + i.view + '" onclick="switchView(\'' + i.view + '\')"' : '') + '>' +
        '<div class="gs-val">' + (i.val || 0).toLocaleString() + '</div>' +
        '<div class="gs-lbl">' + i.lbl + '</div></div>';
    }).join('');

    // Load publishers sidebar
    renderSidebar('publishers');
  });

  // ── Sidebar rendering ──
  window.switchView = function(view) {
    activeView = view;
    document.querySelectorAll('.gs-card').forEach(c => c.classList.remove('active'));
    var el = document.querySelector('.gs-card[data-view="' + view + '"]');
    if (el) el.classList.add('active');
    renderSidebar(view);
  };

  function renderSidebar(view) {
    var html = '';
    if (view === 'publishers') {
      sidebarTitle.textContent = 'Top Publishers';
      (graphData.topPublishers || []).forEach(function(p) {
        html += '<div class="sidebar-item" onclick="loadEntity(\'publisher\',\'' + esc(p.slug) + '\')">' +
          '<span class="si-name">' + esc(p.name) + '</span>' +
          '<span class="si-count">' + p.game_count + ' games</span></div>';
      });
    } else if (view === 'genres') {
      sidebarTitle.textContent = 'Top Genres';
      (graphData.topGenres || []).forEach(function(g) {
        var dir = g.trend_direction === 'growing' ? '▲' : (g.trend_direction === 'declining' ? '▼' : '●');
        var col = g.trend_direction === 'growing' ? '#00e676' : (g.trend_direction === 'declining' ? '#f44336' : '#94a3b8');
        html += '<div class="sidebar-item" onclick="loadEntity(\'genre\',\'' + esc(g.slug) + '\')">' +
          '<span class="si-name">' + esc(g.name) + '</span>' +
          '<span class="si-count" style="color:' + col + '">' + dir + ' ' + parseFloat(g.avg_momentum).toFixed(1) + '</span></div>';
      });
    } else if (view === 'studios' || view === 'games') {
      sidebarTitle.textContent = view === 'studios' ? 'Search Studios' : 'Search Games';
      html = '<div style="color:var(--text-dim);font-size:0.85em;padding:10px;">Use the search bar above to find ' + view + '.</div>';
    }
    sidebar.innerHTML = html;
  }

  // ── Load entity detail ──
  window.loadEntity = function(type, slug) {
    mainPanel.innerHTML = '<div class="gm-loading"><span class="pulse"></span> Loading...</div>';
    document.querySelectorAll('.sidebar-item').forEach(i => i.classList.remove('active'));

    fetch(API + '/' + type + '/' + slug).then(r => r.json()).then(data => {
      if (data.error) {
        mainPanel.innerHTML = '<div class="empty-state"><h2>Not Found</h2><p>' + esc(data.error) + '</p></div>';
        return;
      }
      if (type === 'publisher') renderPublisher(data, slug);
      else if (type === 'studio') renderStudio(data, slug);
      else if (type === 'genre') renderGenre(data, slug);
      else if (type === 'game') renderGame(data, slug);
    }).catch(function() {
      mainPanel.innerHTML = '<div class="empty-state"><h2>Error loading data</h2></div>';
    });
  };

  function renderPublisher(data, slug) {
    var pub = data.publisher || {};
    var studios = data.studios || [];
    var games = data.games || [];
    var stats = data.stats || {};

    var html = '<div class="entity-header">' +
      '<span class="entity-icon">🏛️</span>' +
      '<span class="entity-name">' + esc(pub.name) + '</span>' +
      '<span class="entity-type type-publisher">Publisher</span></div>';

    if (pub.description) html += '<div class="entity-desc">' + esc(pub.description) + '</div>';

    html += '<div class="entity-stats">' +
      '<div class="es-item"><div class="es-val">' + studios.length + '</div><div class="es-lbl">Studios</div></div>' +
      '<div class="es-item"><div class="es-val">' + games.length + '</div><div class="es-lbl">Games</div></div>' +
      '<div class="es-item"><div class="es-val">' + parseFloat(stats.avg_momentum || 0).toFixed(1) + '</div><div class="es-lbl">Avg Momentum</div></div>' +
      '</div>';

    if (studios.length) {
      html += '<div class="conn-section"><div class="conn-title">🏢 Studios (' + studios.length + ')</div><div class="conn-grid">';
      studios.forEach(function(s) {
        html += '<div class="conn-card" onclick="loadEntity(\'studio\',\'' + esc(s.slug) + '\')">' +
          '<div class="cc-name">' + esc(s.name) + '</div>' +
          '<div class="cc-meta">' + (s.game_count || 0) + ' games</div></div>';
      });
      html += '</div></div>';
    }

    if (games.length) {
      html += '<div class="conn-section"><div class="conn-title">🎮 Games (' + games.length + ')</div><div class="conn-grid">';
      games.slice(0, 20).forEach(function(g) {
        var mom = parseFloat(g.momentum || g.latest_momentum || 0);
        html += '<div class="conn-card" onclick="loadEntity(\'game\',\'' + esc(g.slug) + '\')">' +
          '<div class="cc-name">' + esc(g.name) + '</div>' +
          '<div class="cc-meta">' + esc(g.genre || '') +
          (mom > 0 ? ' · <span class="cc-momentum">📈 ' + mom.toFixed(1) + '</span>' : '') + '</div></div>';
      });
      if (games.length > 20) html += '<div style="text-align:center;padding:8px;color:var(--text-dim);font-size:0.8em;">+ ' + (games.length - 20) + ' more games</div>';
      html += '</div></div>';
    }

    html += '<a href="/publisher/' + esc(slug) + '" class="view-page-link">View Full Publisher Page →</a>';
    mainPanel.innerHTML = html;
  }

  function renderStudio(data, slug) {
    var html = '<div class="entity-header">' +
      '<span class="entity-icon">🏢</span>' +
      '<span class="entity-name">' + esc(data.name) + '</span>' +
      '<span class="entity-type type-studio">Studio</span></div>';

    if (data.description) html += '<div class="entity-desc">' + esc(data.description) + '</div>';

    html += '<div class="entity-stats">' +
      '<div class="es-item"><div class="es-val">' + (data.game_count || 0) + '</div><div class="es-lbl">Games</div></div>' +
      '<div class="es-item"><div class="es-val">' + parseFloat(data.avg_momentum || 0).toFixed(1) + '</div><div class="es-lbl">Avg Momentum</div></div>' +
      '<div class="es-item"><div class="es-val">' + (data.total_issues || 0) + '</div><div class="es-lbl">Issues</div></div>' +
      '<div class="es-item"><div class="es-val">' + (data.total_signals || 0) + '</div><div class="es-lbl">Signals</div></div>' +
      '</div>';

    var games = data.games || [];
    if (games.length) {
      html += '<div class="conn-section"><div class="conn-title">🎮 Games (' + games.length + ')</div><div class="conn-grid">';
      games.forEach(function(g) {
        var mom = parseFloat(g.momentum || 0);
        var dir = g.direction === 'rising' ? '▲' : (g.direction === 'falling' ? '▼' : '●');
        var col = g.direction === 'rising' ? '#00e676' : (g.direction === 'falling' ? '#f44336' : '#94a3b8');
        html += '<div class="conn-card" onclick="loadEntity(\'game\',\'' + esc(g.slug) + '\')">' +
          '<div class="cc-name">' + esc(g.name) + '</div>' +
          '<div class="cc-meta"><span style="color:' + col + '">' + dir + '</span> ' +
          (mom > 0 ? '<span class="cc-momentum">' + mom.toFixed(1) + '</span>' : '—') + '</div></div>';
      });
      html += '</div></div>';
    }

    html += '<a href="/studio/' + esc(slug) + '" class="view-page-link">View Full Studio Page →</a>';
    mainPanel.innerHTML = html;
  }

  function renderGenre(data, slug) {
    var genre = data.genre || {};
    var games = data.games || [];
    var events = data.recent_events || [];

    var html = '<div class="entity-header">' +
      '<span class="entity-icon">🎯</span>' +
      '<span class="entity-name">' + esc(genre.name) + '</span>' +
      '<span class="entity-type type-genre">Genre</span></div>';

    if (genre.description) html += '<div class="entity-desc">' + esc(genre.description) + '</div>';

    html += '<div class="entity-stats">' +
      '<div class="es-item"><div class="es-val">' + (genre.game_count || 0) + '</div><div class="es-lbl">Games</div></div>' +
      '<div class="es-item"><div class="es-val">' + parseFloat(genre.avg_momentum || 0).toFixed(1) + '</div><div class="es-lbl">Avg Momentum</div></div>' +
      '</div>';

    if (games.length) {
      html += '<div class="conn-section"><div class="conn-title">🎮 Games (' + games.length + ')</div><div class="conn-grid">';
      games.forEach(function(g) {
        var mom = parseFloat(g.latest_momentum || g.momentum || 0);
        html += '<div class="conn-card" onclick="loadEntity(\'game\',\'' + esc(g.slug) + '\')">' +
          '<div class="cc-name">' + esc(g.name) + '</div>' +
          '<div class="cc-meta">' + (mom > 0 ? '<span class="cc-momentum">📈 ' + mom.toFixed(1) + '</span>' : '') + '</div></div>';
      });
      html += '</div></div>';
    }

    if (events.length) {
      html += '<div class="conn-section"><div class="conn-title">📡 Recent Events (' + events.length + ')</div>';
      events.slice(0, 5).forEach(function(ev) {
        html += '<div class="conn-card" style="cursor:default;">' +
          '<div class="cc-name">' + esc(ev.title || ev.event_type || 'Event') + '</div>' +
          '<div class="cc-meta">' + esc(ev.game_slug || '') + ' · ' + esc(ev.category || '') + '</div></div>';
      });
      html += '</div>';
    }

    html += '<a href="/genre/' + esc(slug) + '" class="view-page-link">View Full Genre Page →</a>';
    mainPanel.innerHTML = html;
  }

  function renderGame(data, slug) {
    var game = data.game || slug;
    var events = data.events || {};
    var issues = data.issues || {};
    var signals = data.signals || {};
    var clusters = data.clusters || [];

    var html = '<div class="entity-header">' +
      '<span class="entity-icon">🎮</span>' +
      '<span class="entity-name">' + esc(typeof game === 'string' ? game.replace(/-/g, ' ') : game) + '</span>' +
      '<span class="entity-type type-game">Game</span></div>';

    html += '<div class="entity-stats">' +
      '<div class="es-item"><div class="es-val">' + (issues.total || 0) + '</div><div class="es-lbl">Issues</div></div>' +
      '<div class="es-item"><div class="es-val">' + (issues.critical || 0) + '</div><div class="es-lbl">Critical</div></div>' +
      '<div class="es-item"><div class="es-val">' + (signals.count || 0) + '</div><div class="es-lbl">Signals</div></div>' +
      '<div class="es-item"><div class="es-val">' + (events.count || 0) + '</div><div class="es-lbl">Events</div></div>' +
      '</div>';

    if (clusters.length) {
      html += '<div class="conn-section"><div class="conn-title">🔬 Issue Clusters (' + clusters.length + ')</div>';
      clusters.forEach(function(c) {
        html += '<div class="conn-card" style="cursor:default;">' +
          '<div class="cc-name">' + esc(c.label || c.cluster_label || 'Cluster') + '</div>' +
          '<div class="cc-meta">' + (c.issue_count || c.count || 0) + ' issues</div></div>';
      });
      html += '</div>';
    }

    html += '<a href="/game/' + esc(slug) + '" class="view-page-link">View Full Game Page →</a>';
    mainPanel.innerHTML = html;
  }

  // ── Search ──
  var debounce = null;
  searchInput.addEventListener('input', function() {
    clearTimeout(debounce);
    var val = searchInput.value.trim();
    if (val.length < 2) { renderSidebar(activeView); return; }
    debounce = setTimeout(function() {
      fetch(API + '/graph/search?q=' + encodeURIComponent(val)).then(r => r.json()).then(function(data) {
        var results = data.results || {};
        var html = '';
        sidebarTitle.textContent = 'Search Results';

        (results.games || []).forEach(function(g) {
          html += '<div class="sidebar-item" onclick="loadEntity(\'game\',\'' + esc(g.slug) + '\')">' +
            '<span class="si-name">🎮 ' + esc(g.name) + '</span></div>';
        });
        (results.studios || []).forEach(function(s) {
          html += '<div class="sidebar-item" onclick="loadEntity(\'studio\',\'' + esc(s.slug) + '\')">' +
            '<span class="si-name">🏢 ' + esc(s.name) + '</span></div>';
        });
        (results.publishers || []).forEach(function(p) {
          html += '<div class="sidebar-item" onclick="loadEntity(\'publisher\',\'' + esc(p.slug) + '\')">' +
            '<span class="si-name">🏛️ ' + esc(p.name) + '</span></div>';
        });
        (results.genres || []).forEach(function(g) {
          html += '<div class="sidebar-item" onclick="loadEntity(\'genre\',\'' + esc(g.slug) + '\')">' +
            '<span class="si-name">🎯 ' + esc(g.name) + '</span></div>';
        });

        if (!html) html = '<div style="color:var(--text-dim);font-size:0.85em;padding:10px;">No results found.</div>';
        sidebar.innerHTML = html;
      });
    }, 250);
  });

})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
