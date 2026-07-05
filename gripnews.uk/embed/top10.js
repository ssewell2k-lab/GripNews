(function() {
  var API = "https://gripai.uk/v2";
  var SITE = "https://gripnews.uk";
  var containerId = "gripnews-top10";
  var el = document.getElementById(containerId);
  if (!el) {
    el = document.createElement("div");
    el.id = containerId;
    document.currentScript.parentNode.insertBefore(el, document.currentScript.nextSibling);
  }
  var style = document.createElement("style");
  style.textContent = '#gripnews-top10{font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;background:#0d1117;color:#e6edf3;border-radius:12px;border:1px solid #30363d;padding:20px;max-width:400px}#gripnews-top10 .gn-title{font-size:1.1em;font-weight:700;margin-bottom:12px}#gripnews-top10 .gn-title a{color:#e6edf3;text-decoration:none}#gripnews-top10 .gn-title a:hover{color:#6c63ff}#gripnews-top10 .gn-row{display:flex;align-items:center;padding:6px 0;border-bottom:1px solid rgba(255,255,255,0.04);font-size:.88em}#gripnews-top10 .gn-row:last-child{border-bottom:none}#gripnews-top10 .gn-rank{width:28px;font-weight:800;color:#8b949e;font-size:.85em}#gripnews-top10 .gn-rank.top3{color:#ffd700}#gripnews-top10 .gn-name{flex:1}#gripnews-top10 .gn-name a{color:#e6edf3;text-decoration:none}#gripnews-top10 .gn-name a:hover{color:#6c63ff}#gripnews-top10 .gn-score{font-weight:700;width:50px;text-align:right}#gripnews-top10 .gn-score.high{color:#2ecc71}#gripnews-top10 .gn-score.mid{color:#6c63ff}#gripnews-top10 .gn-score.low{color:#8b949e}#gripnews-top10 .gn-dir{width:20px;text-align:center;font-size:.8em;margin-left:6px}#gripnews-top10 .gn-footer{margin-top:10px;font-size:.72em;color:#8b949e;display:flex;justify-content:space-between;align-items:center}#gripnews-top10 .gn-footer a{color:#6c63ff;text-decoration:none}#gripnews-top10 .gn-footer a:hover{text-decoration:underline}';
  document.head.appendChild(style);
  el.innerHTML = '<div style="text-align:center;padding:20px;color:#8b949e">Loading rankings...</div>';
  fetch(API + "/momentum/top?limit=10")
    .then(function(r) { return r.json(); })
    .then(function(data) {
      var games = data.games || [];
      if (!games.length) { el.innerHTML = '<div style="text-align:center;padding:20px;color:#8b949e">Rankings updating soon</div>'; return; }
      var html = '<div class="gn-title"><a href="' + SITE + '/rankings" target="_blank" rel="noopener">\u{1F3C6} Top 10 Games by Momentum</a></div>';
      games.forEach(function(g, i) {
        var sc = g.momentum.toFixed(1);
        var cls = g.momentum > 50 ? "high" : g.momentum > 20 ? "mid" : "low";
        var dir = g.direction === "rising" ? "\u{1F7E2}" : g.direction === "falling" ? "\u{1F534}" : "\u26AA";
        var rankCls = i < 3 ? "gn-rank top3" : "gn-rank";
        html += '<div class="gn-row"><span class="' + rankCls + '">' + (i+1) + '</span><span class="gn-name"><a href="' + SITE + '/game/' + g.slug + '" target="_blank" rel="noopener">' + g.game + '</a></span><span class="gn-score ' + cls + '">' + sc + '</span><span class="gn-dir">' + dir + '</span></div>';
      });
      html += '<div class="gn-footer"><span>Updated: ' + (data.date || "daily") + '</span><a href="' + SITE + '/rankings" target="_blank" rel="noopener">View Full Top 100 \u2192</a></div>';
      el.innerHTML = html;
    })
    .catch(function() { el.innerHTML = '<div style="text-align:center;padding:20px;color:#8b949e">Unable to load. <a href="' + SITE + '/rankings" target="_blank" style="color:#6c63ff">Visit GripNews</a></div>'; });
})();