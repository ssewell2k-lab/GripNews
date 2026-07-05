(function() {
  var API = "https://gripnews.uk/api/v1";
  var _K = "gn_embed_REDACTED";
  var SITE = "https://gripnews.uk";
  var containerId = "gripnews-gamecard";
  var scriptTag = document.currentScript;
  var gameSlug = scriptTag.getAttribute("data-game") || "fortnite";
  var el = document.getElementById(containerId + "-" + gameSlug);
  if (!el) {
    el = document.createElement("div");
    el.id = containerId + "-" + gameSlug;
    scriptTag.parentNode.insertBefore(el, scriptTag.nextSibling);
  }
  var style = document.createElement("style");
  style.textContent = '.gn-gamecard{font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;background:#0d1117;color:#e6edf3;border-radius:12px;border:1px solid #30363d;padding:20px;max-width:360px;position:relative;overflow:hidden}.gn-gamecard::before{content:"";position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#6c63ff,#26c6da)}.gn-gamecard .gc-name{font-size:1.2em;font-weight:800;margin-bottom:2px}.gn-gamecard .gc-name a{color:#e6edf3;text-decoration:none}.gn-gamecard .gc-name a:hover{color:#6c63ff}.gn-gamecard .gc-cat{font-size:.72em;color:#8b949e;margin-bottom:14px}.gn-gamecard .gc-stats{display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:14px}.gn-gamecard .gc-stat{text-align:center;padding:8px 4px;background:rgba(255,255,255,.02);border-radius:8px;border:1px solid rgba(255,255,255,.04)}.gn-gamecard .gc-stat-val{font-size:1.3em;font-weight:800;line-height:1}.gn-gamecard .gc-stat-val.high{color:#2ecc71}.gn-gamecard .gc-stat-val.mid{color:#6c63ff}.gn-gamecard .gc-stat-val.neg{color:#ef4444}.gn-gamecard .gc-stat-label{font-size:.65em;color:#8b949e;text-transform:uppercase;letter-spacing:.5px;margin-top:2px}.gn-gamecard .gc-signals{font-size:.82em}.gn-gamecard .gc-signals .gc-sig{padding:6px 0;border-bottom:1px solid rgba(255,255,255,.04);display:flex;justify-content:space-between;align-items:center}.gn-gamecard .gc-signals .gc-sig:last-child{border-bottom:none}.gn-gamecard .gc-sig-title{flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-right:8px}.gn-gamecard .gc-sig-date{font-size:.85em;color:#8b949e;white-space:nowrap}.gn-gamecard .gc-footer{margin-top:12px;font-size:.7em;color:#8b949e;display:flex;justify-content:space-between;align-items:center}.gn-gamecard .gc-footer a{color:#6c63ff;text-decoration:none}.gn-gamecard .gc-footer a:hover{text-decoration:underline}.gn-gamecard .gc-powered{display:inline-flex;align-items:center;gap:4px}.gn-gamecard .gc-dot{width:5px;height:5px;border-radius:50%;background:#6c63ff;animation:gcPulse 2s infinite}@keyframes gcPulse{0%,100%{opacity:.4}50%{opacity:1}}';
  document.head.appendChild(style);
  el.innerHTML = '<div class="gn-gamecard"><div style="text-align:center;padding:20px;color:#8b949e">Loading ' + gameSlug + '...</div></div>';
  fetch(API + "/games/" + encodeURIComponent(gameSlug) + "?api_key=" + _K)
    .then(function(r) { if (!r.ok) throw new Error(r.status); return r.json(); })
    .then(function(g) {
      var momentum = g.momentum || "0";
      var momCls = momentum.indexOf("+") === 0 ? "high" : momentum.indexOf("-") === 0 ? "neg" : "mid";
      var html = '<div class="gn-gamecard">';
      html += '<div class="gc-name"><a href="' + (g.web_url || SITE) + '" target="_blank" rel="noopener">' + esc(g.name) + '</a></div>';
      html += '<div class="gc-cat">' + esc(g.primary_category || "gaming") + ' · ' + g.days_active + ' days active</div>';
      html += '<div class="gc-stats">';
      html += '<div class="gc-stat"><div class="gc-stat-val mid">' + g.mentions + '</div><div class="gc-stat-label">Signals</div></div>';
      html += '<div class="gc-stat"><div class="gc-stat-val high">' + g.avg_impact + '</div><div class="gc-stat-label">Avg Impact</div></div>';
      html += '<div class="gc-stat"><div class="gc-stat-val ' + momCls + '">' + momentum + '</div><div class="gc-stat-label">Momentum</div></div>';
      html += '</div>';
      if (g.signals && g.signals.length) {
        html += '<div class="gc-signals">';
        g.signals.slice(0, 4).forEach(function(s) {
          html += '<div class="gc-sig"><span class="gc-sig-title">' + esc(s.title) + '</span><span class="gc-sig-date">' + (s.date || "") + '</span></div>';
        });
        html += '</div>';
      }
      html += '<div class="gc-footer"><span class="gc-powered"><span class="gc-dot"></span> Grip Intelligence</span><a href="' + (g.web_url || SITE) + '" target="_blank" rel="noopener">Full Profile \u2192</a></div>';
      html += '</div>';
      el.innerHTML = html;
    })
    .catch(function() { el.innerHTML = '<div class="gn-gamecard"><div style="text-align:center;padding:20px;color:#8b949e">Game not found. <a href="' + SITE + '" target="_blank" style="color:#6c63ff">Visit GripNews</a></div></div>'; });

  function esc(s) { var d = document.createElement("div"); d.textContent = s || ""; return d.innerHTML; }
})();
