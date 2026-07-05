// middleware/captcha.js — GripCaptcha verification (replaces Google reCAPTCHA)
// Skips captcha for cross-origin requests from trusted GripAi services
// (rate limiter, honeypot, and timing checks still apply)

const https = require("https");
const http = require("http");

const GRIPCAPTCHA_URL = process.env.GRIPCAPTCHA_URL || "https://cp.gripnews.uk";
const GRIPCAPTCHA_API_KEY = process.env.GRIPCAPTCHA_API_KEY || "";

// Trusted origins that can skip captcha (they use the embedded login modal)
const TRUSTED_ORIGINS = (process.env.ALLOWED_ORIGINS || "")
  .split(",")
  .map(o => o.trim())
  .filter(Boolean);

/**
 * Verify a GripCaptcha validation token
 * POST /api/v1/token/validate with { validation_token }
 */
function verifyGripCaptchaToken(validationToken) {
  return new Promise((resolve, reject) => {
    const postData = JSON.stringify({ validation_token: validationToken });
    const url = new URL(`${GRIPCAPTCHA_URL}/api/v1/token/validate`);
    const isHttps = url.protocol === "https:";
    const transport = isHttps ? https : http;

    const options = {
      hostname: url.hostname,
      port: url.port || (isHttps ? 443 : 80),
      path: url.pathname,
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Content-Length": Buffer.byteLength(postData),
        "X-Site-API-Key": GRIPCAPTCHA_API_KEY,
      },
      // Allow self-signed certs on internal network
      rejectUnauthorized: isHttps,
    };

    const req = transport.request(options, (res) => {
      let data = "";
      res.on("data", (chunk) => (data += chunk));
      res.on("end", () => {
        try {
          const parsed = JSON.parse(data);
          resolve({ success: parsed.success && parsed.valid, data: parsed });
        } catch (e) {
          reject(new Error("Failed to parse GripCaptcha response"));
        }
      });
    });

    req.on("error", reject);
    req.write(postData);
    req.end();
  });
}

/**
 * Express middleware: verify captcha_token in request body
 * Accepts either:
 *   - A raw validation_token string
 *   - A JSON string with { token, signature, expires_at } (from GripCaptcha widget hidden input)
 * Skips verification for requests from trusted origins (cross-origin modal login)
 */
async function verifyCaptcha(req, res, next) {
  // Check if request comes from a trusted GripAi service
  const origin = req.get("Origin") || req.get("Referer") || "";
  const isTrusted = TRUSTED_ORIGINS.some(o => origin.startsWith(o));

  if (isTrusted) {
    // Trusted cross-origin request — skip captcha
    // Rate limiter, honeypot, and timing checks still protect against abuse
    return next();
  }

  // Direct access to auth portal — require captcha
  const { captcha_token } = req.body;

  if (!captcha_token) {
    return res.status(400).json({
      success: false,
      error: "Please complete the CAPTCHA verification",
    });
  }

  // Parse the token — could be JSON from GripCaptcha widget or a raw string
  let validationToken = captcha_token;
  try {
    const parsed = JSON.parse(captcha_token);
    if (parsed.token) validationToken = parsed.token;
  } catch (e) {
    // Not JSON, use as-is
  }

  try {
    const result = await verifyGripCaptchaToken(validationToken);

    if (!result.success) {
      console.error("[CAPTCHA] GripCaptcha validation failed:", result.data);
      return res.status(403).json({
        success: false,
        error: "CAPTCHA verification failed — please try again",
      });
    }

    next();
  } catch (err) {
    console.error("[CAPTCHA] Verification error:", err.message);
    return res.status(500).json({
      success: false,
      error: "Captcha verification service unavailable",
    });
  }
}

module.exports = { verifyCaptcha };
