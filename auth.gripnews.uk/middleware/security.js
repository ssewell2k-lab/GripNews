// middleware/security.js — Anti-abuse layer for GameGrip Auth
// Rate limiter, honeypot, timing check, risk scoring, high-risk blocking

const rateLimitMap = new Map();

function getClientIp(req) {
  return (
    req.headers["x-forwarded-for"]?.split(",")[0] ||
    req.socket.remoteAddress ||
    "unknown"
  );
}

/*
|--------------------------------------------------------------------------
| RATE LIMITER
|--------------------------------------------------------------------------
*/

function rateLimiter(options = {}) {
  const {
    windowMs = 60 * 1000,
    max = 10,
    message = "Too many requests"
  } = options;

  return (req, res, next) => {
    const ip = getClientIp(req);
    const key = `${ip}:${req.path}`;

    const now = Date.now();

    if (!rateLimitMap.has(key)) {
      rateLimitMap.set(key, []);
    }

    const timestamps = rateLimitMap
      .get(key)
      .filter(ts => now - ts < windowMs);

    timestamps.push(now);

    rateLimitMap.set(key, timestamps);

    if (timestamps.length > max) {
      return res.status(429).json({
        success: false,
        error: message
      });
    }

    next();
  };
}

/*
|--------------------------------------------------------------------------
| HONEYPOT CHECK
|--------------------------------------------------------------------------
*/

function honeypot(req, res, next) {
  const trap = req.body.website;

  if (trap && trap.trim() !== "") {
    return res.status(403).json({
      success: false,
      error: "Bot detected"
    });
  }

  next();
}

/*
|--------------------------------------------------------------------------
| TIMING CHECK
|--------------------------------------------------------------------------
*/

function timingCheck(minMs = 1500) {
  return (req, res, next) => {
    const formTime = Number(req.body.form_started_at);

    if (!formTime) {
      return res.status(400).json({
        success: false,
        error: "Missing timing field"
      });
    }

    const elapsed = Date.now() - formTime;

    if (elapsed < minMs) {
      return res.status(403).json({
        success: false,
        error: "Suspiciously fast submission"
      });
    }

    next();
  };
}

/*
|--------------------------------------------------------------------------
| SIMPLE RISK SCORE
|--------------------------------------------------------------------------
*/

function riskScore(req, res, next) {
  let score = 0;

  const userAgent = req.headers["user-agent"] || "";

  // Missing UA
  if (!userAgent) score += 40;

  // Suspicious agents
  const suspicious = [
    "curl",
    "python",
    "wget",
    "bot",
    "crawler",
    "scrapy"
  ];

  if (
    suspicious.some(term =>
      userAgent.toLowerCase().includes(term)
    )
  ) {
    score += 50;
  }

  // Missing accept-language
  if (!req.headers["accept-language"]) {
    score += 10;
  }

  req.riskScore = score;

  next();
}

/*
|--------------------------------------------------------------------------
| BLOCK HIGH RISK
|--------------------------------------------------------------------------
*/

function blockHighRisk(maxRisk = 60) {
  return (req, res, next) => {
    if ((req.riskScore || 0) >= maxRisk) {
      return res.status(403).json({
        success: false,
        error: "High risk request blocked"
      });
    }

    next();
  };
}

module.exports = {
  rateLimiter,
  honeypot,
  timingCheck,
  riskScore,
  blockHighRisk
};
