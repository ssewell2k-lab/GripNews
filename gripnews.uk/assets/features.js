/* ============================================================
   GripNews Features — Theme, Cookie, Newsletter, Social Share
   v2 — 2026-07-04
   ============================================================ */
(function() {
  'use strict';

  /* ─── THEME TOGGLE ─── */
  const THEME_KEY = 'gn_theme';

  function initTheme() {
    // Check saved preference, then system preference, default dark
    const saved = localStorage.getItem(THEME_KEY);
    let theme = saved;
    if (!theme) {
      theme = window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
    }
    applyTheme(theme, false);
    injectToggleButton();

    // Listen for system preference changes (if no manual override)
    window.matchMedia('(prefers-color-scheme: light)').addEventListener('change', (e) => {
      if (!localStorage.getItem(THEME_KEY)) {
        applyTheme(e.matches ? 'light' : 'dark', false);
        updateToggleIcon();
      }
    });
  }

  function applyTheme(theme, animate) {
    if (animate) {
      document.documentElement.setAttribute('data-theme', theme);
    } else {
      // No transition on initial load
      document.documentElement.style.transition = 'none';
      document.documentElement.setAttribute('data-theme', theme);
      requestAnimationFrame(() => {
        requestAnimationFrame(() => {
          document.documentElement.style.transition = '';
        });
      });
    }
  }

  function injectToggleButton() {
    const nav = document.querySelector('.nav-links');
    if (!nav) return;

    const btn = document.createElement('button');
    btn.className = 'theme-toggle';
    btn.setAttribute('aria-label', 'Toggle light/dark mode');
    btn.setAttribute('title', 'Toggle light/dark mode');
    updateIcon(btn);

    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      const current = document.documentElement.getAttribute('data-theme') || 'dark';
      const next = current === 'dark' ? 'light' : 'dark';
      applyTheme(next, true);
      localStorage.setItem(THEME_KEY, next);
      updateIcon(btn);
    });

    // Insert before the last item (More dropdown) or at end
    const moreDropdown = nav.querySelector('.nav-dropdown:last-of-type');
    if (moreDropdown) {
      nav.insertBefore(btn, moreDropdown);
    } else {
      nav.appendChild(btn);
    }
  }

  function updateIcon(btn) {
    const theme = document.documentElement.getAttribute('data-theme') || 'dark';
    btn.innerHTML = theme === 'dark' ? '☀️' : '🌙';
  }

  function updateToggleIcon() {
    const btn = document.querySelector('.theme-toggle');
    if (btn) updateIcon(btn);
  }

  // Apply theme ASAP (before DOM ready) for no flash
  (function() {
    const saved = localStorage.getItem(THEME_KEY);
    if (saved) {
      document.documentElement.setAttribute('data-theme', saved);
    }
  })();

  /* ─── COOKIE CONSENT ─── */
  const COOKIE_KEY = 'gn_cookie_consent';
  const COOKIE_VERSION = 1;

  function getCookieConsent() {
    try {
      const raw = localStorage.getItem(COOKIE_KEY);
      if (!raw) return null;
      const data = JSON.parse(raw);
      if (data.version !== COOKIE_VERSION) return null;
      return data;
    } catch(e) { return null; }
  }

  function saveCookieConsent(prefs) {
    localStorage.setItem(COOKIE_KEY, JSON.stringify({
      version: COOKIE_VERSION,
      timestamp: Date.now(),
      essential: true,
      analytics: prefs.analytics,
      marketing: prefs.marketing
    }));
  }

  function applyConsent(prefs) {
    if (prefs.analytics) {
      enableGA();
    } else {
      disableGA();
    }
  }

  function enableGA() {
    // GA4 is loaded via gtag.js in header — enable data collection
    if (window.gtag) {
      gtag('consent', 'update', {
        'analytics_storage': 'granted'
      });
    }
  }

  function disableGA() {
    if (window.gtag) {
      gtag('consent', 'update', {
        'analytics_storage': 'denied'
      });
    }
    // Delete existing GA cookies
    document.cookie.split(';').forEach(function(c) {
      var name = c.trim().split('=')[0];
      if (name.startsWith('_ga') || name.startsWith('_gid')) {
        document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/;domain=.' + location.hostname;
        document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/';
      }
    });
  }

  function createCookieBanner() {
    var banner = document.createElement('div');
    banner.className = 'gn-cookie-banner';
    banner.id = 'gnCookieBanner';
    banner.innerHTML =
      '<div class="gn-cookie-inner">' +
        '<div class="gn-cookie-main">' +
          '<div class="gn-cookie-icon">🍪</div>' +
          '<div class="gn-cookie-text">' +
            '<h3>We value your privacy</h3>' +
            '<p>GripNews uses cookies to analyse site traffic and optimise your experience. ' +
            'We use <strong>Google Analytics</strong> to understand how visitors interact with our site. ' +
            'You can accept, reject, or customise your preferences below. ' +
            '<a href="/privacy">Privacy Policy</a></p>' +
            '<div class="gn-cookie-actions">' +
              '<button class="gn-cookie-btn gn-cookie-btn-accept" onclick="GripFeatures.acceptAllCookies()">Accept All</button>' +
              '<button class="gn-cookie-btn gn-cookie-btn-reject" onclick="GripFeatures.rejectAllCookies()">Essential Only</button>' +
              '<button class="gn-cookie-btn gn-cookie-btn-manage" onclick="GripFeatures.toggleCookiePrefs()">Manage Preferences</button>' +
            '</div>' +
          '</div>' +
        '</div>' +
        '<div class="gn-cookie-prefs" id="gnCookiePrefs">' +
          '<div class="gn-cookie-category">' +
            '<div class="gn-cookie-cat-info">' +
              '<strong>Essential Cookies <span class="gn-cookie-cat-badge">Always Active</span></strong>' +
              '<span>Required for the site to function. These handle sessions, remember your preferences (like cookie consent), and keep things running. No personal data is collected.</span>' +
            '</div>' +
            '<label class="gn-toggle">' +
              '<input type="checkbox" checked disabled>' +
              '<span class="gn-toggle-slider"></span>' +
            '</label>' +
          '</div>' +
          '<div class="gn-cookie-category">' +
            '<div class="gn-cookie-cat-info">' +
              '<strong>Analytics Cookies</strong>' +
              '<span>Google Analytics (GA4) collects anonymised data about page views, session duration, and which features are used. This helps us understand what content matters most. Data is processed by Google under their <a href="https://policies.google.com/privacy" target="_blank">Privacy Policy</a>.</span>' +
            '</div>' +
            '<label class="gn-toggle">' +
              '<input type="checkbox" id="gnToggleAnalytics">' +
              '<span class="gn-toggle-slider"></span>' +
            '</label>' +
          '</div>' +
          '<div class="gn-cookie-category">' +
            '<div class="gn-cookie-cat-info">' +
              '<strong>Marketing Cookies</strong>' +
              '<span>Currently not in use. In the future, these may be used for personalised content recommendations and measuring campaign performance. We\'ll update this section when enabled.</span>' +
            '</div>' +
            '<label class="gn-toggle">' +
              '<input type="checkbox" id="gnToggleMarketing" disabled>' +
              '<span class="gn-toggle-slider"></span>' +
            '</label>' +
          '</div>' +
          '<div class="gn-cookie-prefs-save">' +
            '<button class="gn-cookie-btn gn-cookie-btn-accept" onclick="GripFeatures.savePrefs()">Save Preferences</button>' +
          '</div>' +
        '</div>' +
      '</div>';
    document.body.appendChild(banner);

    // Set default consent to denied until user opts in
    if (window.gtag) {
      gtag('consent', 'default', {
        'analytics_storage': 'denied',
        'ad_storage': 'denied'
      });
    }

    setTimeout(function() { banner.classList.add('visible'); }, 300);
  }

  function hideBanner() {
    var banner = document.getElementById('gnCookieBanner');
    if (banner) {
      banner.classList.remove('visible');
      setTimeout(function() { banner.remove(); }, 500);
    }
  }

  /* ─── NEWSLETTER POPUP ─── */
  var NL_KEY = 'gn_newsletter';
  var NL_DISMISS_DAYS = 30;
  var NL_SHOW_DELAY = 25000; // 25 seconds
  var NL_SCROLL_THRESHOLD = 0.45; // 45% scroll

  function shouldShowNewsletter() {
    try {
      var data = JSON.parse(localStorage.getItem(NL_KEY) || '{}');
      if (data.subscribed) return false;
      if (data.dismissed) {
        var daysSince = (Date.now() - data.dismissed) / (1000*60*60*24);
        if (daysSince < NL_DISMISS_DAYS) return false;
      }
      return true;
    } catch(e) { return true; }
  }

  function createNewsletter() {
    var overlay = document.createElement('div');
    overlay.className = 'gn-newsletter-overlay';
    overlay.id = 'gnNewsletter';
    overlay.innerHTML =
      '<div class="gn-newsletter-modal">' +
        '<button class="gn-newsletter-close" onclick="GripFeatures.closeNewsletter()" aria-label="Close">&times;</button>' +
        '<div id="gnNlContent">' +
          '<div class="gn-newsletter-badge">📡 Intel Brief</div>' +
          '<h2>Get gaming intelligence<br>delivered daily</h2>' +
          '<p>Join the GripNews briefing — curated signals, trending games, and impact analysis straight to your inbox. No spam, unsubscribe anytime.</p>' +
          '<div class="gn-newsletter-features">' +
            '<div class="gn-newsletter-feature"><span>✓</span> Daily top signals</div>' +
            '<div class="gn-newsletter-feature"><span>✓</span> Trend alerts</div>' +
            '<div class="gn-newsletter-feature"><span>✓</span> Weekly deep dives</div>' +
          '</div>' +
          '<form class="gn-newsletter-form" onsubmit="return GripFeatures.submitNewsletter(event)">' +
            '<input type="email" class="gn-newsletter-input" id="gnNlEmail" placeholder="your@email.com" required>' +
            '<button type="submit" class="gn-newsletter-submit" id="gnNlSubmit">Subscribe</button>' +
          '</form>' +
          '<div class="gn-newsletter-note">Free · No spam · Unsubscribe anytime</div>' +
        '</div>' +
      '</div>';
    document.body.appendChild(overlay);

    // Close on backdrop click
    overlay.addEventListener('click', function(e) {
      if (e.target === overlay) GripFeatures.closeNewsletter();
    });
    // Close on Escape
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') GripFeatures.closeNewsletter();
    });
  }

  function showNewsletter() {
    var overlay = document.getElementById('gnNewsletter');
    if (!overlay) return;
    // Don't show if cookie banner is still visible
    var cookieBanner = document.getElementById('gnCookieBanner');
    if (cookieBanner && cookieBanner.classList.contains('visible')) {
      setTimeout(showNewsletter, 5000);
      return;
    }
    overlay.classList.add('visible');
  }

  function initNewsletter() {
    if (!shouldShowNewsletter()) return;
    createNewsletter();

    var shown = false;
    function triggerShow() {
      if (shown) return;
      shown = true;
      showNewsletter();
    }

    // Timer trigger
    setTimeout(triggerShow, NL_SHOW_DELAY);

    // Scroll trigger
    window.addEventListener('scroll', function onScroll() {
      var scrolled = window.scrollY / (document.documentElement.scrollHeight - window.innerHeight);
      if (scrolled >= NL_SCROLL_THRESHOLD) {
        window.removeEventListener('scroll', onScroll);
        triggerShow();
      }
    }, { passive: true });
  }

  /* ─── SOCIAL SHARING ─── */
  var SHARE_SVG = {
    x: '<svg viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
    reddit: '<svg viewBox="0 0 24 24"><path d="M12 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0zm5.01 4.744c.688 0 1.25.561 1.25 1.249a1.25 1.25 0 0 1-2.498.056l-2.597-.547-.8 3.747c1.824.07 3.48.632 4.674 1.488.308-.309.73-.491 1.207-.491.968 0 1.754.786 1.754 1.754 0 .716-.435 1.333-1.01 1.614a3.111 3.111 0 0 1 .042.52c0 2.694-3.13 4.87-7.004 4.87-3.874 0-7.004-2.176-7.004-4.87 0-.183.015-.366.043-.534A1.748 1.748 0 0 1 4.028 12c0-.968.786-1.754 1.754-1.754.463 0 .898.196 1.207.49 1.207-.883 2.878-1.43 4.744-1.487l.885-4.182a.342.342 0 0 1 .14-.197.35.35 0 0 1 .238-.042l2.906.617a1.214 1.214 0 0 1 1.108-.701zM9.25 12C8.561 12 8 12.562 8 13.25c0 .687.561 1.248 1.25 1.248.687 0 1.248-.561 1.248-1.249 0-.688-.561-1.249-1.249-1.249zm5.5 0c-.687 0-1.248.561-1.248 1.25 0 .687.561 1.248 1.249 1.248.688 0 1.249-.561 1.249-1.249 0-.687-.562-1.249-1.25-1.249zm-5.466 3.99a.327.327 0 0 0-.231.094.33.33 0 0 0 0 .463c.842.842 2.484.913 2.961.913.477 0 2.105-.056 2.961-.913a.361.361 0 0 0 .029-.463.33.33 0 0 0-.464 0c-.547.533-1.684.73-2.512.73-.828 0-1.979-.196-2.512-.73a.326.326 0 0 0-.232-.095z"/></svg>',
    linkedin: '<svg viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',
    copy: '<svg viewBox="0 0 24 24"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>'
  };

  function addShareBars() {
    var cards = document.querySelectorAll('.signal-card');
    cards.forEach(function(card) {
      if (card.querySelector('.gn-share-bar')) return; // already added

      // Get signal title and link
      var titleEl = card.querySelector('.signal-title a') || card.querySelector('.signal-title');
      var title = titleEl ? titleEl.textContent.trim() : 'Gaming Signal';
      var link = '';
      if (titleEl && titleEl.href) {
        link = titleEl.href;
      } else {
        link = window.location.href;
      }

      var shareText = title + ' — via GripNews';
      var encodedText = encodeURIComponent(shareText);
      var encodedUrl = encodeURIComponent(link);

      var bar = document.createElement('div');
      bar.className = 'gn-share-bar';
      bar.innerHTML =
        '<span class="gn-share-label">Share</span>' +
        '<a class="gn-share-btn gn-share-x" href="https://x.com/intent/tweet?text=' + encodedText + '&url=' + encodedUrl + '" target="_blank" rel="noopener" title="Share on X">' + SHARE_SVG.x + '</a>' +
        '<a class="gn-share-btn gn-share-reddit" href="https://www.reddit.com/submit?url=' + encodedUrl + '&title=' + encodeURIComponent(title) + '" target="_blank" rel="noopener" title="Share on Reddit">' + SHARE_SVG.reddit + '</a>' +
        '<a class="gn-share-btn gn-share-linkedin" href="https://www.linkedin.com/sharing/share-offsite/?url=' + encodedUrl + '" target="_blank" rel="noopener" title="Share on LinkedIn">' + SHARE_SVG.linkedin + '</a>' +
        '<button class="gn-share-btn gn-share-copy" onclick="GripFeatures.copyLink(\'' + link.replace(/'/g, "\\'") + '\', this)" title="Copy link">' + SHARE_SVG.copy + '<span class="gn-share-tooltip" id="gn-tt-' + Math.random().toString(36).substr(2,6) + '">Copied!</span></button>';

      card.appendChild(bar);
    });
  }

  /* ─── PUBLIC API ─── */
  window.GripFeatures = {
    acceptAllCookies: function() {
      var prefs = { analytics: true, marketing: false };
      saveCookieConsent(prefs);
      applyConsent(prefs);
      hideBanner();
    },

    rejectAllCookies: function() {
      var prefs = { analytics: false, marketing: false };
      saveCookieConsent(prefs);
      applyConsent(prefs);
      hideBanner();
    },

    toggleCookiePrefs: function() {
      var panel = document.getElementById('gnCookiePrefs');
      if (panel) panel.classList.toggle('open');
    },

    savePrefs: function() {
      var prefs = {
        analytics: document.getElementById('gnToggleAnalytics').checked,
        marketing: document.getElementById('gnToggleMarketing').checked
      };
      saveCookieConsent(prefs);
      applyConsent(prefs);
      hideBanner();
    },

    showCookieSettings: function() {
      var existing = document.getElementById('gnCookieBanner');
      if (existing) {
        existing.classList.add('visible');
        return;
      }
      createCookieBanner();
      // Pre-fill current preferences
      var consent = getCookieConsent();
      if (consent) {
        var a = document.getElementById('gnToggleAnalytics');
        if (a) a.checked = consent.analytics;
      }
      // Auto-open preferences panel
      setTimeout(function() {
        var panel = document.getElementById('gnCookiePrefs');
        if (panel) panel.classList.add('open');
      }, 400);
    },

    closeNewsletter: function() {
      var overlay = document.getElementById('gnNewsletter');
      if (overlay) {
        overlay.classList.remove('visible');
        localStorage.setItem(NL_KEY, JSON.stringify({ dismissed: Date.now() }));
        setTimeout(function() { overlay.remove(); }, 400);
      }
    },

    submitNewsletter: function(e) {
      e.preventDefault();
      var email = document.getElementById('gnNlEmail').value.trim();
      if (!email) return false;

      var btn = document.getElementById('gnNlSubmit');
      btn.disabled = true;
      btn.textContent = 'Subscribing...';

      // Send to backend
      fetch('/newsletter-signup.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: email, source: 'popup', page: location.pathname })
      })
      .then(function(resp) { return resp.json(); })
      .then(function(data) {
        var content = document.getElementById('gnNlContent');
        content.innerHTML =
          '<div class="gn-newsletter-success">' +
            '<div class="gn-check">✅</div>' +
            '<h2>You\'re in!</h2>' +
            '<p>Welcome to the GripNews briefing.<br>Check your inbox for a confirmation.</p>' +
          '</div>';
        localStorage.setItem(NL_KEY, JSON.stringify({ subscribed: true, email: email }));
        setTimeout(function() { GripFeatures.closeNewsletter(); }, 3000);
      })
      .catch(function() {
        // Still mark as subscribed locally even if backend fails
        var content = document.getElementById('gnNlContent');
        content.innerHTML =
          '<div class="gn-newsletter-success">' +
            '<div class="gn-check">✅</div>' +
            '<h2>You\'re in!</h2>' +
            '<p>Thanks for subscribing to GripNews.</p>' +
          '</div>';
        localStorage.setItem(NL_KEY, JSON.stringify({ subscribed: true, email: email }));
        setTimeout(function() { GripFeatures.closeNewsletter(); }, 3000);
      });

      return false;
    },

    copyLink: function(url, btn) {
      navigator.clipboard.writeText(url).then(function() {
        var tooltip = btn.querySelector('.gn-share-tooltip');
        if (tooltip) {
          tooltip.classList.add('show');
          setTimeout(function() { tooltip.classList.remove('show'); }, 1500);
        }
      });
    }
  };

  /* ─── INIT ─── */
  function init() {
    // Theme toggle
    initTheme();

    // Cookie consent
    var consent = getCookieConsent();
    if (!consent) {
      // Set default denied state before showing banner
      if (window.gtag) {
        gtag('consent', 'default', {
          'analytics_storage': 'denied',
          'ad_storage': 'denied'
        });
      }
      createCookieBanner();
    } else {
      applyConsent(consent);
    }

    // Newsletter (delay init slightly)
    setTimeout(initNewsletter, 2000);

    // Social sharing bars
    addShareBars();

    // Re-add share bars when new content is loaded (for infinite scroll / AJAX)
    var observer = new MutationObserver(function(mutations) {
      var hasNewCards = mutations.some(function(m) {
        return m.addedNodes.length > 0;
      });
      if (hasNewCards) {
        setTimeout(addShareBars, 200);
      }
    });
    var container = document.querySelector('.signals-list') || document.querySelector('main');
    if (container) {
      observer.observe(container, { childList: true, subtree: true });
    }
  }

  // Run on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();