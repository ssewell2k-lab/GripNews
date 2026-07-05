// public/js/portal.js — GripAi Auth Portal Frontend v0.7.0
// GripCaptcha integration — Google reCAPTCHA removed
// SSO redirect flow: ?redirect=https://gripai.uk

const AUTH_BASE = window.location.origin;

// ── Allowed redirect origins (must match ALLOWED_ORIGINS in .env) ──
const ALLOWED_REDIRECT_ORIGINS = [
  "https://jaffaai.co.uk",
  "https://gripai.uk",
  "https://api.gripai.uk",
  "https://auth.gripnews.uk",
  "https://gripai.uk",
  "https://gripai.uk",
  "https://gripai.uk",
  "https://www.gripai.uk",
  "https://gripai.uk",
  "https://www.gripai.uk",
];

// ── State ─────────────────────────────────────────────────
let currentUser = null;
let accessToken = localStorage.getItem("gg_access_token");
let refreshToken = localStorage.getItem("gg_refresh_token");
let tokenVisible = false;
let tokenExpiryTimer = null;
let redirectTarget = null; // Where to send user after login

// ── Parse redirect param ──────────────────────────────────
function parseRedirect() {
  const params = new URLSearchParams(window.location.search);
  const redirect = params.get("redirect") || params.get("return_to");
  if (!redirect) return null;

  try {
    const url = new URL(redirect);
    const origin = url.origin;
    if (ALLOWED_REDIRECT_ORIGINS.includes(origin)) {
      return redirect;
    }
    console.warn("Redirect blocked — origin not in allowlist:", origin);
    return null;
  } catch (e) {
    return null;
  }
}

// ── Redirect back to calling service ──────────────────────
function redirectBack(tokens) {
  if (!redirectTarget) return false;

  try {
    const url = new URL(redirectTarget);
    // Pass tokens via URL fragment (not query) — avoids server-side logging
    const fragment = `access_token=${encodeURIComponent(tokens.accessToken)}&refresh_token=${encodeURIComponent(tokens.refreshToken)}&user=${encodeURIComponent(JSON.stringify(tokens.user))}`;
    url.hash = fragment;
    window.location.href = url.toString();
    return true;
  } catch (e) {
    console.error("Redirect failed:", e);
    return false;
  }
}

// ── DOM Helpers ───────────────────────────────────────────
function $(sel) { return document.querySelector(sel); }
function $$(sel) { return document.querySelectorAll(sel); }

function showError(id, msg) {
  const el = $(id);
  el.textContent = msg;
  el.classList.add("visible");
}

function hideError(id) {
  $(id).classList.remove("visible");
}

function showSuccess(id, msg) {
  const el = $(id);
  el.textContent = msg;
  el.classList.add("visible");
}

function hideSuccess(id) {
  $(id).classList.remove("visible");
}

function setLoading(btn, loading) {
  const text = btn.querySelector(".btn-text");
  const loader = btn.querySelector(".btn-loader");
  text.style.display = loading ? "none" : "inline";
  loader.style.display = loading ? "inline" : "none";
  btn.disabled = loading;
}

// ── API ───────────────────────────────────────────────────
async function api(path, options = {}) {
  const url = `${AUTH_BASE}${path}`;
  const res = await fetch(url, {
    ...options,
    headers: {
      "Content-Type": "application/json",
      ...options.headers,
    },
  });
  const body = await res.json().catch(() => ({}));
  return { status: res.status, body };
}

async function apiAuth(path, options = {}) {
  const url = `${AUTH_BASE}${path}`;
  const res = await fetch(url, {
    ...options,
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${accessToken}`,
      ...options.headers,
    },
  });
  const body = await res.json().catch(() => ({}));
  return { status: res.status, body };
}

// ── Auth Actions ──────────────────────────────────────────

async function login(email, password) {
  const website = $("#login-honeypot") ? $("#login-honeypot").value : "";
  const form_started_at = $("#login-form-started") ? Number($("#login-form-started").value) : Date.now();
  const captcha_token = (window._loginGripCaptcha && window._loginGripCaptcha.isVerified()) ? window._loginGripCaptcha.getResponse() : "";
  const { status, body } = await api("/auth/login", {
    method: "POST",
    body: JSON.stringify({ email, password, website, form_started_at, captcha_token }),
  });

  if (status !== 200) {
    throw new Error(body.error || "Login failed");
  }

  accessToken = body.accessToken;
  refreshToken = body.refreshToken;
  currentUser = body.user;
  saveSession();
  return body;
}

async function register(email, password) {
  const website = $("#register-honeypot") ? $("#register-honeypot").value : "";
  const form_started_at = $("#register-form-started") ? Number($("#register-form-started").value) : Date.now();
  const captcha_token = (window._registerGripCaptcha && window._registerGripCaptcha.isVerified()) ? window._registerGripCaptcha.getResponse() : "";
  const { status, body } = await api("/auth/register", {
    method: "POST",
    body: JSON.stringify({ email, password, website, form_started_at, captcha_token }),
  });

  if (status !== 201) {
    throw new Error(body.error || "Registration failed");
  }

  accessToken = body.accessToken;
  refreshToken = body.refreshToken;
  currentUser = body.user;
  saveSession();
  return body;
}

async function refreshAccessToken() {
  if (!refreshToken) return null;

  const { status, body } = await api("/auth/refresh", {
    method: "POST",
    body: JSON.stringify({ refreshToken }),
  });

  if (status !== 200) {
    clearSession();
    return null;
  }

  accessToken = body.accessToken;
  refreshToken = body.refreshToken;
  saveSession();
  return accessToken;
}

async function logout() {
  if (refreshToken) {
    await api("/auth/logout", {
      method: "POST",
      body: JSON.stringify({ refreshToken }),
    }).catch(() => {});
  }
  clearSession();
}

function saveSession() {
  localStorage.setItem("gg_access_token", accessToken);
  localStorage.setItem("gg_refresh_token", refreshToken);
  localStorage.setItem("gg_user", JSON.stringify(currentUser));
}

function clearSession() {
  accessToken = null;
  refreshToken = null;
  currentUser = null;
  localStorage.removeItem("gg_access_token");
  localStorage.removeItem("gg_refresh_token");
  localStorage.removeItem("gg_user");
  if (tokenExpiryTimer) clearInterval(tokenExpiryTimer);
}

// ── UI State ──────────────────────────────────────────────

function showRedirectBanner() {
  // Show a banner when user arrived via redirect
  if (!redirectTarget) return;
  try {
    const origin = new URL(redirectTarget).hostname;
    const banner = document.createElement("div");
    banner.className = "redirect-banner";
    banner.innerHTML = '<span class="redirect-icon">↩</span> Sign in to continue to <strong>' + origin + '</strong>';
    const card = $(".auth-card");
    if (card) card.prepend(banner);
  } catch (e) {}
}

function showAuthForms() {
  $("#login-form").classList.add("active");
  $("#register-form").classList.remove("active");
  $("#dashboard").style.display = "none";
  $$(".auth-tabs .tab-btn").forEach(b => b.style.display = "");
  $(".auth-tabs").style.display = "flex";
  showRedirectBanner();
}

function showDashboard() {
  $("#login-form").classList.remove("active");
  $("#register-form").classList.remove("active");
  $(".auth-tabs").style.display = "none";
  $("#dashboard").style.display = "block";

  // Populate user info
  $("#dashboard-email").textContent = currentUser.email;
  $("#user-initial").textContent = currentUser.email[0].toUpperCase();

  updateTokenDisplay();
  startTokenExpiryTimer();
  loadSessions();
  loadApiKeyInfo();
}

// Handle post-login: redirect or show dashboard
function onAuthSuccess(tokens) {
  // Try to redirect back to calling service
  if (redirectBack(tokens)) return; // will navigate away

  // No redirect — show local dashboard
  showDashboard();
}

function updateTokenDisplay() {
  const preview = $("#token-preview");
  if (tokenVisible && accessToken) {
    preview.textContent = accessToken;
  } else {
    preview.textContent = "\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022";
  }
}

function startTokenExpiryTimer() {
  if (tokenExpiryTimer) clearInterval(tokenExpiryTimer);

  function update() {
    if (!accessToken) return;
    try {
      const payload = JSON.parse(atob(accessToken.split(".")[1]));
      const exp = payload.exp * 1000;
      const now = Date.now();
      const remaining = Math.max(0, exp - now);
      const mins = Math.floor(remaining / 60000);
      const secs = Math.floor((remaining % 60000) / 1000);
      $("#token-expiry").textContent = `Expires in ${mins}m ${secs}s`;

      if (remaining < 60000 && remaining > 0) {
        $("#token-expiry").style.color = "var(--warning)";
      } else {
        $("#token-expiry").style.color = "var(--text-muted)";
      }
    } catch (e) {
      $("#token-expiry").textContent = "Invalid token";
    }
  }

  update();
  tokenExpiryTimer = setInterval(update, 1000);
}

async function loadSessions() {
  const list = $("#sessions-list");
  list.innerHTML = '<div class="session-item loading">Loading sessions...</div>';

  const { status, body } = await apiAuth("/user/sessions");

  if (status !== 200) {
    list.innerHTML = '<div class="session-item">Failed to load sessions</div>';
    return;
  }

  if (!body.sessions || body.sessions.length === 0) {
    list.innerHTML = '<div class="session-item">No active sessions</div>';
    return;
  }

  list.innerHTML = body.sessions.map(s => {
    const date = new Date(s.created_at).toLocaleDateString();
    const statusClass = s.revoked ? "revoked" : "active";
    const statusText = s.revoked ? "Revoked" : "Active";
    return `
      <div class="session-item" data-id="${s.id}">
        <div class="session-info">
          <span class="session-id">Session #${s.id}</span>
          <span class="session-date">${date}</span>
        </div>
        <span class="session-status ${statusClass}">${statusText}</span>
      </div>
    `;
  }).join("");
}

// ── Event Handlers ────────────────────────────────────────

// Tab switching
$$(".tab-btn").forEach(btn => {
  btn.addEventListener("click", () => {
    const tab = btn.dataset.tab;
    $$(".tab-btn").forEach(b => b.classList.remove("active"));
    btn.classList.add("active");
    $$(".auth-form").forEach(f => f.classList.remove("active"));
    $(`#${tab}-form`).classList.add("active");
    hideError("#login-error");
    hideError("#register-error");
    hideSuccess("#register-success");
  });
});

// Login form
$("#login-form").addEventListener("submit", async (e) => {
  e.preventDefault();
  hideError("#login-error");

  const email = $("#login-email").value.trim();
  const password = $("#login-password").value;
  const btn = $("#login-form .btn-primary");

  setLoading(btn, true);
  try {
    const tokens = await login(email, password);
    onAuthSuccess(tokens);
  } catch (err) {
    showError("#login-error", err.message);
    if (window._loginGripCaptcha) { window._loginGripCaptcha.reset(); }
    $("#login-captcha-token").value = "";
  } finally {
    setLoading(btn, false);
  }
});

// Register form
$("#register-form").addEventListener("submit", async (e) => {
  e.preventDefault();
  hideError("#register-error");
  hideSuccess("#register-success");

  const email = $("#register-email").value.trim();
  const password = $("#register-password").value;
  const confirm = $("#register-password-confirm").value;
  const btn = $("#register-form .btn-primary");

  if (password !== confirm) {
    showError("#register-error", "Passwords do not match");
    return;
  }

  if (password.length < 8) {
    showError("#register-error", "Password must be at least 8 characters");
    return;
  }

  setLoading(btn, true);
  try {
    const tokens = await register(email, password);
    showSuccess("#register-success", "Account created! Redirecting...");
    setTimeout(() => onAuthSuccess(tokens), 800);
  } catch (err) {
    showError("#register-error", err.message);
    if (window._registerGripCaptcha) { window._registerGripCaptcha.reset(); }
    $("#register-captcha-token").value = "";
  } finally {
    setLoading(btn, false);
  }
});

// Password strength indicator
$("#register-password").addEventListener("input", (e) => {
  const val = e.target.value;
  const indicator = $("#password-strength");
  indicator.classList.remove("weak", "medium", "strong");

  if (val.length === 0) return;

  let score = 0;
  if (val.length >= 8) score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;

  if (score <= 1) indicator.classList.add("weak");
  else if (score <= 2) indicator.classList.add("medium");
  else indicator.classList.add("strong");
});

// Dashboard actions
$("#btn-show-token").addEventListener("click", () => {
  tokenVisible = !tokenVisible;
  updateTokenDisplay();
});

$("#btn-copy-token").addEventListener("click", async () => {
  if (!accessToken) return;
  try {
    await navigator.clipboard.writeText(accessToken);
    const btn = $("#btn-copy-token");
    const original = btn.textContent;
    btn.textContent = "\u2713";
    setTimeout(() => btn.textContent = original, 1500);
  } catch (e) {
    console.error("Copy failed", e);
  }
});

$("#btn-refresh").addEventListener("click", async () => {
  const btn = $("#btn-refresh");
  btn.textContent = "Refreshing...";
  btn.disabled = true;
  try {
    await refreshAccessToken();
    updateTokenDisplay();
    startTokenExpiryTimer();
    btn.textContent = "Refreshed!";
    setTimeout(() => { btn.textContent = "Refresh now"; btn.disabled = false; }, 2000);
  } catch (e) {
    btn.textContent = "Failed";
    setTimeout(() => { btn.textContent = "Refresh now"; btn.disabled = false; }, 2000);
  }
});

$("#btn-logout").addEventListener("click", async () => {
  await logout();
  showAuthForms();
});

$("#btn-logout-all").addEventListener("click", async () => {
  await logout();
  showAuthForms();
});

// ── Health Check ──────────────────────────────────────────

async function checkHealth() {
  try {
    const { status, body } = await api("/health");
    const dot = $("#service-status");
    const text = $("#service-status-text");

    if (status === 200 && body.status === "healthy") {
      dot.classList.add("online");
      dot.classList.remove("offline");
      text.textContent = `Online — v${body.version || "0.6.2"}`;
    } else {
      dot.classList.add("offline");
      dot.classList.remove("online");
      text.textContent = "Degraded";
    }
  } catch (e) {
    $("#service-status").classList.add("offline");
    $("#service-status-text").textContent = "Offline";
  }
}



// ── API Key Management ────────────────────────────────────
const GRIPAI_API_BASE = "https://gripai.uk/safety-api";

async function loadApiKeyInfo() {
  const container = document.getElementById("api-key-status");
  if (!container || !currentUser || !currentUser.email) return;

  container.innerHTML = '<div style="color:var(--text-muted);font-size:0.8rem;">Checking API key...</div>';

  try {
    const res = await fetch(GRIPAI_API_BASE + "/v1/account?email=" + encodeURIComponent(currentUser.email));
    const data = await res.json();

    if (data.has_key) {
      const usagePct = data.monthly_limit > 0 ? Math.min(100, Math.round((data.usage_count / data.monthly_limit) * 100)) : 0;
      const limitStr = data.monthly_limit >= 1000000 ? (data.monthly_limit / 1000000) + "M"
                      : data.monthly_limit >= 1000 ? (data.monthly_limit / 1000) + "K"
                      : data.monthly_limit.toString();

      container.innerHTML = '<div class="api-key-info">' +
        '<div class="api-key-row"><span class="label">Key</span><span class="value green">' + data.api_key_prefix + '••••••••</span></div>' +
        '<div class="api-key-row"><span class="label">Plan</span><span class="value purple">' + data.plan.toUpperCase() + '</span></div>' +
        '<div class="api-key-row"><span class="label">Usage</span><span class="value">' + data.usage_count.toLocaleString() + ' / ' + limitStr + '</span></div>' +
        '<div class="usage-bar-bg"><div class="usage-bar-fill" style="width:' + usagePct + '%"></div></div>' +
        '<div class="api-badges">' +
          '<span class="api-badge-sm game">🎮 Game API</span>' +
          '<span class="api-badge-sm safety">🛡️ Safety API</span>' +
        '</div>' +
        '<div style="display:flex;gap:0.5rem;margin-top:0.5rem;">' +
          '<a href="https://gripai.uk/safety-api/docs" class="btn-upgrade" target="_blank">📖 API Docs</a>' +
          '<a href="https://gripai.uk#pricing" class="btn-upgrade" target="_blank">⬆️ Upgrade</a>' +
        '</div>' +
      '</div>';
    } else {
      container.innerHTML = '<div class="api-key-info">' +
        '<div style="font-size:0.85rem;color:var(--text-muted);margin-bottom:0.75rem;">No API key yet. Generate a free key to access both the Game API and Safety API.</div>' +
        '<button class="btn-generate-key" onclick="generateFreeKey()">🔑 Generate Free API Key</button>' +
      '</div>';
    }
  } catch (err) {
    console.error("API key lookup error:", err);
    container.innerHTML = '<div style="color:var(--text-muted);font-size:0.8rem;">Unable to load API key info.</div>';
  }
}

async function generateFreeKey() {
  const btn = document.querySelector(".btn-generate-key");
  if (!btn || !currentUser || !currentUser.email) return;

  btn.textContent = "Generating...";
  btn.disabled = true;

  try {
    const res = await fetch(GRIPAI_API_BASE + "/v1/keys/generate", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ email: currentUser.email })
    });
    const data = await res.json();

    if (data.api_key) {
      const container = document.getElementById("api-key-status");
      container.innerHTML = '<div class="api-key-info">' +
        '<div style="text-align:center;margin-bottom:0.75rem;">' +
          '<div style="font-size:1.5rem;margin-bottom:0.25rem;">🎉</div>' +
          '<div style="font-weight:700;">Key Generated!</div>' +
        '</div>' +
        '<div style="background:rgba(0,0,0,0.3);border-radius:8px;padding:0.75rem;border:1px solid rgba(0,210,106,0.2);">' +
          '<div style="font-size:0.7rem;text-transform:uppercase;color:var(--text-muted);margin-bottom:0.3rem;">Your API Key</div>' +
          '<code id="new-api-key" style="font-family:Fira Code,monospace;font-size:0.8rem;color:#00D26A;word-break:break-all;display:block;">' + data.api_key + '</code>' +
        '</div>' +
        '<button onclick="copyNewKey()" style="width:100%;margin-top:0.5rem;padding:0.5rem;background:none;border:1px solid rgba(255,255,255,0.12);border-radius:6px;color:#fff;font-size:0.8rem;cursor:pointer;">📋 Copy Key</button>' +
        '<div style="font-size:0.75rem;color:#ff4757;margin-top:0.5rem;text-align:center;">⚠️ Save this key now — it will only be shown once.</div>' +
        '<div class="api-badges" style="justify-content:center;">' +
          '<span class="api-badge-sm game">🎮 Game API</span>' +
          '<span class="api-badge-sm safety">🛡️ Safety API</span>' +
        '</div>' +
        '<div style="text-align:center;margin-top:0.75rem;">' +
          '<div style="font-size:0.8rem;color:var(--text-muted);">Plan: <strong style="color:#845ef7;">INDIE</strong> · 5K calls/month</div>' +
          '<a href="https://gripai.uk#pricing" class="btn-upgrade" target="_blank">⬆️ Need more? Upgrade</a>' +
        '</div>' +
      '</div>';
    } else {
      alert(data.error || "Failed to generate key.");
      btn.textContent = "🔑 Generate Free API Key";
      btn.disabled = false;
    }
  } catch (err) {
    alert("Network error. Please try again.");
    btn.textContent = "🔑 Generate Free API Key";
    btn.disabled = false;
  }
}

function copyNewKey() {
  const el = document.getElementById("new-api-key");
  if (!el) return;
  navigator.clipboard.writeText(el.textContent).then(function() {
    var btn = event.target;
    btn.textContent = "✓ Copied!";
    setTimeout(function() { btn.textContent = "📋 Copy Key"; }, 2000);
  });
}

// ── Init ──────────────────────────────────────────────────

function init() {
  // Parse redirect target from URL
  redirectTarget = parseRedirect();

  // Check for existing session
  const savedUser = localStorage.getItem("gg_user");
  if (accessToken && savedUser) {
    try {
      currentUser = JSON.parse(savedUser);

      // If we have a valid session AND a redirect target, go straight back
      if (redirectTarget) {
        redirectBack({ accessToken, refreshToken, user: currentUser });
        return; // navigating away
      }

      showDashboard();
    } catch (e) {
      clearSession();
      showAuthForms();
    }
  } else {
    showAuthForms();
  }

  checkHealth();
  setInterval(checkHealth, 30000);
}

init();

// ── Anti-abuse: set form timing on tab switch and page load ──
function initFormTiming() {
  if ($("#login-form-started")) $("#login-form-started").value = Date.now();
  if ($("#register-form-started")) $("#register-form-started").value = Date.now();
}
$$(".auth-tabs .tab-btn").forEach(btn => {
  btn.addEventListener("click", () => setTimeout(initFormTiming, 50));
});
initFormTiming();

// ── GripCaptcha Integration ────────────────────────────────────
const GRIPCAPTCHA_SITE_KEY = "live_FE8DBBEE1C4574EEDE62196673BDCF2E6B06E3A364B7DA1C";

function initGripCaptcha() {
  if (typeof GripCaptcha === "undefined") {
    // GripCaptcha not loaded yet, retry
    setTimeout(initGripCaptcha, 200);
    return;
  }

  // Initialize login captcha
  try {
    window._loginGripCaptcha = new GripCaptcha("login-gripcaptcha", {
      apiKey: GRIPCAPTCHA_SITE_KEY,
      theme: "dark",
      challengeType: "auto",
      onSuccess: function(data) {
        var el = document.querySelector("#login-captcha-token");
        if (el) el.value = JSON.stringify({ token: data.validation_token, signature: data.validation_signature, expires_at: data.expires_at });
      },
      onError: function(err) {
        console.error("[GripCaptcha] Login error:", err);
      }
    });
  } catch(e) {
    console.error("[GripCaptcha] Login init error:", e);
  }

  // Initialize register captcha
  try {
    window._registerGripCaptcha = new GripCaptcha("register-gripcaptcha", {
      apiKey: GRIPCAPTCHA_SITE_KEY,
      theme: "dark",
      challengeType: "auto",
      onSuccess: function(data) {
        var el = document.querySelector("#register-captcha-token");
        if (el) el.value = JSON.stringify({ token: data.validation_token, signature: data.validation_signature, expires_at: data.expires_at });
      },
      onError: function(err) {
        console.error("[GripCaptcha] Register error:", err);
      }
    });
  } catch(e) {
    console.error("[GripCaptcha] Register init error:", e);
  }
}

// Reset captcha on tab switch
document.querySelectorAll(".auth-tabs .tab-btn").forEach(function(btn) {
  btn.addEventListener("click", function() {
    if (window._loginGripCaptcha) try { window._loginGripCaptcha.reset(); } catch(e) {}
    if (window._registerGripCaptcha) try { window._registerGripCaptcha.reset(); } catch(e) {}
    document.querySelectorAll("#login-captcha-token, #register-captcha-token").forEach(function(el) { el.value = ""; });
  });
});

// Init when DOM is ready
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initGripCaptcha);
} else {
  initGripCaptcha();
}
