<?php
require_once __DIR__ . '/includes/functions.php';

$page_title = 'About — GripNews';
$page_desc = 'GripNews is a gaming intelligence platform. Not just news — signals, trends, patterns, and insights powered by GripAI.';
$page_canonical = SITE_URL . '/about';

require_once __DIR__ . '/includes/header.php';
?>

  <article class="static-page">
    <h1>About <span style="color:var(--accent)">GripNews</span></h1>
    
    <section class="about-section">
      <h2>Not news. Intelligence.</h2>
      <p>Most gaming news sites follow a simple loop: <em>news happens → summarise → publish</em>. That's a race to the bottom.</p>
      <p>GripNews is different. Every story has a <strong>Grip layer</strong> — structured intelligence that tells you not just <em>what happened</em>, but <em>why it matters</em>, <em>who is affected</em>, and <em>what it signals</em>.</p>
    </section>

    <section class="about-section">
      <h2>How It Works</h2>
      <div class="about-grid">
        <div class="about-card">
          <div class="about-icon">📡</div>
          <h3>Signal Collection</h3>
          <p>GripAI continuously monitors gaming data sources — patches, player trends, industry moves, esports results, and indie developments.</p>
        </div>
        <div class="about-card">
          <div class="about-icon">🧠</div>
          <h3>Intelligence Analysis</h3>
          <p>Every story is scored for impact across four dimensions: players, developers, esports, and industry. Confidence levels (confirmed, developing, rumour) ensure transparency.</p>
        </div>
        <div class="about-card">
          <div class="about-icon">🔍</div>
          <h3>Pattern Detection</h3>
          <p>Instead of treating stories individually, GripNews looks for signals — recurring patterns, category spikes, and emerging trends across the gaming landscape.</p>
        </div>
        <div class="about-card">
          <div class="about-icon">💡</div>
          <h3>GripAI Insight</h3>
          <p>Every story includes a unique analytical observation — the insight that connects dots most readers miss. That's the part you remember.</p>
        </div>
      </div>
    </section>

    <section class="about-section">
      <h2>The GripNews Signal</h2>
      <p>Each signal published on GripNews follows a structured format:</p>
      <div class="format-list">
        <div class="format-item"><span class="format-label">What Happened</span> The actual news</div>
        <div class="format-item"><span class="format-label">Why It Matters</span> The significance explained</div>
        <div class="format-item"><span class="format-label">Who Is Affected</span> Players · Developers · Publishers · Esports</div>
        <div class="format-item"><span class="format-label">Impact Score</span> 1–10 across four dimensions</div>
        <div class="format-item"><span class="format-label">Confidence</span> Confirmed · Developing · Rumour</div>
        <div class="format-item"><span class="format-label">GripAI Insight</span> One unique analytical observation</div>
      </div>
    </section>

    <section class="about-section">
      <h2>Part of the GRIP0S Ecosystem</h2>
      <p>GripNews is the public signal layer of a broader gaming intelligence network:</p>
      <ul>
        <li><strong><a href="https://gripai.uk" target="_blank">GripAI</a></strong> — Deep analysis engine</li>
        <li><strong><a href="https://gripai.uk" target="_blank">GameGrip</a></strong> — Gaming data platform</li>
        <li><strong>GripAI</strong> — The intelligence behind it all</li>
      </ul>
    </section>

    <section class="about-section about-manifesto">
      <h2>Our Manifesto</h2>
      <blockquote class="insight-quote">
        When someone visits GripNews, they shouldn't think "I learned what happened."<br>
        They should think "I understand what's happening."<br><br>
        That's the difference between a news site and an intelligence platform.
      </blockquote>
    </section>

    <section class="about-section">
      <p style="color:var(--text-dim);text-align:center;margin-top:32px;">
        20 signals published daily · Updated at 07:45 BST<br>
        Built in the UK 🇬🇧
      </p>
    </section>
  </article>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
