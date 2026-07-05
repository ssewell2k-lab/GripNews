<?php
/**
 * GripNews.uk — Developer Portal
 * Phase 13: Grip Protocol documentation & widget showcase.
 */
require_once __DIR__ . '/includes/functions.php';

$page_title = "Developers — Grip Protocol";
$page_desc  = "Build on gaming intelligence. REST API, embeddable widgets, and real-time data. Free to start.";
$nav_active = 'developers';

require_once __DIR__ . '/includes/header.php';
?>

<style>
.dev-hero{text-align:center;padding:60px 20px 40px;position:relative}
.dev-hero h1{font-size:2.4em;margin-bottom:8px;letter-spacing:-0.5px}
.dev-hero h1 span{background:linear-gradient(135deg,var(--accent),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.dev-hero .subtitle{color:var(--muted);font-size:1.05em;max-width:600px;margin:0 auto 24px}
.dev-hero .badge{display:inline-block;padding:4px 14px;border-radius:20px;font-size:.72em;font-weight:700;letter-spacing:1px;text-transform:uppercase;background:rgba(108,99,255,.12);color:var(--accent);border:1px solid rgba(108,99,255,.25)}

.dev-grid{max-width:1100px;margin:0 auto;padding:0 20px}
.dev-section{margin-bottom:56px}
.dev-section h2{font-size:1.3em;margin-bottom:6px;display:flex;align-items:center;gap:8px}
.dev-section h2 .icon{font-size:1.2em}
.dev-section .desc{color:var(--muted);font-size:.9em;margin-bottom:20px}

.endpoint-list{display:flex;flex-direction:column;gap:12px}
.ep-card{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:18px 20px;transition:border-color .2s}
.ep-card:hover{border-color:var(--accent)}
.ep-top{display:flex;align-items:center;gap:10px;margin-bottom:6px;flex-wrap:wrap}
.ep-method{padding:2px 8px;border-radius:5px;font-size:.7em;font-weight:700;font-family:monospace;background:rgba(38,198,218,.12);color:var(--cyan);letter-spacing:.5px}
.ep-path{font-family:monospace;font-size:.9em;color:#e6edf3;font-weight:600}
.ep-new{padding:1px 6px;border-radius:4px;font-size:.6em;font-weight:700;background:rgba(255,145,0,.15);color:#ff9100;letter-spacing:.5px}
.ep-desc{color:var(--muted);font-size:.85em;line-height:1.5}
.ep-params{margin-top:10px;display:flex;flex-wrap:wrap;gap:6px}
.ep-param{padding:2px 8px;border-radius:4px;font-size:.72em;font-family:monospace;background:rgba(255,255,255,.04);color:#8b949e;border:1px solid rgba(255,255,255,.06)}

.try-it{margin-top:14px;border-radius:10px;overflow:hidden;border:1px solid var(--border)}
.try-bar{display:flex;align-items:center;background:rgba(255,255,255,.03);padding:8px 14px;gap:8px}
.try-bar input{flex:1;background:transparent;border:none;color:#e6edf3;font-family:monospace;font-size:.82em;outline:none}
.try-bar button{padding:5px 14px;border:none;border-radius:6px;font-size:.78em;font-weight:600;cursor:pointer;background:var(--accent);color:#fff;transition:opacity .2s}
.try-bar button:hover{opacity:.85}
.try-output{background:var(--bg-deeper,#0d1117);padding:14px;font-family:monospace;font-size:.78em;color:#8b949e;max-height:300px;overflow:auto;white-space:pre-wrap;display:none}

.widget-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px}
.widget-card{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:20px;transition:border-color .2s}
.widget-card:hover{border-color:var(--accent)}
.widget-card h3{font-size:1em;margin-bottom:4px}
.widget-card .wdesc{color:var(--muted);font-size:.82em;margin-bottom:12px}
.widget-code{background:rgba(0,0,0,.3);border:1px solid var(--border);border-radius:8px;padding:10px 14px;font-family:monospace;font-size:.75em;color:var(--accent);cursor:pointer;position:relative;overflow-x:auto;word-break:break-all}
.widget-code:hover::after{content:'Click to copy';position:absolute;right:8px;top:50%;transform:translateY(-50%);font-size:.9em;color:var(--muted)}
.widget-code.copied::after{content:'Copied!';color:var(--cyan)}

.tier-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px}
.tier-card{background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:24px 20px;text-align:center;position:relative;overflow:hidden}
.tier-card.featured{border-color:var(--accent)}
.tier-card.featured::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--accent),var(--cyan))}
.tier-name{font-size:.7em;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:var(--muted);margin-bottom:6px}
.tier-price{font-size:2em;font-weight:800;margin-bottom:4px}
.tier-price span{font-size:.4em;color:var(--muted);font-weight:400}
.tier-desc{color:var(--muted);font-size:.82em;margin-bottom:16px}
.tier-features{list-style:none;padding:0;text-align:left;font-size:.82em;color:#ccc}
.tier-features li{padding:5px 0;border-bottom:1px solid rgba(255,255,255,.04)}
.tier-features li::before{content:'✓ ';color:var(--cyan)}
.tier-btn{display:inline-block;margin-top:16px;padding:8px 24px;border-radius:8px;font-size:.82em;font-weight:600;text-decoration:none;border:1px solid var(--border);color:#e6edf3;transition:all .2s}
.tier-btn:hover{border-color:var(--accent);color:var(--accent)}
.tier-card.featured .tier-btn{background:var(--accent);border-color:var(--accent);color:#fff}
.tier-card.featured .tier-btn:hover{opacity:.9}

.powered-badge{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:8px;font-size:.8em;font-weight:600;background:var(--bg-card);border:1px solid var(--border);color:#e6edf3;text-decoration:none}
.powered-badge:hover{border-color:var(--accent)}
.powered-badge .dot{width:6px;height:6px;border-radius:50%;background:var(--accent);animation:pulse-dot 2s infinite}
@keyframes pulse-dot{0%,100%{opacity:.4}50%{opacity:1}}

.badge-grid{display:flex;flex-wrap:wrap;gap:12px;margin-top:12px}
.badge-code{margin-top:16px;background:rgba(0,0,0,.3);border:1px solid var(--border);border-radius:8px;padding:12px 16px;font-family:monospace;font-size:.75em;color:var(--accent);cursor:pointer}
</style>

<section class="dev-hero">
    <span class="badge">Protocol v1</span>
    <h1>Build on <span>Grip Intelligence</span></h1>
    <p class="subtitle">Gaming signals, entity data, momentum scores, release intelligence — all available as structured APIs and embeddable widgets. Free to start.</p>
</section>

<div class="dev-grid">

    <!-- ── API Reference ── -->
    <section class="dev-section" id="api">
        <h2><span class="icon">⚡</span> API Reference</h2>
        <p class="desc">REST API. JSON responses. CORS enabled. 300 requests/hour free, no key needed to start.</p>
        <p class="desc" style="font-family:monospace;color:var(--cyan);font-size:.85em">Base URL: https://gripnews.uk/api/v1</p>
        
        <div class="endpoint-list">
            <div class="ep-card">
                <div class="ep-top"><span class="ep-method">GET</span><span class="ep-path">/signals</span></div>
                <div class="ep-desc">Today's intelligence signals, ranked by impact. Filter by category, score, tag, or date range.</div>
                <div class="ep-params">
                    <span class="ep-param">?date=YYYY-MM-DD</span>
                    <span class="ep-param">?category=patch</span>
                    <span class="ep-param">?min_score=7</span>
                    <span class="ep-param">?tag=fortnite</span>
                    <span class="ep-param">?days=7</span>
                    <span class="ep-param">?limit=20</span>
                </div>
                <div class="try-it" id="try-signals">
                    <div class="try-bar">
                        <span style="color:var(--cyan);font-family:monospace;font-size:.8em">GET</span>
                        <input type="text" value="/api/v1/signals?limit=3" id="try-signals-url">
                        <button onclick="tryEndpoint('signals')">Try it</button>
                    </div>
                    <pre class="try-output" id="try-signals-output"></pre>
                </div>
            </div>

            <div class="ep-card">
                <div class="ep-top"><span class="ep-method">GET</span><span class="ep-path">/games</span></div>
                <div class="ep-desc">All games seen in signal data. Returns mentions, impact, momentum, and activity days.</div>
                <div class="try-it" id="try-games">
                    <div class="try-bar">
                        <span style="color:var(--cyan);font-family:monospace;font-size:.8em">GET</span>
                        <input type="text" value="/api/v1/games" id="try-games-url">
                        <button onclick="tryEndpoint('games')">Try it</button>
                    </div>
                    <pre class="try-output" id="try-games-output"></pre>
                </div>
            </div>

            <div class="ep-card">
                <div class="ep-top"><span class="ep-method">GET</span><span class="ep-path">/games/{slug}</span></div>
                <div class="ep-desc">Full game entity — signal history, momentum, impact breakdown, category trends.</div>
                <div class="try-it" id="try-game">
                    <div class="try-bar">
                        <span style="color:var(--cyan);font-family:monospace;font-size:.8em">GET</span>
                        <input type="text" value="/api/v1/games/fortnite" id="try-game-url">
                        <button onclick="tryEndpoint('game')">Try it</button>
                    </div>
                    <pre class="try-output" id="try-game-output"></pre>
                </div>
            </div>

            <div class="ep-card">
                <div class="ep-top"><span class="ep-method">GET</span><span class="ep-path">/studios</span></div>
                <div class="ep-desc">Studio rankings with game counts, scores, and signal activity.</div>
                <div class="try-it" id="try-studios">
                    <div class="try-bar">
                        <span style="color:var(--cyan);font-family:monospace;font-size:.8em">GET</span>
                        <input type="text" value="/api/v1/studios" id="try-studios-url">
                        <button onclick="tryEndpoint('studios')">Try it</button>
                    </div>
                    <pre class="try-output" id="try-studios-output"></pre>
                </div>
            </div>

            <div class="ep-card">
                <div class="ep-top"><span class="ep-method">GET</span><span class="ep-path">/momentum</span><span class="ep-new">NEW</span></div>
                <div class="ep-desc">Top momentum movers — games with rising or falling signal activity, weighted by impact and recency.</div>
                <div class="ep-params">
                    <span class="ep-param">?days=7</span>
                    <span class="ep-param">?limit=20</span>
                </div>
                <div class="try-it" id="try-momentum">
                    <div class="try-bar">
                        <span style="color:var(--cyan);font-family:monospace;font-size:.8em">GET</span>
                        <input type="text" value="/api/v1/momentum?limit=5" id="try-momentum-url">
                        <button onclick="tryEndpoint('momentum')">Try it</button>
                    </div>
                    <pre class="try-output" id="try-momentum-output"></pre>
                </div>
            </div>

            <div class="ep-card">
                <div class="ep-top"><span class="ep-method">GET</span><span class="ep-path">/releases/radar</span><span class="ep-new">NEW</span></div>
                <div class="ep-desc">Upcoming and recent releases ranked by hype index. Tracks announcements, reveals, and launches.</div>
                <div class="try-it" id="try-radar">
                    <div class="try-bar">
                        <span style="color:var(--cyan);font-family:monospace;font-size:.8em">GET</span>
                        <input type="text" value="/api/v1/releases/radar" id="try-radar-url">
                        <button onclick="tryEndpoint('radar')">Try it</button>
                    </div>
                    <pre class="try-output" id="try-radar-output"></pre>
                </div>
            </div>

            <div class="ep-card">
                <div class="ep-top"><span class="ep-method">GET</span><span class="ep-path">/releases/risk</span><span class="ep-new">NEW</span></div>
                <div class="ep-desc">Release delay risk index. Tracks delays, postponements, and cancellations.</div>
            </div>

            <div class="ep-card">
                <div class="ep-top"><span class="ep-method">GET</span><span class="ep-path">/trends</span><span class="ep-new">NEW</span></div>
                <div class="ep-desc">Trending patterns, category distribution, and rising games over a configurable window.</div>
                <div class="ep-params"><span class="ep-param">?days=7</span></div>
            </div>

            <div class="ep-card">
                <div class="ep-top"><span class="ep-method">GET</span><span class="ep-path">/graph</span><span class="ep-new">NEW</span></div>
                <div class="ep-desc">Entity relationship graph. Nodes (games/topics) and edges (co-occurrence in signals).</div>
                <div class="ep-params"><span class="ep-param">?days=14</span></div>
            </div>

            <div class="ep-card">
                <div class="ep-top"><span class="ep-method">GET</span><span class="ep-path">/overview</span></div>
                <div class="ep-desc">Platform-wide intelligence summary — total signals, entity counts, category breakdown.</div>
            </div>

            <div class="ep-card">
                <div class="ep-top"><span class="ep-method">GET</span><span class="ep-path">/weekly</span></div>
                <div class="ep-desc">Weekly intelligence report with biggest stories, patterns, and rising games.</div>
            </div>

            <div class="ep-card">
                <div class="ep-top"><span class="ep-method">GET</span><span class="ep-path">/categories</span></div>
                <div class="ep-desc">Signal categories with descriptions and 30-day signal counts.</div>
            </div>
        </div>
    </section>

    <!-- ── Embeddable Widgets ── -->
    <section class="dev-section" id="widgets">
        <h2><span class="icon">📦</span> Embeddable Widgets</h2>
        <p class="desc">Drop a single <code>&lt;script&gt;</code> tag on any page. Auto-updating, dark-themed, responsive. No API key needed.</p>

        <div class="widget-grid">
            <div class="widget-card">
                <h3>🏆 Top 10 Games</h3>
                <p class="wdesc">Daily top 10 games ranked by momentum score.</p>
                <div class="widget-code" onclick="copyWidget(this)">&lt;script src="https://gripnews.uk/embed/top10.js"&gt;&lt;/script&gt;</div>
            </div>
            <div class="widget-card">
                <h3>📈 Trending Games</h3>
                <p class="wdesc">Games with rising momentum right now.</p>
                <div class="widget-code" onclick="copyWidget(this)">&lt;script src="https://gripnews.uk/embed/trending.js"&gt;&lt;/script&gt;</div>
            </div>
            <div class="widget-card">
                <h3>🚀 Momentum Movers</h3>
                <p class="wdesc">Biggest momentum changes today.</p>
                <div class="widget-code" onclick="copyWidget(this)">&lt;script src="https://gripnews.uk/embed/momentum.js"&gt;&lt;/script&gt;</div>
            </div>
            <div class="widget-card">
                <h3>📡 Signal Feed <span class="ep-new">NEW</span></h3>
                <p class="wdesc">Live intelligence signals, filterable by category.</p>
                <div class="widget-code" onclick="copyWidget(this)">&lt;script src="https://gripnews.uk/embed/signals.js"&gt;&lt;/script&gt;</div>
            </div>
            <div class="widget-card">
                <h3>🎮 Game Card <span class="ep-new">NEW</span></h3>
                <p class="wdesc">Per-game intelligence card. Pass a game slug as attribute.</p>
                <div class="widget-code" onclick="copyWidget(this)">&lt;script src="https://gripnews.uk/embed/gamecard.js" data-game="fortnite"&gt;&lt;/script&gt;</div>
            </div>
            <div class="widget-card">
                <h3>🔥 Release Radar <span class="ep-new">NEW</span></h3>
                <p class="wdesc">Upcoming releases ranked by hype index.</p>
                <div class="widget-code" onclick="copyWidget(this)">&lt;script src="https://gripnews.uk/embed/releases.js"&gt;&lt;/script&gt;</div>
            </div>
        </div>
    </section>

    <!-- ── Powered by Grip Badge ── -->
    <section class="dev-section" id="badges">
        <h2><span class="icon">⚡</span> Powered by Grip</h2>
        <p class="desc">Show your integration. Drop a badge on your site, bot, or tool.</p>
        
        <div class="badge-grid">
            <a href="https://gripnews.uk" class="powered-badge" target="_blank">
                <span class="dot"></span> Powered by Grip Intelligence
            </a>
            <a href="https://gripnews.uk" class="powered-badge" target="_blank" style="background:transparent;border-color:rgba(108,99,255,.3)">
                <span class="dot"></span> Grip Protocol
            </a>
        </div>
        
        <div class="badge-code" onclick="navigator.clipboard.writeText(this.textContent.trim());this.style.color='var(--cyan)'">
&lt;a href="https://gripnews.uk" style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:8px;font-size:13px;font-weight:600;background:#161b22;border:1px solid #30363d;color:#e6edf3;text-decoration:none;font-family:system-ui"&gt;⚡ Powered by Grip Intelligence&lt;/a&gt;
        </div>
    </section>

    <!-- ── Tiers ── -->
    <section class="dev-section" id="tiers">
        <h2><span class="icon">🏗️</span> Access Tiers</h2>
        <p class="desc">Start free. Scale when you need to.</p>

        <div class="tier-grid">
            <div class="tier-card">
                <div class="tier-name">Free</div>
                <div class="tier-price">£0 <span>/month</span></div>
                <div class="tier-desc">For hobbyists and experiments</div>
                <ul class="tier-features">
                    <li>300 requests/hour</li>
                    <li>All public endpoints</li>
                    <li>Embeddable widgets</li>
                    <li>CORS enabled</li>
                    <li>Community support</li>
                </ul>
                <a href="#api" class="tier-btn">Start Building</a>
            </div>

            <div class="tier-card featured">
                <div class="tier-name">Grip Pro</div>
                <div class="tier-price">£9 <span>/month</span></div>
                <div class="tier-desc">For creators and communities</div>
                <ul class="tier-features">
                    <li>10,000 requests/hour</li>
                    <li>All endpoints + priority</li>
                    <li>Custom embed themes</li>
                    <li>Historical data access</li>
                    <li>Email support</li>
                </ul>
                <a href="mailto:hello@gripnews.uk?subject=Grip%20Pro%20Access" class="tier-btn">Contact Us</a>
            </div>

            <div class="tier-card">
                <div class="tier-name">Developer</div>
                <div class="tier-price">£29 <span>/month</span></div>
                <div class="tier-desc">For apps and bots</div>
                <ul class="tier-features">
                    <li>100,000 requests/hour</li>
                    <li>Webhooks (coming soon)</li>
                    <li>Batch endpoints</li>
                    <li>White-label embeds</li>
                    <li>Priority support</li>
                </ul>
                <a href="mailto:hello@gripnews.uk?subject=Grip%20Developer%20Access" class="tier-btn">Contact Us</a>
            </div>

            <div class="tier-card">
                <div class="tier-name">Enterprise</div>
                <div class="tier-price">Custom</div>
                <div class="tier-desc">For studios and analytics</div>
                <ul class="tier-features">
                    <li>Unlimited requests</li>
                    <li>Dedicated infrastructure</li>
                    <li>Custom data feeds</li>
                    <li>SLA guarantees</li>
                    <li>Account manager</li>
                </ul>
                <a href="mailto:hello@gripnews.uk?subject=Grip%20Enterprise" class="tier-btn">Talk to Us</a>
            </div>
        </div>
    </section>

    <!-- ── Use Cases ── -->
    <section class="dev-section" id="use-cases">
        <h2><span class="icon">🧩</span> Use Cases</h2>
        <p class="desc">Where Grip Protocol fits.</p>
        
        <div class="widget-grid">
            <div class="widget-card">
                <h3>Discord Bots</h3>
                <p class="wdesc">Feed /top10, /signals, and /momentum into Discord commands. Keep your community informed automatically.</p>
            </div>
            <div class="widget-card">
                <h3>Gaming Blogs</h3>
                <p class="wdesc">Embed live widgets for trending games, releases, and signals. Always fresh, zero maintenance.</p>
            </div>
            <div class="widget-card">
                <h3>Streamer Overlays</h3>
                <p class="wdesc">Pull game intelligence into OBS overlays. Show patch status, momentum, and breaking signals live.</p>
            </div>
            <div class="widget-card">
                <h3>Analytics Dashboards</h3>
                <p class="wdesc">Pull structured game and studio data into internal tools. Track releases, risks, and industry momentum.</p>
            </div>
            <div class="widget-card">
                <h3>Creator Tools</h3>
                <p class="wdesc">Build content calendars from release radar. Find trending topics before they peak.</p>
            </div>
            <div class="widget-card">
                <h3>Publisher Monitoring</h3>
                <p class="wdesc">Studios and publishers can monitor their own signal footprint, competitor movements, and community sentiment.</p>
            </div>
        </div>
    </section>

</div>

<script>
function tryEndpoint(name) {
    var input = document.getElementById('try-' + name + '-url');
    var output = document.getElementById('try-' + name + '-output');
    var url = 'https://gripnews.uk' + input.value;
    
    output.style.display = 'block';
    output.textContent = 'Loading...';
    
    fetch(url)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            output.textContent = JSON.stringify(data, null, 2);
        })
        .catch(function(err) {
            output.textContent = 'Error: ' + err.message;
        });
}

function copyWidget(el) {
    var text = el.textContent.trim()
        .replace(/&lt;/g, '<')
        .replace(/&gt;/g, '>')
        .replace(/&amp;/g, '&');
    navigator.clipboard.writeText(text);
    el.classList.add('copied');
    setTimeout(function() { el.classList.remove('copied'); }, 2000);
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
