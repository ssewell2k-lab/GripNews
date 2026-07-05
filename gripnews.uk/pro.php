<?php
/**
 * GripNews.uk — Grip Pro & Grip Studio
 * Premium intelligence features for enthusiasts and businesses
 */
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Grip Pro — Premium Gaming Intelligence | GripNews';
$page_desc  = 'Unlock deeper gaming intelligence with Grip Pro for enthusiasts and Grip Studio for developers and businesses. Custom alerts, API access, historical analytics, and more.';
$nav_active = 'pro';

require_once __DIR__ . '/includes/header.php';
?>

<style>
/* ── Grip Pro Page ───────────────────────────────────── */
.pro-hero {
  text-align: center;
  padding: 56px 20px 40px;
  position: relative;
}
.pro-hero h1 {
  font-size: 2.6em;
  font-weight: 900;
  letter-spacing: -1px;
  margin-bottom: 8px;
}
.pro-hero h1 .accent { color: var(--accent); }
.pro-hero h1 .pro-badge {
  display: inline-block;
  background: linear-gradient(135deg, var(--accent), #a855f7);
  color: #fff;
  font-size: 0.35em;
  padding: 4px 14px;
  border-radius: 20px;
  vertical-align: super;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  font-weight: 800;
}
.pro-hero p {
  color: var(--text-muted);
  max-width: 600px;
  margin: 12px auto 0;
  font-size: 1.1em;
  line-height: 1.6;
}

/* Free vs Pro comparison */
.pro-comparison {
  max-width: 900px;
  margin: 0 auto 48px;
  padding: 0 20px;
}
.pro-comparison h2 {
  text-align: center;
  font-size: 1.6em;
  font-weight: 800;
  margin-bottom: 32px;
}
.pro-comparison h2 span { color: var(--accent); }

.comparison-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
}
@media (max-width: 600px) {
  .comparison-grid { grid-template-columns: 1fr; }
}

.comparison-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 32px 28px;
  transition: border-color 0.3s;
}
.comparison-card:hover { border-color: var(--border-hover); }

.comparison-card h3 {
  font-size: 1.2em;
  font-weight: 700;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.comparison-card h3 .card-badge {
  font-size: 0.6em;
  padding: 3px 10px;
  border-radius: 12px;
  letter-spacing: 0.5px;
  font-weight: 700;
}
.card-badge.free-badge {
  background: rgba(0,230,118,0.15);
  color: #00e676;
}
.card-badge.pro-badge {
  background: linear-gradient(135deg, rgba(0,229,255,0.2), rgba(168,85,247,0.2));
  color: #a855f7;
}

.comparison-card ul {
  list-style: none;
  padding: 0;
  margin: 0;
}
.comparison-card ul li {
  padding: 8px 0;
  color: var(--text-muted);
  font-size: 0.92em;
  display: flex;
  align-items: flex-start;
  gap: 10px;
  line-height: 1.4;
}
.comparison-card ul li::before {
  flex-shrink: 0;
  font-size: 1em;
}
.free-list li::before { content: "✅"; }
.pro-list li::before { content: "⚡"; }

/* Tier cards */
.pro-tiers {
  max-width: 960px;
  margin: 0 auto 48px;
  padding: 0 20px;
}
.pro-tiers > h2 {
  text-align: center;
  font-size: 1.6em;
  font-weight: 800;
  margin-bottom: 12px;
}
.pro-tiers > h2 span { color: var(--accent); }
.pro-tiers > p.tier-subtitle {
  text-align: center;
  color: var(--text-muted);
  margin-bottom: 36px;
  font-size: 0.95em;
}

.tier-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 28px;
}
@media (max-width: 700px) {
  .tier-grid { grid-template-columns: 1fr; }
}

.tier-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 20px;
  padding: 36px 32px 32px;
  position: relative;
  overflow: hidden;
  transition: border-color 0.3s, transform 0.2s;
}
.tier-card:hover {
  border-color: var(--border-hover);
  transform: translateY(-2px);
}
.tier-card.studio-card {
  border-color: rgba(168,85,247,0.3);
}
.tier-card.studio-card:hover {
  border-color: rgba(168,85,247,0.6);
}

.tier-card .tier-icon {
  font-size: 2.4em;
  margin-bottom: 16px;
}
.tier-card h3 {
  font-size: 1.5em;
  font-weight: 800;
  margin-bottom: 6px;
}
.tier-card h3 .tier-label {
  color: var(--accent);
}
.tier-card.studio-card h3 .tier-label {
  color: #a855f7;
}
.tier-card .tier-audience {
  color: var(--text-muted);
  font-size: 0.85em;
  margin-bottom: 24px;
}
.tier-card .tier-price {
  font-size: 1.1em;
  font-weight: 700;
  margin-bottom: 24px;
  color: var(--text-muted);
}
.tier-card .tier-price .coming { 
  display: inline-block;
  background: linear-gradient(135deg, rgba(0,229,255,0.15), rgba(168,85,247,0.15));
  color: var(--accent);
  padding: 4px 14px;
  border-radius: 20px;
  font-size: 0.85em;
  letter-spacing: 0.5px;
}
.tier-card.studio-card .tier-price .coming {
  color: #a855f7;
}

.tier-features {
  list-style: none;
  padding: 0;
  margin: 0 0 28px;
}
.tier-features li {
  padding: 7px 0;
  color: var(--text-muted);
  font-size: 0.9em;
  display: flex;
  align-items: flex-start;
  gap: 10px;
  line-height: 1.4;
}
.tier-features li .feat-icon { flex-shrink: 0; }

.tier-card .tier-cta {
  display: block;
  text-align: center;
  padding: 12px 24px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 0.95em;
  text-decoration: none;
  transition: all 0.2s;
  cursor: pointer;
  border: none;
  width: 100%;
}
.tier-card .tier-cta.pro-cta {
  background: linear-gradient(135deg, var(--accent), #0099cc);
  color: #fff;
}
.tier-card .tier-cta.pro-cta:hover {
  filter: brightness(1.15);
  transform: translateY(-1px);
}
.tier-card .tier-cta.studio-cta {
  background: linear-gradient(135deg, #a855f7, #6c3bbd);
  color: #fff;
}
.tier-card .tier-cta.studio-cta:hover {
  filter: brightness(1.15);
  transform: translateY(-1px);
}

/* Use cases */
.pro-usecases {
  max-width: 900px;
  margin: 0 auto 48px;
  padding: 0 20px;
}
.pro-usecases h2 {
  text-align: center;
  font-size: 1.6em;
  font-weight: 800;
  margin-bottom: 32px;
}
.pro-usecases h2 span { color: var(--accent); }

.usecase-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}
@media (max-width: 700px) {
  .usecase-grid { grid-template-columns: 1fr; }
}

.usecase-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 24px 20px;
  text-align: center;
  transition: border-color 0.3s;
}
.usecase-card:hover { border-color: var(--border-hover); }
.usecase-card .uc-icon {
  font-size: 2em;
  margin-bottom: 12px;
}
.usecase-card h4 {
  font-size: 1em;
  font-weight: 700;
  margin-bottom: 8px;
}
.usecase-card p {
  color: var(--text-muted);
  font-size: 0.85em;
  line-height: 1.5;
  margin: 0;
}

/* Notify form */
.pro-notify {
  max-width: 560px;
  margin: 0 auto 56px;
  padding: 0 20px;
  text-align: center;
}
.pro-notify h2 {
  font-size: 1.4em;
  font-weight: 800;
  margin-bottom: 8px;
}
.pro-notify p {
  color: var(--text-muted);
  font-size: 0.92em;
  margin-bottom: 20px;
}
.notify-form {
  display: flex;
  gap: 10px;
  max-width: 420px;
  margin: 0 auto;
}
@media (max-width: 480px) {
  .notify-form { flex-direction: column; }
}
.notify-form input[type="email"] {
  flex: 1;
  padding: 12px 16px;
  border-radius: 10px;
  border: 1px solid var(--border);
  background: var(--bg-card);
  color: var(--text, #e2e8f0);
  font-size: 0.92em;
  outline: none;
  transition: border-color 0.2s;
}
.notify-form input[type="email"]:focus {
  border-color: var(--accent);
}
.notify-form button {
  padding: 12px 24px;
  border-radius: 10px;
  border: none;
  background: linear-gradient(135deg, var(--accent), #a855f7);
  color: #fff;
  font-weight: 700;
  font-size: 0.92em;
  cursor: pointer;
  transition: filter 0.2s, transform 0.2s;
  white-space: nowrap;
}
.notify-form button:hover {
  filter: brightness(1.15);
  transform: translateY(-1px);
}
.notify-success {
  display: none;
  color: #00e676;
  font-weight: 600;
  margin-top: 12px;
}

/* FAQ */
.pro-faq {
  max-width: 700px;
  margin: 0 auto 56px;
  padding: 0 20px;
}
.pro-faq h2 {
  text-align: center;
  font-size: 1.4em;
  font-weight: 800;
  margin-bottom: 28px;
}
.faq-item {
  border-bottom: 1px solid var(--border);
  padding: 16px 0;
}
.faq-item:first-of-type { border-top: 1px solid var(--border); }
.faq-q {
  font-weight: 700;
  font-size: 0.95em;
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
}
.faq-q .faq-toggle {
  color: var(--text-muted);
  font-size: 1.2em;
  transition: transform 0.2s;
}
.faq-item.open .faq-toggle { transform: rotate(45deg); }
.faq-a {
  color: var(--text-muted);
  font-size: 0.88em;
  line-height: 1.6;
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.3s ease, padding 0.3s;
  padding-top: 0;
}
.faq-item.open .faq-a {
  max-height: 200px;
  padding-top: 12px;
}
</style>

<!-- Hero -->
<section class="pro-hero">
  <h1>Grip <span class="accent">Pro</span> <span class="pro-badge">Coming Soon</span></h1>
  <p>Premium intelligence for the people who don't just read gaming news — they use it.</p>
</section>

<!-- Free vs Pro -->
<section class="pro-comparison">
  <h2>What stays <span>free</span>, what goes Pro</h2>
  <div class="comparison-grid">
    <div class="comparison-card">
      <h3>🟢 Always Free <span class="card-badge free-badge">OPEN</span></h3>
      <ul class="free-list">
        <li>Latest news and daily intelligence signals</li>
        <li>Top signals ranked by impact score</li>
        <li>Game intelligence pages (860+ games)</li>
        <li>Release Radar and upcoming launches</li>
        <li>Basic momentum rankings</li>
        <li>Community Buzz and Bug Tracker</li>
        <li>Category feeds (patches, esports, indie…)</li>
        <li>Blog and Weekly intelligence roundups</li>
      </ul>
    </div>
    <div class="comparison-card">
      <h3>⚡ Grip Pro <span class="card-badge pro-badge">PREMIUM</span></h3>
      <ul class="pro-list">
        <li>Historical momentum charts and trends</li>
        <li>Release risk analysis and predictions</li>
        <li>Advanced filters (genre, platform, publisher)</li>
        <li>Daily intelligence email briefing</li>
        <li>Custom watchlists with instant alerts</li>
        <li>Downloadable datasets (CSV/JSON)</li>
        <li>Advanced analytics dashboards</li>
        <li>Higher API rate limits</li>
      </ul>
    </div>
  </div>
</section>

<!-- Two tiers -->
<section class="pro-tiers">
  <h2>Two ways to <span>go Pro</span></h2>
  <p class="tier-subtitle">Whether you're a dedicated gamer or building a business on gaming data.</p>
  <div class="tier-grid">
    <!-- Grip Pro -->
    <div class="tier-card">
      <div class="tier-icon">🎮</div>
      <h3>Grip <span class="tier-label">Pro</span></h3>
      <div class="tier-audience">For enthusiasts, content creators, and competitive players</div>
      <div class="tier-price"><span class="coming">Coming Soon</span></div>
      <ul class="tier-features">
        <li><span class="feat-icon">📊</span> Historical momentum charts — track any game over time</li>
        <li><span class="feat-icon">🔔</span> Custom watchlist alerts — get notified when tracked games change</li>
        <li><span class="feat-icon">📧</span> Daily intelligence email — your personalised morning briefing</li>
        <li><span class="feat-icon">🎯</span> Release risk scores — AI-predicted delay and quality indicators</li>
        <li><span class="feat-icon">🔍</span> Advanced filters — slice signals by genre, platform, publisher</li>
        <li><span class="feat-icon">📥</span> Data exports — download signals and trends as CSV</li>
        <li><span class="feat-icon">🚫</span> Ad-free experience</li>
      </ul>
      <button class="tier-cta pro-cta" onclick="document.querySelector('.notify-form input').focus();window.scrollTo({top:document.querySelector('.pro-notify').offsetTop-80,behavior:'smooth'})">
        Notify Me at Launch
      </button>
    </div>

    <!-- Grip Studio -->
    <div class="tier-card studio-card">
      <div class="tier-icon">🏢</div>
      <h3>Grip <span class="tier-label">Studio</span></h3>
      <div class="tier-audience">For developers, studios, publishers, and gaming businesses</div>
      <div class="tier-price"><span class="coming">Coming Soon</span></div>
      <ul class="tier-features">
        <li><span class="feat-icon">🔌</span> Full API access — structured gaming intelligence for your products</li>
        <li><span class="feat-icon">📦</span> Bulk data downloads — JSON/CSV datasets updated daily</li>
        <li><span class="feat-icon">📈</span> Analytics dashboards — industry trends and competitive positioning</li>
        <li><span class="feat-icon">🧩</span> Embeddable widgets — live intelligence on your own site</li>
        <li><span class="feat-icon">📡</span> Webhook alerts — push notifications for monitored games or studios</li>
        <li><span class="feat-icon">🏷️</span> Studio visibility tools — promote your releases to our audience</li>
        <li><span class="feat-icon">🤝</span> Priority support and custom integrations</li>
      </ul>
      <button class="tier-cta studio-cta" onclick="document.querySelector('.notify-form input').focus();window.scrollTo({top:document.querySelector('.pro-notify').offsetTop-80,behavior:'smooth'})">
        Notify Me at Launch
      </button>
    </div>
  </div>
</section>

<!-- Use cases -->
<section class="pro-usecases">
  <h2>What you'll actually <span>pay for</span></h2>
  <div class="usecase-grid">
    <div class="usecase-card">
      <div class="uc-icon">🔔</div>
      <h4>Instant Alerts</h4>
      <p>"Tell me the moment a watched game gets delayed, patched, or enters controversy."</p>
    </div>
    <div class="usecase-card">
      <div class="uc-icon">📧</div>
      <h4>Morning Briefing</h4>
      <p>"Email me every morning with changes to my tracked games and studios."</p>
    </div>
    <div class="usecase-card">
      <div class="uc-icon">🔌</div>
      <h4>API Access</h4>
      <p>"Give me structured release data so I can build my own dashboards and tools."</p>
    </div>
  </div>
</section>

<!-- Notify signup -->
<section class="pro-notify" id="notify">
  <h2>Get notified when Grip Pro launches</h2>
  <p>We'll email you once — no spam, no drip campaigns.</p>
  <form class="notify-form" onsubmit="return gripProNotify(this)">
    <input type="email" name="email" placeholder="you@example.com" required>
    <button type="submit">Notify Me</button>
  </form>
  <div class="notify-success" id="proNotifySuccess">✅ You're on the list. We'll be in touch.</div>
</section>

<!-- FAQ -->
<section class="pro-faq">
  <h2>Questions</h2>
  
  <div class="faq-item" onclick="this.classList.toggle('open')">
    <div class="faq-q">When does Grip Pro launch? <span class="faq-toggle">+</span></div>
    <div class="faq-a">We're building the foundation right now. Once we have consistent traffic data and can prove the value, we'll open a beta — likely with a small monthly fee and founder pricing for early supporters.</div>
  </div>
  
  <div class="faq-item" onclick="this.classList.toggle('open')">
    <div class="faq-q">Will free features ever become paid? <span class="faq-toggle">+</span></div>
    <div class="faq-a">No. The core intelligence feed — daily signals, game pages, categories, blog — will always be free. Pro features are about saving time (alerts, exports, email briefings), not gating content.</div>
  </div>
  
  <div class="faq-item" onclick="this.classList.toggle('open')">
    <div class="faq-q">What's the difference between Pro and Studio? <span class="faq-toggle">+</span></div>
    <div class="faq-a">Grip Pro is for individual enthusiasts and content creators who want deeper insight and personalised alerts. Grip Studio is for businesses — developers, publishers, and platforms that want API access, bulk data, and promotional tools.</div>
  </div>
  
  <div class="faq-item" onclick="this.classList.toggle('open')">
    <div class="faq-q">Is there an API right now? <span class="faq-toggle">+</span></div>
    <div class="faq-a">Yes — there's a free API tier at <a href="/developers">/developers</a> with basic access. Grip Studio will offer higher rate limits, webhook integrations, and bulk data endpoints.</div>
  </div>
  
  <div class="faq-item" onclick="this.classList.toggle('open')">
    <div class="faq-q">Will there be founder pricing? <span class="faq-toggle">+</span></div>
    <div class="faq-a">That's the plan. Sign up for the notification and you'll be first in line for early-access pricing that locks in permanently.</div>
  </div>
</section>

<script>
function gripProNotify(form) {
  var email = form.email.value;
  fetch('/newsletter-signup.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ email: email, source: 'grip-pro' })
  }).then(function(r) { return r.json(); }).then(function(d) {
    document.getElementById('proNotifySuccess').style.display = 'block';
    form.style.display = 'none';
  }).catch(function() {
    document.getElementById('proNotifySuccess').style.display = 'block';
    document.getElementById('proNotifySuccess').textContent = '✅ Thanks! We\'ll notify you at launch.';
    form.style.display = 'none';
  });
  return false;
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
