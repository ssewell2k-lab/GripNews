(function() {
  var API = "https://gripnews.uk/api/v1";
  var _K = "gn_embed_REDACTED";
  var SITE = "https://gripnews.uk";
  var containerId = "gripnews-releases";
  var el = document.getElementById(containerId);
  if (!el) {
    el = document.createElement("div");
    el.id = containerId;
    document.currentScript.parentNode.insertBefore(el, document.currentScript.nextSibling);
  }
  var style = document.createElement("style");
  style.textContent = '#gripnews-releases{font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;background:#0d1117;color:#e6edf3;border-radius:12px;border:1px solid #30363d;padding:20px;max-width:400px}#gripnews-releases .gnr-title{font-size:1.1em;font-weight:700;margin-bottom:14px}#gripnews-releases .gnr-title a{color:#e6edf3;text-decoration:none}#gripnews-releases .gnr-title a:hover{color:#6c63ff}#gripnews-releases .gnr-row{display:flex;align-items:center;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.04);gap:10px;font-size:.88em}#gripnews-releases .gnr-row:last-child{border-bottom:none}#gripnews-releases .gnr-hype{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.8em;background:rgba(255,145,0,.1);color:#ff9100;flex-shrink:0}#gripnews-releases .gnr-info{flex:1;min-width:0}#gripnews-releases .gnr-name{font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}#gripnews-releases .gnr-name a{color:#e6edf3;text-decoration:none}#gripnews-releases .gnr-name a:hover{color:#6c63ff}#gripnews-releases .gnr-meta{font-size:.8em;color:#8b949e;display:flex;gap:6px}#gripnews-releases .gnr-conf{padding:1px 5px;border-radius:3px;font-size:.85em;font-weight:600}#gripnews-releases .gnr-conf-confirmed{background:rgba(46,204,113,.1);color:#2ecc71}#gripnews-releases .gnr-conf-rumor{background:rgba(239,83,80,.1);color:#ef5350}#gripnews-releases .gnr-conf-leak{background:rgba(255,215,0,.1);color:#ffd700}#gripnews-releases .gnr-footer{margin-top:12px;font-size:.7em;color:#8b949e;display:flex;justify-content:space-between;align-items:center}#gripnews-releases .gnr-footer a{color:#6c63ff;text-decoration:none}#gripnews-releases .gnr-footer a:hover{text-decoration:underline}#gripnews-releases .gnr-powered{display:inline-flex;align-items:center;gap:4px}#gripnews-releases .gnr-dot{width:5px;height:5px;border-radius:50%;background:#6c63ff;animation:gnrPulse 2s infinite}@keyframes gnrPulse{0%,100%{opacity:.4}50%{opacity:1}}';
  document.head.appendChild(style);
  el.innerHTML = '<div style="text-align:center;padding:20px;color:#8b949e">Loading releases...</div>';
  fetch(API + "/releases/radar?api_key=" + _K)
    .then(function(r) { return r.json(); })
    .then(function(data) {
      var releases = (data.releases || []).slice(0, 8);
      if (!releases.length) { el.innerHTML = '<div style="text-align:center;padding:20px;color:#8b949e">No release signals yet.</div>'; return; }
      var html = '<div class="gnr-title"><a href="' + SITE + '/upcoming" target="_blank" rel="noopener">\u{1F680} Release Radar</a></div>';
      releases.forEach(function(r) {
        var hype = r.hype_index || r.score || 0;
        var conf = (r.confidence || "confirmed").toLowerCase();
        var confCls = "gnr-conf gnr-conf-" + (conf === "rumor" || conf === "rumour" ? "rumor" : conf === "leak" ? "leak" : "confirmed");
        html += '<div class="gnr-row"><div class="gnr-hype">' + hype + '</div><div class="gnr-info"><div class="gnr-name"><a href="' + (r.url || SITE) + '" target="_blank" rel="noopener">' + esc(r.title) + '</a></div><div class="gnr-meta"><span class="' + confCls + '">' + esc(r.confidence || "confirmed") + '</span><span>' + esc(r.date || "") + '</span></div></div></div>';
      });
      html += '<div class="gnr-footer"><span class="gnr-powered"><span class="gnr-dot"></span> Grip Intelligence</span><a href="' + SITE + '/upcoming" target="_blank" rel="noopener">Full Radar \u2192</a></div>';
      el.innerHTML = html;
    })
    .catch(function() { el.innerHTML = '<div style="text-align:center;padding:20px;color:#8b949e">Unable to load. <a href="' + SITE + '/upcoming" target="_blank" style="color:#6c63ff">Visit GripNews</a></div>'; });

  function esc(s) { var d = document.createElement("div"); d.textContent = s || ""; return d.innerHTML; }
})();
