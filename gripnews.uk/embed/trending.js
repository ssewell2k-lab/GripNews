(function() {
  var API = "https://gripai.uk/v2";
  var SITE = "https://gripnews.uk";
  var containerId = "gripnews-trending";
  var el = document.getElementById(containerId);
  if (!el) { el = document.createElement("div"); el.id = containerId; document.currentScript.parentNode.insertBefore(el, document.currentScript.nextSibling); }
  var style = document.createElement("style");
  style.textContent = '#gripnews-trending{font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;background:#0d1117;color:#e6edf3;border-radius:12px;border:1px solid #30363d;padding:20px;max-width:400px}#gripnews-trending .gt-title{font-size:1.1em;font-weight:700;margin-bottom:12px}#gripnews-trending .gt-title a{color:#e6edf3;text-decoration:none}#gripnews-trending .gt-row{display:flex;align-items:center;padding:8px 0;border-bottom:1px solid rgba(255,255,255,0.04)}#gripnews-trending .gt-row:last-child{border-bottom:none}#gripnews-trending .gt-icon{width:24px;font-size:.9em}#gripnews-trending .gt-name{flex:1;font-size:.9em}#gripnews-trending .gt-name a{color:#e6edf3;text-decoration:none}#gripnews-trending .gt-name a:hover{color:#2ecc71}#gripnews-trending .gt-delta{color:#2ecc71;font-weight:700;font-size:.88em}#gripnews-trending .gt-footer{margin-top:10px;font-size:.72em;color:#8b949e;text-align:right}#gripnews-trending .gt-footer a{color:#6c63ff;text-decoration:none}';
  document.head.appendChild(style);
  el.innerHTML = '<div style="text-align:center;padding:20px;color:#8b949e">Loading...</div>';
  fetch(API + "/momentum/top?direction=rising&limit=5")
    .then(function(r) { return r.json(); })
    .then(function(data) {
      var games = data.games || [];
      var html = '<div class="gt-title"><a href="' + SITE + '/watchlist" target="_blank">\u{1F4C8} Trending Games</a></div>';
      if (!games.length) { html += '<div style="padding:12px;color:#8b949e;font-size:.9em">No rising games detected right now.</div>'; }
      else { games.forEach(function(g) { var delta = g.delta > 0 ? "+" + g.delta.toFixed(1) : g.delta.toFixed(1); html += '<div class="gt-row"><span class="gt-icon">\u{1F7E2}</span><span class="gt-name"><a href="' + SITE + '/game/' + g.slug + '" target="_blank">' + g.game + '</a></span><span class="gt-delta">\u25B2 ' + delta + '</span></div>'; }); }
      html += '<div class="gt-footer"><a href="' + SITE + '/watchlist" target="_blank">Full Watchlist \u2192</a></div>';
      el.innerHTML = html;
    })
    .catch(function() { el.innerHTML = '<div style="text-align:center;padding:20px;color:#8b949e">Unable to load</div>'; });
})();