// routes/auth.js — Authentication endpoints
const express = require("express");
const bcrypt = require("bcryptjs");
const { body, validationResult } = require("express-validator");
const { pool } = require("../db");
const { signAccessToken, signRefreshToken, verifyRefreshToken } = require("../utils/jwt");

const { honeypot, timingCheck, riskScore, blockHighRisk } = require("../middleware/security");
const { verifyCaptcha } = require("../middleware/captcha");

const router = express.Router();

// Validation rules
const registerValidation = [
  body("email").isEmail().normalizeEmail().withMessage("Valid email required"),
  body("password").isLength({ min: 8 }).withMessage("Password must be at least 8 characters"),
];

const loginValidation = [
  body("email").isEmail().normalizeEmail().withMessage("Valid email required"),
  body("password").notEmpty().withMessage("Password required"),
];

// REGISTER
router.post("/register",
  registerValidation,
  honeypot,
  timingCheck(1500),
  riskScore,
  blockHighRisk(60),
  verifyCaptcha,
  async (req, res) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) {
    return res.status(400).json({ error: errors.array()[0].msg });
  }

  const { email, password } = req.body;

  try {
    // Check if user exists
    const [existing] = await pool.query("SELECT id FROM users WHERE email = ?", [email]);
    if (existing.length > 0) {
      return res.status(409).json({ error: "Email already registered" });
    }

    const hash = await bcrypt.hash(password, 12);

    const [result] = await pool.query(
      "INSERT INTO users (email, password_hash) VALUES (?, ?)",
      [email, hash]
    );

    const user = { id: result.insertId, email, plan: "free" };
    const accessToken = signAccessToken(user);
    const refreshToken = signRefreshToken(user);

    // Store refresh token hash in DB for revocation support
    const refreshHash = await bcrypt.hash(refreshToken, 10);
    await pool.query(
      "INSERT INTO refresh_tokens (user_id, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY))",
      [result.insertId, refreshHash]
    );

    res.status(201).json({
      success: true,
      accessToken,
      refreshToken,
      user: { id: user.id, email: user.email, plan: user.plan || "free" },
    });
  } catch (err) {
    console.error("Register error:", err);
    res.status(500).json({ error: "Registration failed" });
  }
});

// LOGIN
router.post("/login",
  loginValidation,
  honeypot,
  timingCheck(1500),
  riskScore,
  blockHighRisk(60),
  verifyCaptcha,
  async (req, res) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) {
    return res.status(400).json({ error: errors.array()[0].msg });
  }

  const { email, password } = req.body;

  try {
    const [rows] = await pool.query("SELECT * FROM users WHERE email = ?", [email]);
    if (rows.length === 0) {
      return res.status(401).json({ error: "Invalid credentials" });
    }

    const user = rows[0];
    const match = await bcrypt.compare(password, user.password_hash);

    if (!match) {
      return res.status(401).json({ error: "Invalid credentials" });
    }

    const accessToken = signAccessToken(user);
    const refreshToken = signRefreshToken(user);

    // Store refresh token hash
    const refreshHash = await bcrypt.hash(refreshToken, 10);
    await pool.query(
      "INSERT INTO refresh_tokens (user_id, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY))",
      [user.id, refreshHash]
    );

    res.json({
      accessToken,
      refreshToken,
      user: {
        id: user.id,
        email: user.email,
        plan: user.plan || "free",
      },
    });
  } catch (err) {
    console.error("Login error:", err);
    res.status(500).json({ error: "Login failed" });
  }
});

// REFRESH — get new access token
router.post("/refresh", async (req, res) => {
  const { refreshToken } = req.body;
  if (!refreshToken) {
    return res.status(400).json({ error: "Refresh token required" });
  }

  try {
    const decoded = verifyRefreshToken(refreshToken);

    if (decoded.type !== "refresh") {
      return res.status(401).json({ error: "Invalid token type" });
    }

    // Verify token exists in DB and not revoked
    const [rows] = await pool.query(
      `SELECT * FROM refresh_tokens 
       WHERE user_id = ? AND revoked = 0 AND expires_at > NOW()
       ORDER BY created_at DESC LIMIT 10`,
      [decoded.userId]
    );

    let valid = false;
    for (const row of rows) {
      if (await bcrypt.compare(refreshToken, row.token_hash)) {
        valid = true;
        break;
      }
    }

    if (!valid) {
      return res.status(401).json({ error: "Invalid or revoked refresh token" });
    }

    // Issue new tokens
    const [userRows] = await pool.query("SELECT id, email, role, plan FROM users WHERE id = ?", [decoded.userId]);
    if (userRows.length === 0) {
      return res.status(401).json({ error: "User not found" });
    }

    const user = userRows[0];
    const newAccessToken = signAccessToken(user);
    const newRefreshToken = signRefreshToken(user);

    // Store new refresh, revoke old one
    const newHash = await bcrypt.hash(newRefreshToken, 10);
    await pool.query(
      "INSERT INTO refresh_tokens (user_id, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY))",
      [user.id, newHash]
    );

    res.json({
      accessToken: newAccessToken,
      refreshToken: newRefreshToken,
      user: { id: user.id, email: user.email, plan: user.plan || "free" },
    });
  } catch (err) {
    if (err.name === "TokenExpiredError") {
      return res.status(401).json({ error: "Refresh token expired", code: "REFRESH_EXPIRED" });
    }
    console.error("Refresh error:", err);
    res.status(401).json({ error: "Invalid refresh token" });
  }
});

// LOGOUT — revoke refresh token
router.post("/logout", async (req, res) => {
  const { refreshToken } = req.body;

  if (!refreshToken) {
    return res.status(200).json({ success: true, message: "Logged out (no refresh token)" });
  }

  try {
    const decoded = verifyRefreshToken(refreshToken);

    // Revoke all refresh tokens for this user (or match specific one)
    await pool.query(
      "UPDATE refresh_tokens SET revoked = 1 WHERE user_id = ?",
      [decoded.userId]
    );

    res.json({ success: true, message: "Logged out successfully" });
  } catch (err) {
    // Even if token is invalid, consider logout successful
    res.json({ success: true, message: "Logged out" });
  }
});

module.exports = router;
