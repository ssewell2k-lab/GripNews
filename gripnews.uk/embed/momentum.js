(function() {
  var API = "https://gripai.uk/v2";
  var SITE = "https://gripnews.uk";
  var scripts = document.getElementsByTagName("script");
  var thisScript = scripts[scripts.length - 1];
  var gameSlug = thisScript.getAttribute("data-game") || "fortnite";
  var containerId = "gripnews-momentum-" + gameSlug;
  var el = document.getElementById(containerId);
  if (!el) { el = document.createElement("div"); el.id = containerId; thisScript.parentNode.insertBefore(el, thisScript.nextSibling); }
  var style = document.createElement("style");
  style.textContent = '[id^="gripnews-momentum-"]{font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;background:#0d1117;color:#e6edf3;border-radius:12px;border:1px solid #30363d;padding:20px;max-width:300px;display:inline-block}[id^="gripnews-momentum-"] .gm-game{font-size:1em;font-weight:700;margin-bottom:8px}[id^="gripnews-momentum-"] .gm-game a{color:#e6edf3;text-decoration:none}[id^="gripnews-momentum-"] .gm-game a:hover{color:#6c63ff}[id^="gripnews-momentum-"] .gm-score{font-size:2.4em;font-weight:900;text-align:center;margin:8px 0}[id^="gripnews-momentum-"] .gm-score.high{color:#2ecc71}[id^="gripnews-momentum-"] .gm-score.mid{color:#6c63ff}[id^="gripnews-momentum-"] .gm-score.low{color:#8b949e}[id^="gripnews-momentum-"] .gm-label{text-align:center;font-size:.72em;text-transform:uppercase;letter-spacing:1px;color:#8b949e}[id^="gripnews-momentum-"] .gm-dir{text-align:center;font-size:.85em;margin-top:4px}[id^="gripnews-momentum-"] .gm-footer{margin-top:10px;font-size:.7em;color:#8b949e;text-align:center}[id^="gripnews-momentum-"] .gm-footer a{color:#6c63ff;text-decoration:none}';
  document.head.appendChild(style);
  fetch(API + "/momentum/" + gameSlug)
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.error) throw new Error(data.error);
      var g = data;
      var sc = (g.momentum || 0).toFixed(1);
      var cls = g.momentum > 50 ? "high" : g.momentum > 20 ? "mid" : "low";
      var dir = g.direction === "rising" ? "\u{1F7E2} Rising" : g.direction === "falling" ? "\u{1F534} Falling" : "\u26AA Stable";
      el.innerHTML = '<div class="gm-game"><a href="' + SITE + '/game/' + gameSlug + '" target="_blank">\u{1F3AE} ' + (g.game || gameSlug) + '</a></div><div class="gm-score ' + cls + '">' + sc + '</div><div class="gm-label">Momentum Score</div><div class="gm-dir">' + dir + '</div><div class="gm-footer">Powered by <a href="' + SITE + '" target="_blank">GripNews</a></div>';
    })
    .catch(function() { el.innerHTML = '<div style="padding:12px;color:#8b949e;font-size:.85em;text-align:center">Game data unavailable</div>'; });
})();