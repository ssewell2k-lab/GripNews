  <footer class="site-footer">
    <div class="footer-inner">
      <div class="footer-copy">&copy; <?= date('Y') ?> GripNews. All rights reserved.</div>
      <div class="footer-links">
        <a href="/about">About</a>
        <a href="/archive">Archive</a>
        <a href="/privacy">Privacy</a>
        <a href="/terms">Terms</a>
        <a href="#" class="gn-cookie-settings-link" onclick="event.preventDefault();GripFeatures.showCookieSettings()">Cookie Settings</a>
        <a href="/accuracy">Accuracy</a>
        <a href="/graph">Graph</a>
        <a href="/pro">Grip Pro</a>
        <a href="/embed">Embed</a>
        <a href="/blog">Blog</a>
        <a href="/sandbox">Bug Tracker</a>
        <a href="/chatbox">Community Buzz</a>
        <a href="/feed.xml" title="RSS Feed">📡 RSS</a>
      </div>
    </div>
    <div class="footer-categories" style="max-width:900px;margin:16px auto 0;text-align:center;font-size:0.75em;">
      <?php foreach (get_categories() as $_ftr_slug => $_ftr_cat): ?>
        <a href="/<?= $_ftr_slug ?>" style="color:var(--text-dim);margin:0 8px;"><?= $_ftr_cat['label'] ?></a>
      <?php endforeach; ?>
    </div>
    <div class="footer-powered-by" style="max-width:900px;margin:16px auto 0;text-align:center;">
      Powered by <a href="https://gripai.uk" target="_blank">GripAI</a>
    </div>
  </footer>
</body>
</html>