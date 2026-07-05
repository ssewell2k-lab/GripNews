// gg-auth.js — GripAi SSO v0.7.0
// Embedded login modal + plan-based gating.
// Usage: <script src="https://auth.gripnews.uk/js/gg-auth.js"></script>

(function() {
  "use strict";

  var AUTH_URL = "https://auth.gripnews.uk";
  var STORAGE_PREFIX = "gg_";
  var modalEl = null;
  var _onLogin = null;

  // Plan hierarchy (higher index = more access)
  var PLAN_LEVELS = { "free": 0, "studio": 1, "enterprise": 2 };

  // ── Token Receiver (legacy redirect support) ──────────
  function receiveTokens() {
    var hash = window.location.hash;
    if (!hash || hash.indexOf("access_token=") === -1) return false;
    var params = new URLSearchParams(hash.substring(1));
    var at = params.get("access_token");
    var rt = params.get("refresh_token");
    var userStr = params.get("user");
    if (at && rt) {
      localStorage.setItem(STORAGE_PREFIX + "access_token", at);
      localStorage.setItem(STORAGE_PREFIX + "refresh_token", rt);
      if (userStr) try { localStorage.setItem(STORAGE_PREFIX + "user", userStr); } catch(e) {}
      history.replaceState(null, "", window.location.pathname + window.location.search);
      window.dispatchEvent(new CustomEvent("gg:auth", { detail: { type: "login" } }));
      return true;
    }
    return false;
  }

  // ── Modal HTML ──────────────────────────────────────────
  function buildModal() {
    if (modalEl) return modalEl;

    var div = document.createElement("div");
    div.id = "gg-auth-modal";
    div.innerHTML = [
      '<div class="gg-modal-overlay">',
      '  <div class="gg-modal-card">',
      '    <button class="gg-modal-close" id="gg-modal-close">&times;</button>',
      '    <div class="gg-modal-logo">',
      '      <svg width="28" height="28" viewBox="0 0 32 32" fill="none"><circle cx="16" cy="16" r="14" stroke="#00d4ff" stroke-width="2"/><path d="M10 16l4 4 8-8" stroke="#00d4ff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
      '      <span>GripAi Auth</span>',
      '    </div>',
      '    <div id="gg-gate-msg" class="gg-gate-msg"></div>',
      '    <div class="gg-modal-tabs">',
      '      <button class="gg-tab active" data-tab="login">Sign In</button>',
      '      <button class="gg-tab" data-tab="register">Create Account</button>',
      '    </div>',
      '    <div id="gg-modal-error" class="gg-modal-error"></div>',
      '    <div id="gg-modal-success" class="gg-modal-success"></div>',
      '    <form id="gg-login-pane" class="gg-pane active">',
      '      <input type="email" id="gg-login-email" placeholder="Email" required autocomplete="email" />',
      '      <input type="password" id="gg-login-pass" placeholder="Password" required autocomplete="current-password" />',
      '      <input type="hidden" id="gg-login-hp" name="website" value="" />',
      '      <input type="hidden" id="gg-login-ts" value="" />',
      '      <button type="submit" class="gg-modal-btn">Sign In</button>',
      '    </form>',
      '    <form id="gg-register-pane" class="gg-pane">',
      '      <input type="email" id="gg-reg-email" placeholder="Email" required autocomplete="email" />',
      '      <input type="password" id="gg-reg-pass" placeholder="Password (8+ chars)" required autocomplete="new-password" minlength="8" />',
      '      <input type="password" id="gg-reg-pass2" placeholder="Confirm password" required autocomplete="new-password" />',
      '      <input type="hidden" id="gg-reg-hp" name="website" value="" />',
      '      <input type="hidden" id="gg-reg-ts" value="" />',
      '      <button type="submit" class="gg-modal-btn">Create Account</button>',
      '    </form>',
      '    <div class="gg-modal-footer">Secured by GripAi Auth</div>',
      '  </div>',
      '</div>'
    ].join("\n");

    // ── Styles ──
    var style = document.createElement("style");
    style.textContent = [
      "#gg-auth-modal { position:fixed; inset:0; z-index:99999; display:none; }",
      "#gg-auth-modal.open { display:block; }",
      ".gg-modal-overlay { position:absolute; inset:0; background:rgba(0,0,0,0.7); backdrop-filter:blur(4px); display:flex; align-items:center; justify-content:center; }",
      ".gg-modal-card { background:#0f1923; border:1px solid rgba(0,212,255,0.15); border-radius:16px; padding:2rem; width:360px; max-width:92vw; position:relative; box-shadow:0 20px 60px rgba(0,0,0,0.6); font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; color:#e2e8f0; }",
      ".gg-modal-close { position:absolute; top:12px; right:16px; background:none; border:none; color:#64748b; font-size:1.5rem; cursor:pointer; line-height:1; padding:4px; }",
      ".gg-modal-close:hover { color:#e2e8f0; }",
      ".gg-modal-logo { display:flex; align-items:center; gap:8px; margin-bottom:1.25rem; font-size:1rem; font-weight:600; color:#00d4ff; }",
      ".gg-gate-msg { display:none; padding:10px 14px; background:rgba(0,212,255,0.08); border:1px solid rgba(0,212,255,0.2); border-radius:8px; color:#7dd3fc; font-size:0.82rem; margin-bottom:12px; line-height:1.4; }",
      ".gg-gate-msg.show { display:block; }",
      ".gg-modal-tabs { display:flex; gap:0; margin-bottom:1rem; border-bottom:1px solid rgba(255,255,255,0.08); }",
      ".gg-tab { flex:1; background:none; border:none; border-bottom:2px solid transparent; color:#64748b; padding:8px 0; cursor:pointer; font-size:0.85rem; font-family:inherit; transition:all 0.2s; }",
      ".gg-tab.active { color:#00d4ff; border-bottom-color:#00d4ff; }",
      ".gg-tab:hover { color:#94a3b8; }",
      ".gg-pane { display:none; }",
      ".gg-pane.active { display:block; }",
      ".gg-pane input[type=email], .gg-pane input[type=password] { display:block; width:100%; padding:10px 12px; margin-bottom:10px; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.1); border-radius:8px; color:#e2e8f0; font-size:0.9rem; font-family:inherit; outline:none; box-sizing:border-box; }",
      ".gg-pane input:focus { border-color:rgba(0,212,255,0.4); box-shadow:0 0 0 2px rgba(0,212,255,0.1); }",
      ".gg-modal-btn { display:block; width:100%; padding:10px; background:linear-gradient(135deg,#00d4ff,#0099cc); border:none; border-radius:8px; color:#fff; font-size:0.9rem; font-weight:600; cursor:pointer; font-family:inherit; margin-top:4px; transition:opacity 0.2s; }",
      ".gg-modal-btn:hover { opacity:0.9; }",
      ".gg-modal-btn:disabled { opacity:0.5; cursor:wait; }",
      ".gg-modal-error { display:none; padding:8px 12px; background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); border-radius:8px; color:#f87171; font-size:0.8rem; margin-bottom:10px; }",
      ".gg-modal-error.show { display:block; }",
      ".gg-modal-success { display:none; padding:8px 12px; background:rgba(34,197,94,0.1); border:1px solid rgba(34,197,94,0.3); border-radius:8px; color:#4ade80; font-size:0.8rem; margin-bottom:10px; }",
      ".gg-modal-success.show { display:block; }",
      ".gg-modal-footer { text-align:center; margin-top:1rem; font-size:0.7rem; color:#475569; }",
      // Paywall overlay
      "#gg-paywall-overlay { position:fixed; inset:0; z-index:99998; background:rgba(10,15,26,0.92); backdrop-filter:blur(8px); display:flex; align-items:center; justify-content:center; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; }",
      ".gg-paywall-card { background:#0f1923; border:1px solid rgba(255,215,0,0.2); border-radius:16px; padding:2.5rem; width:420px; max-width:92vw; text-align:center; color:#e2e8f0; box-shadow:0 20px 60px rgba(0,0,0,0.6); }",
      ".gg-paywall-card h2 { margin:0 0 0.5rem; font-size:1.3rem; color:#FFD700; }",
      ".gg-paywall-card p { color:#94a3b8; font-size:0.9rem; line-height:1.5; margin-bottom:1.5rem; }",
      ".gg-paywall-btn { display:inline-block; padding:12px 28px; background:linear-gradient(135deg,#FFD700,#FFA500); border:none; border-radius:8px; color:#0a0f1a; font-size:0.95rem; font-weight:700; cursor:pointer; text-decoration:none; transition:opacity 0.2s; }",
      ".gg-paywall-btn:hover { opacity:0.9; }",
      ".gg-paywall-back { display:block; margin-top:1rem; color:#64748b; font-size:0.8rem; text-decoration:none; }",
      ".gg-paywall-back:hover { color:#94a3b8; }",
    ].join("\n");

    document.head.appendChild(style);
    document.body.appendChild(div);
    modalEl = div;

    // ── Events ──
    div.querySelector("#gg-modal-close").onclick = closeModal;
    div.querySelector(".gg-modal-overlay").addEventListener("click", function(e) {
      if (e.target === e.currentTarget) closeModal();
    });

    // Tab switching
    div.querySelectorAll(".gg-tab").forEach(function(tab) {
      tab.onclick = function() {
        div.querySelectorAll(".gg-tab").forEach(function(t) { t.classList.remove("active"); });
        div.querySelectorAll(".gg-pane").forEach(function(p) { p.classList.remove("active"); });
        tab.classList.add("active");
        div.querySelector("#gg-" + tab.dataset.tab + "-pane").classList.add("active");
        hideMsg();
        setTimestamps();
      };
    });

    // Login submit
    div.querySelector("#gg-login-pane").onsubmit = function(e) {
      e.preventDefault();
      hideMsg();
      var email = div.querySelector("#gg-login-email").value.trim();
      var pass = div.querySelector("#gg-login-pass").value;
      var btn = this.querySelector(".gg-modal-btn");
      btn.disabled = true;
      btn.textContent = "Signing in...";

      authRequest("/auth/login", {
        email: email,
        password: pass,
        website: div.querySelector("#gg-login-hp").value,
        form_started_at: Number(div.querySelector("#gg-login-ts").value),
        captcha_token: ""
      }).then(function(data) {
        saveTokens(data);
        closeModal();
        window.dispatchEvent(new CustomEvent("gg:auth", { detail: { type: "login" } }));
        if (_onLogin) _onLogin(data);
      }).catch(function(err) {
        showError(err.message);
      }).finally(function() {
        btn.disabled = false;
        btn.textContent = "Sign In";
      });
    };

    // Register submit
    div.querySelector("#gg-register-pane").onsubmit = function(e) {
      e.preventDefault();
      hideMsg();
      var email = div.querySelector("#gg-reg-email").value.trim();
      var pass = div.querySelector("#gg-reg-pass").value;
      var pass2 = div.querySelector("#gg-reg-pass2").value;

      if (pass !== pass2) { showError("Passwords do not match"); return; }
      if (pass.length < 8) { showError("Password must be at least 8 characters"); return; }

      var btn = this.querySelector(".gg-modal-btn");
      btn.disabled = true;
      btn.textContent = "Creating account...";

      authRequest("/auth/register", {
        email: email,
        password: pass,
        website: div.querySelector("#gg-reg-hp").value,
        form_started_at: Number(div.querySelector("#gg-reg-ts").value),
        captcha_token: ""
      }).then(function(data) {
        saveTokens(data);
        showSuccess("Account created!");
        setTimeout(function() {
          closeModal();
          window.dispatchEvent(new CustomEvent("gg:auth", { detail: { type: "login" } }));
          if (_onLogin) _onLogin(data);
        }, 600);
      }).catch(function(err) {
        showError(err.message);
      }).finally(function() {
        btn.disabled = false;
        btn.textContent = "Create Account";
      });
    };

    return div;
  }

  function setTimestamps() {
    var now = Date.now();
    var el1 = document.querySelector("#gg-login-ts");
    var el2 = document.querySelector("#gg-reg-ts");
    if (el1) el1.value = now;
    if (el2) el2.value = now;
  }

  function openModal(onLogin, gateMsg) {
    _onLogin = onLogin || null;
    buildModal();
    setTimestamps();
    hideMsg();
    // Reset forms
    var inputs = modalEl.querySelectorAll("input[type=email], input[type=password]");
    inputs.forEach(function(i) { i.value = ""; });
    // Show gate message if provided
    var gm = modalEl.querySelector("#gg-gate-msg");
    if (gm) {
      if (gateMsg) { gm.textContent = gateMsg; gm.classList.add("show"); }
      else { gm.classList.remove("show"); }
    }
    modalEl.classList.add("open");
  }

  function closeModal() {
    if (modalEl) modalEl.classList.remove("open");
  }

  function showError(msg) {
    var el = document.querySelector("#gg-modal-error");
    if (el) { el.textContent = msg; el.classList.add("show"); }
  }

  function showSuccess(msg) {
    var el = document.querySelector("#gg-modal-success");
    if (el) { el.textContent = msg; el.classList.add("show"); }
  }

  function hideMsg() {
    var e = document.querySelector("#gg-modal-error");
    var s = document.querySelector("#gg-modal-success");
    if (e) e.classList.remove("show");
    if (s) s.classList.remove("show");
  }

  // ── Paywall Overlay ────────────────────────────────────
  function showPaywall(opts) {
    opts = opts || {};
    var existing = document.getElementById("gg-paywall-overlay");
    if (existing) existing.remove();

    var overlay = document.createElement("div");
    overlay.id = "gg-paywall-overlay";
    overlay.innerHTML = [
      '<div class="gg-paywall-card">',
      '  <h2>' + (opts.title || 'Upgrade to Access') + '</h2>',
      '  <p>' + (opts.message || 'This feature requires a paid plan. Upgrade to unlock full access to JaffaAi intelligence tools.') + '</p>',
      '  <a class="gg-paywall-btn" href="' + (opts.upgradeUrl || 'https://gripai.uk/#pricing') + '">Upgrade Now</a>',
      '  &larr; Back to JaffaAi',
      '</div>'
    ].join("\n");

    document.body.appendChild(overlay);
  }

  // ── API helper ──────────────────────────────────────────
  function authRequest(path, body) {
    return fetch(AUTH_URL + path, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(body)
    }).then(function(res) {
      return res.json().then(function(data) {
        if (!res.ok) throw new Error(data.error || "Request failed");
        return data;
      });
    });
  }

  function saveTokens(data) {
    if (data.accessToken) localStorage.setItem(STORAGE_PREFIX + "access_token", data.accessToken);
    if (data.refreshToken) localStorage.setItem(STORAGE_PREFIX + "refresh_token", data.refreshToken);
    if (data.user) localStorage.setItem(STORAGE_PREFIX + "user", JSON.stringify(data.user));
  }

  function decodeToken(token) {
    try { return JSON.parse(atob(token.split(".")[1])); }
    catch(e) { return null; }
  }

  // ── Public API ──────────────────────────────────────────
  window.GGAuth = {
    AUTH_URL: AUTH_URL,
    VERSION: "0.7.0",

    getAccessToken: function() {
      return localStorage.getItem(STORAGE_PREFIX + "access_token");
    },
    getRefreshToken: function() {
      return localStorage.getItem(STORAGE_PREFIX + "refresh_token");
    },
    getUser: function() {
      try { return JSON.parse(localStorage.getItem(STORAGE_PREFIX + "user")); }
      catch(e) { return null; }
    },
    isLoggedIn: function() {
      var token = this.getAccessToken();
      if (!token) return false;
      var payload = decodeToken(token);
      if (!payload) return false;
      return payload.exp * 1000 > Date.now();
    },

    // Get plan from JWT (fast, no network call)
    getPlan: function() {
      var token = this.getAccessToken();
      if (!token) return null;
      var payload = decodeToken(token);
      return payload ? (payload.plan || "free") : null;
    },

    // Check if user plan meets minimum required level
    hasPlan: function(minPlan) {
      var current = this.getPlan();
      if (!current) return false;
      var currentLevel = PLAN_LEVELS[current] || 0;
      var requiredLevel = PLAN_LEVELS[minPlan] || 0;
      return currentLevel >= requiredLevel;
    },

    // Gate: requires login (free account OK). Shows modal if not logged in.
    // Returns true if already logged in, false if modal was shown.
    requireAuth: function(opts) {
      opts = opts || {};
      if (this.isLoggedIn()) return true;
      // Try refresh first
      var self = this;
      this.refresh().then(function() {
        if (opts.onSuccess) opts.onSuccess();
        else window.location.reload();
      }).catch(function() {
        openModal(function(data) {
          if (opts.onSuccess) opts.onSuccess(data);
          else window.location.reload();
        }, opts.message || "Sign in to access this page.");
      });
      return false;
    },

    // Gate: requires paid plan. Shows login if not authed, paywall if wrong plan.
    requirePlan: function(minPlan, opts) {
      opts = opts || {};
      var self = this;
      if (this.isLoggedIn()) {
        if (this.hasPlan(minPlan)) return true;
        // Logged in but wrong plan — show paywall
        showPaywall({
          title: opts.paywallTitle || "Pro Feature",
          message: opts.paywallMessage || "This page requires a " + minPlan + " plan or above.",
          upgradeUrl: opts.upgradeUrl
        });
        return false;
      }
      // Not logged in — try refresh, then modal
      this.refresh().then(function() {
        if (self.hasPlan(minPlan)) {
          if (opts.onSuccess) opts.onSuccess();
          else window.location.reload();
        } else {
          showPaywall({
            title: opts.paywallTitle,
            message: opts.paywallMessage,
            upgradeUrl: opts.upgradeUrl
          });
        }
      }).catch(function() {
        openModal(function(data) {
          // After login, check plan
          if (self.hasPlan(minPlan)) {
            if (opts.onSuccess) opts.onSuccess(data);
            else window.location.reload();
          } else {
            closeModal();
            showPaywall({
              title: opts.paywallTitle,
              message: opts.paywallMessage,
              upgradeUrl: opts.upgradeUrl
            });
          }
        }, opts.message || "Sign in to continue.");
      });
      return false;
    },

    // Opens login modal — no redirect, no page navigation
    login: function(callback) {
      openModal(callback);
    },

    logout: function() {
      var rt = localStorage.getItem(STORAGE_PREFIX + "refresh_token");
      if (rt) {
        fetch(AUTH_URL + "/auth/logout", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ refreshToken: rt }),
        }).catch(function() {});
      }
      localStorage.removeItem(STORAGE_PREFIX + "access_token");
      localStorage.removeItem(STORAGE_PREFIX + "refresh_token");
      localStorage.removeItem(STORAGE_PREFIX + "user");
      window.dispatchEvent(new CustomEvent("gg:auth", { detail: { type: "logout" } }));
    },

    refresh: function() {
      var rt = localStorage.getItem(STORAGE_PREFIX + "refresh_token");
      if (!rt) return Promise.reject(new Error("No refresh token"));
      return fetch(AUTH_URL + "/auth/refresh", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ refreshToken: rt }),
      }).then(function(res) { return res.json(); })
        .then(function(data) {
          if (data.accessToken) {
            localStorage.setItem(STORAGE_PREFIX + "access_token", data.accessToken);
            localStorage.setItem(STORAGE_PREFIX + "refresh_token", data.refreshToken);
            if (data.user) localStorage.setItem(STORAGE_PREFIX + "user", JSON.stringify(data.user));
            return data.accessToken;
          }
          throw new Error(data.error || "Refresh failed");
        });
    },

    fetch: function(url, options) {
      var self = this;
      options = options || {};
      options.headers = options.headers || {};
      options.headers["Authorization"] = "Bearer " + this.getAccessToken();
      return fetch(url, options).then(function(res) {
        if (res.status === 401) {
          return self.refresh().then(function(newToken) {
            options.headers["Authorization"] = "Bearer " + newToken;
            return fetch(url, options);
          });
        }
        return res;
      });
    },
  };

  // Auto-receive tokens from legacy redirect flow
  receiveTokens();

})();
