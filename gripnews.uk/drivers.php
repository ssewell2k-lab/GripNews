<?php
/**
 * GripNews.uk — Driver Updates Intelligence
 * Migrated from sandbox/drivers.html
 */
require_once __DIR__ . '/includes/functions.php';

$page_title = "Driver Updates Intelligence — GripNews";
$page_desc  = "Real-time driver updates from 30 major hardware companies. GPU, CPU, firmware tracking.";
$nav_active = 'drivers';

require_once __DIR__ . '/includes/header.php';
?>

<style>
/* ── Driver Updates Intelligence Styles ───────────────────────────────────── */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;700&display=swap');

    :root{--bg:#0a0e17;--surface:#111827;--border:#1e293b;--cyan:#00d4ff;--text:#e8e8f0;--text-dim:#8892a6;--danger:#ff4757;--gold:#ffa502;--success:#2ed573;--purple:#a855f7}
    *{margin:0;padding:0;box-sizing:border-box}
    body{background:var(--bg);color:var(--text);font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;min-height:100vh}
    .container{max-width:1200px;margin:0 auto;padding:1.5rem}
    header{text-align:center;margin-bottom:2rem}
    header h1{font-size:2rem;background:linear-gradient(135deg,var(--cyan),var(--purple));-webkit-background-clip:text;-webkit-text-fill-color:transparent;margin-bottom:0.3rem}
    header p{color:var(--text-dim);font-size:0.9rem}
    .stats-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:0.8rem;margin-bottom:1.5rem}
    .stat-box{background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:1rem;text-align:center}
    .stat-box .num{font-size:1.6rem;font-weight:700;color:var(--cyan)}
    .stat-box .label{font-size:0.7rem;color:var(--text-dim);text-transform:uppercase;letter-spacing:0.05em}
    .filters{display:flex;gap:0.6rem;flex-wrap:wrap;margin-bottom:1.2rem;align-items:center}
    .search-box{flex:1;min-width:200px;background:var(--surface);border:1px solid var(--border);border-radius:8px;padding:0.6rem 1rem;color:var(--text);font-size:0.9rem;outline:none}
    .search-box:focus{border-color:var(--cyan)}
    select{background:var(--surface);border:1px solid var(--border);border-radius:8px;padding:0.6rem;color:var(--text);font-size:0.85rem;cursor:pointer;outline:none}
    select:focus{border-color:var(--cyan)}
    .pills{display:flex;gap:0.4rem;flex-wrap:wrap;margin-bottom:1rem}
    .pill{background:var(--surface);border:1px solid var(--border);border-radius:20px;padding:4px 14px;font-size:0.78rem;cursor:pointer;transition:all 0.2s;color:var(--text-dim)}
    .pill:hover,.pill.active{background:rgba(0,212,255,0.1);border-color:var(--cyan);color:var(--cyan)}
    .driver-grid{display:grid;gap:0.8rem}
    .driver-card{background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:1rem;transition:border-color 0.2s;cursor:pointer}
    .driver-card:hover{border-color:var(--cyan)}
    .driver-card .company-badge{display:inline-block;background:rgba(0,212,255,0.1);color:var(--cyan);padding:2px 10px;border-radius:12px;font-size:0.7rem;font-weight:600;margin-right:0.4rem}
    .driver-card .cat-badge{display:inline-block;padding:2px 8px;border-radius:12px;font-size:0.65rem;font-weight:600;text-transform:uppercase}
    .cat-gpu{background:rgba(168,85,247,0.15);color:var(--purple)}
    .cat-cpu{background:rgba(255,165,2,0.15);color:var(--gold)}
    .cat-firmware{background:rgba(255,71,87,0.15);color:var(--danger)}
    .cat-networking{background:rgba(46,213,115,0.15);color:var(--success)}
    .cat-storage{background:rgba(0,212,255,0.15);color:var(--cyan)}
    .cat-software{background:rgba(255,255,255,0.08);color:var(--text-dim)}
    .cat-mobile{background:rgba(255,165,2,0.1);color:#ffd43b}
    .cat-peripheral{background:rgba(168,85,247,0.1);color:#c084fc}
    .cat-chipset{background:rgba(46,213,115,0.1);color:#86efac}
    .cat-other{background:rgba(255,255,255,0.05);color:var(--text-dim)}
    .driver-card h3{font-size:0.95rem;margin:0.5rem 0 0.3rem;line-height:1.3}
    .driver-card .desc{font-size:0.8rem;color:var(--text-dim);line-height:1.4;margin-bottom:0.5rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
    .driver-card .meta{display:flex;gap:0.8rem;font-size:0.72rem;color:var(--text-dim);flex-wrap:wrap}
    .driver-card .meta span{display:flex;align-items:center;gap:3px}
    .version-tag{color:var(--success);font-weight:600}
    .load-more{text-align:center;margin:1.5rem 0}
    .load-more button{background:linear-gradient(135deg,var(--cyan),var(--purple));border:none;color:#fff;padding:0.7rem 2rem;border-radius:8px;cursor:pointer;font-weight:600;font-size:0.9rem}
    .load-more button:hover{opacity:0.9}
    .empty{text-align:center;color:var(--text-dim);padding:3rem;font-size:0.9rem}
    .spinner{width:30px;height:30px;border:3px solid var(--border);border-top-color:var(--cyan);border-radius:50%;animation:spin 0.8s linear infinite;margin:2rem auto}
    @keyframes spin{to{transform:rotate(360deg)}}
    .nav-links{display:flex;gap:1rem;justify-content:center;margin-bottom:1.5rem;flex-wrap:wrap}
    .nav-links a{color:var(--cyan);text-decoration:none;font-size:0.85rem;padding:4px 12px;border:1px solid var(--border);border-radius:6px;transition:all 0.2s}
    .nav-links a:hover{background:rgba(0,212,255,0.1);border-color:var(--cyan)}
    @media(max-width:600px){header h1{font-size:1.5rem}.filters{flex-direction:column}.stats-row{grid-template-columns:repeat(2,1fr)}}
  
/* ── Mobile Nav ── */
.hw-nav-toggle { display: none; background: none; border: none; cursor: pointer; width: 30px; height: 22px; position: relative; padding: 0; }
.hw-nav-toggle span { display: block; width: 100%; height: 2px; background: var(--text, #e8e8f0); border-radius: 2px; position: absolute; left: 0; transition: all 0.3s; }
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

@media (max-width: 768px) { nav.nav-links > a { display: none; } .hw-nav-toggle { display: block; } }

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

<div class="container">
    <h1>🖥️ Driver Updates Intelligence</h1>
    <p style="color:var(--text-dim);font-size:0.9rem;margin-bottom:1.5rem;">Real-time driver updates from 30+ hardware companies. GPU, CPU, firmware tracking — powered by GripAi.</p>
<div class="sb-powered"><span class="sb-powered-text">Powered by GripAi</span></div></div></div></div>

    <nav class="nav-links">
      <a href="/blog">Blog</a>
      <a href="https://gripai.uk">GripAi</a>
      <a href="/sandbox">Intelligence</a>
      <a href="/sandbox">Sandbox</a>
      <a href="https://gripai.uk/api">API</a>
<a href="https://gripai.uk/patches">Patches</a>
  <a href="mailto:hello@gripai.uk">Support</a>
      <a href="mailto:hello@gripai.uk">Support</a>
    

<button class="hw-nav-toggle" id="hwNavToggle" onclick="hwToggleNav()" aria-label="Menu">
  <span></span><span></span><span></span>
</button>
</nav>

    <div class="stats-row" id="stats-row">
      <div class="stat-box"><div class="num" id="stat-total">-</div><div class="label">Total Updates</div></div>
      <div class="stat-box"><div class="num" id="stat-companies">-</div><div class="label">Companies</div></div>
      <div class="stat-box"><div class="num" id="stat-gpu">-</div><div class="label">GPU Drivers</div></div>
      <div class="stat-box"><div class="num" id="stat-firmware">-</div><div class="label">Firmware</div></div>
    </div>

    <div class="filters">
      <input type="text" class="search-box" id="search" placeholder="Search drivers, companies, versions..." oninput="debounceSearch()">
      <select id="company-filter" onchange="applyFilters()"><option value="">All Companies</option></select>
      <select id="category-filter" onchange="applyFilters()">
        <option value="">All Categories</option>
        <option value="gpu">GPU</option>
        <option value="cpu">CPU</option>
        <option value="chipset">Chipset</option>
        <option value="networking">Networking</option>
        <option value="storage">Storage</option>
        <option value="firmware">Firmware</option>
        <option value="software">Software</option>
        <option value="peripheral">Peripheral</option>
        <option value="mobile">Mobile</option>
        <option value="other">Other</option>
      </select>
    </div>

    <div class="pills" id="company-pills"></div>
    <div id="results"><div class="spinner"></div></div>
    <div class="load-more" id="load-more" style="display:none"><button onclick="loadMore()">Load More</button></div>
  </div>
</div>


<script>
/* ── Driver Updates Intelligence Logic ────────────────────────────────────── */
    const API = 'https://gripai.uk/Jaffa/drivers';
    let allDrivers = [];
    let offset = 0;
    let total = 0;
    let debounceTimer;
    const LIMIT = 40;

    const CAT_ICONS = {gpu:'🎮',cpu:'⚡',chipset:'🔧',networking:'🌐',storage:'💾',firmware:'📟',software:'💻',peripheral:'🖱️',mobile:'📱',other:'📦'};

    function debounceSearch() {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(applyFilters, 350);
    }

    async function loadStats() {
      try {
        const r = await fetch(API + '/drivers/stats');
        const data = await r.json();
        document.getElementById('stat-total').textContent = data.total || 0;
        document.getElementById('stat-companies').textContent = (data.byCompany || []).length;
        const gpuCount = (data.byCategory || []).find(c => c.category === 'gpu');
        document.getElementById('stat-gpu').textContent = gpuCount ? gpuCount.count : 0;
        const fwCount = (data.byCategory || []).find(c => c.category === 'firmware');
        document.getElementById('stat-firmware').textContent = fwCount ? fwCount.count : 0;
      } catch(e) { console.warn('Stats failed:', e); }
    }

    async function loadCompanies() {
      try {
        const r = await fetch(API + '/drivers/companies');
        const data = await r.json();
        const sel = document.getElementById('company-filter');
        const pills = document.getElementById('company-pills');
        let pillsHtml = '<span class="pill active" onclick="filterCompany(this, \'\')">All</span>';
        data.forEach(c => {
          sel.innerHTML += '<option value="' + c.company + '">' + c.company + ' (' + c.count + ')</option>';
          if (c.count >= 5) {
            pillsHtml += '<span class="pill" onclick="filterCompany(this, \'' + c.company + '\')">' + c.company + '</span>';
          }
        });
        pills.innerHTML = pillsHtml;
      } catch(e) {}
    }

    function filterCompany(el, company) {
      document.querySelectorAll('.pill').forEach(p => p.classList.remove('active'));
      el.classList.add('active');
      document.getElementById('company-filter').value = company;
      applyFilters();
    }

    async function applyFilters() {
      offset = 0;
      allDrivers = [];
      await loadDrivers();
    }

    async function loadDrivers() {
      const q = document.getElementById('search').value;
      const company = document.getElementById('company-filter').value;
      const category = document.getElementById('category-filter').value;

      let url = API + '/drivers?limit=' + LIMIT + '&offset=' + offset;
      if (q) url += '&q=' + encodeURIComponent(q);
      if (company) url += '&company=' + encodeURIComponent(company);
      if (category) url += '&category=' + encodeURIComponent(category);

      if (offset === 0) document.getElementById('results').innerHTML = '<div class="spinner"></div>';

      try {
        const r = await fetch(url);
        const data = await r.json();
        total = data.total;

        if (offset === 0) allDrivers = data.drivers;
        else allDrivers = allDrivers.concat(data.drivers);

        renderDrivers();
      } catch(e) {
        document.getElementById('results').innerHTML = '<div class="empty">Failed to load driver data.</div>';
      }
    }

    function renderDrivers() {
      if (allDrivers.length === 0) {
        document.getElementById('results').innerHTML = '<div class="empty">No driver updates found matching your filters.</div>';
        document.getElementById('load-more').style.display = 'none';
        return;
      }

      let html = '<div class="driver-grid">';
      allDrivers.forEach(d => {
        const catClass = 'cat-' + (d.category || 'other');
        const catIcon = CAT_ICONS[d.category] || '📦';
        const desc = d.description ? '<div class="desc">' + escHtml(d.description) + '</div>' : '';
        const version = d.version ? '<span class="version-tag">v' + escHtml(d.version) + '</span>' : '';
        const platform = d.platform ? '<span>🖥️ ' + escHtml(d.platform) + '</span>' : '';
        const date = d.release_date || d.crawled_at;
        const dateStr = date ? timeAgo(date) : '';

        html += '<div class="driver-card" onclick="window.open(\'' + escAttr(d.url) + '\', \'_blank\')">' +
          '<span class="company-badge">' + escHtml(d.company) + '</span>' +
          '<span class="cat-badge ' + catClass + '">' + catIcon + ' ' + escHtml(d.category || 'other') + '</span>' +
          '<h3>' + escHtml(d.title) + '</h3>' +
          desc +
          '<div class="meta">' +
            (version ? version : '') +
            '<span>📰 ' + escHtml(d.source) + '</span>' +
            (platform ? platform : '') +
            (dateStr ? '<span>🕐 ' + dateStr + '</span>' : '') +
          '</div>' +
        '</div>';
      });
      html += '</div>';

      document.getElementById('results').innerHTML = html;
      document.getElementById('load-more').style.display = allDrivers.length < total ? '' : 'none';
    }

    function loadMore() {
      offset += LIMIT;
      loadDrivers();
    }

    function escHtml(s) { return (s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
    function escAttr(s) { return (s||'').replace(/'/g, "\\'").replace(/"/g, '&quot;'); }
    function timeAgo(d) {
      const diff = Date.now() - new Date(d).getTime();
      const mins = Math.floor(diff/60000);
      if (mins < 60) return mins + 'm ago';
      const hrs = Math.floor(mins/60);
      if (hrs < 24) return hrs + 'h ago';
      const days = Math.floor(hrs/24);
      if (days < 30) return days + 'd ago';
      return Math.floor(days/30) + 'mo ago';
    }

    // Init
    loadStats();
    loadCompanies();
    loadDrivers();
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
