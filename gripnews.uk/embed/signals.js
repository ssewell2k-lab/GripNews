(function() {
  var API = "https://gripnews.uk/api/v1";
  var _K = "gn_embed_REDACTED";
  var SITE = "https://gripnews.uk";
  var containerId = "gripnews-signals";
  var el = document.getElementById(containerId);
  if (!el) {
    el = document.createElement("div");
    el.id = containerId;
    document.currentScript.parentNode.insertBefore(el, document.currentScript.nextSibling);
  }
  var limit = el.getAttribute("data-limit") || "5";
  var category = el.getAttribute("data-category") || "";
  var style = document.createElement("style");
  style.textContent = '#gripnews-signals{font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;background:#0d1117;color:#e6edf3;border-radius:12px;border:1px solid #30363d;padding:20px;max-width:440px}#gripnews-signals .gns-title{font-size:1.1em;font-weight:700;margin-bottom:14px;display:flex;align-items:center;justify-content:space-between}#gripnews-signals .gns-title a{color:#e6edf3;text-decoration:none}#gripnews-signals .gns-title a:hover{color:#6c63ff}#gripnews-signals .gns-live{font-size:.65em;padding:2px 8px;border-radius:10px;background:rgba(46,204,113,.12);color:#2ecc71;font-weight:600;letter-spacing:.5px}#gripnews-signals .gns-card{padding:10px 0;border-bottom:1px solid rgba(255,255,255,.04)}#gripnews-signals .gns-card:last-child{border-bottom:none}#gripnews-signals .gns-row{display:flex;align-items:flex-start;gap:10px}#gripnews-signals .gns-score{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.82em;flex-shrink:0}#gripnews-signals .gns-score.high{background:rgba(239,68,68,.12);color:#ef4444}#gripnews-signals .gns-score.mid{background:rgba(108,99,255,.12);color:#6c63ff}#gripnews-signals .gns-score.low{background:rgba(139,148,158,.1);color:#8b949e}#gripnews-signals .gns-body{flex:1;min-width:0}#gripnews-signals .gns-headline{font-size:.88em;font-weight:600;line-height:1.3;margin-bottom:2px}#gripnews-signals .gns-headline a{color:#e6edf3;text-decoration:none}#gripnews-signals .gns-headline a:hover{color:#6c63ff}#gripnews-signals .gns-meta{font-size:.72em;color:#8b949e;display:flex;gap:8px;align-items:center}#gripnews-signals .gns-cat{padding:1px 6px;border-radius:4px;font-weight:600;font-size:.9em}#gripnews-signals .gns-cat-patch{background:rgba(38,198,218,.1);color:#26c6da}#gripnews-signals .gns-cat-release{background:rgba(102,187,106,.1);color:#66bb6a}#gripnews-signals .gns-cat-industry{background:rgba(255,145,0,.1);color:#ff9100}#gripnews-signals .gns-cat-esports{background:rgba(255,215,0,.1);color:#ffd700}#gripnews-signals .gns-cat-indie{background:rgba(108,99,255,.1);color:#6c63ff}#gripnews-signals .gns-cat-rumor{background:rgba(239,83,80,.1);color:#ef5350}#gripnews-signals .gns-footer{margin-top:12px;font-size:.7em;color:#8b949e;display:flex;justify-content:space-between;align-items:center}#gripnews-signals .gns-footer a{color:#6c63ff;text-decoration:none}#gripnews-signals .gns-footer a:hover{text-decoration:underline}#gripnews-signals .gns-powered{display:inline-flex;align-items:center;gap:4px}#gripnews-signals .gns-dot{width:5px;height:5px;border-radius:50%;background:#6c63ff;animation:gnsPulse 2s infinite}@keyframes gnsPulse{0%,100%{opacity:.4}50%{opacity:1}}';
  document.head.appendChild(style);
  el.innerHTML = '<div style="text-align:center;padding:20px;color:#8b949e">Loading signals...</div>';
  var url = API + "/signals?api_key=" + _K + "&limit=" + limit;
  if (category) url += "&category=" + category;
  fetch(url)
    .then(function(r) { return r.json(); })
    .then(function(data) {
      var signals = data.signals || [];
      if (!signals.length) { el.innerHTML = '<div style="text-align:center;padding:20px;color:#8b949e">No signals yet today.</div>'; return; }
      var catMap = {patch:"patch",update:"patch",release:"release",announcement:"release",industry:"industry",esports:"esports",indie:"indie",rumor:"rumor",rumour:"rumor"};
      var html = '<div class="gns-title"><a href="' + SITE + '/signals" target="_blank" rel="noopener">\u{1F4E1} Intelligence Signals</a><span class="gns-live">LIVE</span></div>';
      signals.forEach(function(s) {
        var sc = s.score || 0;
        var cls = sc >= 7 ? "high" : sc >= 4 ? "mid" : "low";
        var cat = (s.category || "Update").toLowerCase();
        var catCls = "gns-cat gns-cat-" + (catMap[cat] || "industry");
        html += '<div class="gns-card"><div class="gns-row"><div class="gns-score ' + cls + '">' + sc + '</div><div class="gns-body"><div class="gns-headline"><a href="' + (s.url || SITE) + '" target="_blank" rel="noopener">' + esc(s.title) + '</a></div><div class="gns-meta"><span class="' + catCls + '">' + esc(s.category || "Update") + '</span><span>' + esc(s.date || "") + '</span></div></div></div></div>';
      });
      html += '<div class="gns-footer"><span class="gns-powered"><span class="gns-dot"></span> Powered by Grip Intelligence</span><a href="' + SITE + '/signals" target="_blank" rel="noopener">View All \u2192</a></div>';
      el.innerHTML = html;
    })
    .catch(function() { el.innerHTML = '<div style="text-align:center;padding:20px;color:#8b949e">Unable to load. <a href="' + SITE + '/signals" target="_blank" style="color:#6c63ff">Visit GripNews</a></div>'; });

  function esc(s) { var d = document.createElement("div"); d.textContent = s || ""; return d.innerHTML; }
})();
