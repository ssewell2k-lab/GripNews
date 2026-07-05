// middleware/auth.js — JWT verification middleware
const { verifyAccessToken } = require("../utils/jwt");

function authMiddleware(req, res, next) {
  const header = req.headers.authorization;

  if (!header || !header.startsWith("Bearer ")) {
    return res.status(401).json({ error: "Unauthorized — no token provided" });
  }

  const token = header.split(" ")[1];

  try {
    const decoded = verifyAccessToken(token);

    // Ensure it's an access token, not refresh
    if (decoded.type !== "access") {
      return res.status(401).json({ error: "Invalid token type" });
    }

    req.user = decoded;
    next();
  } catch (err) {
    if (err.name === "TokenExpiredError") {
      return res.status(401).json({ error: "Token expired", code: "TOKEN_EXPIRED" });
    }
    return res.status(401).json({ error: "Invalid token" });
  }
}

// Role-based middleware factory — v0.6.0-beta: ACTIVE
function requireRole(...roles) {
  return (req, res, next) => {
    if (!req.user) {
      return res.status(401).json({ error: "Unauthorized" });
    }
    if (!roles.includes(req.user.role || "user")) {
      return res.status(403).json({ error: "Forbidden", message: "Insufficient permissions" });
    }
    next();
  };
}

module.exports = { authMiddleware, requireRole };
