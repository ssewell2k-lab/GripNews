// routes/user.js — User profile, sessions, and API key management
const express = require("express");
const crypto = require("crypto");
const bcrypt = require("bcryptjs");
const { pool } = require("../db");
const { authMiddleware } = require("../middleware/auth");

const router = express.Router();

// ── Profile ────────────────────────────────────────────────

// GET /user/me — current user profile
router.get("/me", authMiddleware, async (req, res) => {
  try {
    const [rows] = await pool.query(
      "SELECT id, email, display_name, avatar_url, role, plan, stripe_customer_id, plan_expires_at, email_verified, created_at, last_login FROM users WHERE id = ?",
      [req.user.userId]
    );

    if (rows.length === 0) {
      return res.status(404).json({ error: "User not found" });
    }

    res.json({ user: rows[0] });
  } catch (err) {
    console.error("Profile error:", err);
    res.status(500).json({ error: "Failed to fetch profile" });
  }
});

// PUT /user/profile — update display name
router.put("/profile", authMiddleware, async (req, res) => {
  try {
    const { display_name } = req.body;
    if (display_name !== undefined) {
      await pool.query(
        "UPDATE users SET display_name = ? WHERE id = ?",
        [display_name.slice(0, 100), req.user.userId]
      );
    }
    res.json({ success: true });
  } catch (err) {
    console.error("Update profile error:", err);
    res.status(500).json({ error: "Failed to update profile" });
  }
});

// ── Sessions ───────────────────────────────────────────────

// GET /user/sessions — active sessions (refresh tokens)
router.get("/sessions", authMiddleware, async (req, res) => {
  try {
    const [rows] = await pool.query(
      `SELECT id, created_at, expires_at, revoked 
       FROM refresh_tokens 
       WHERE user_id = ? 
       ORDER BY created_at DESC`,
      [req.user.userId]
    );
    res.json({ sessions: rows });
  } catch (err) {
    console.error("Sessions error:", err);
    res.status(500).json({ error: "Failed to fetch sessions" });
  }
});

// DELETE /user/sessions/:id — revoke a specific session
router.delete("/sessions/:id", authMiddleware, async (req, res) => {
  try {
    await pool.query(
      "UPDATE refresh_tokens SET revoked = 1 WHERE id = ? AND user_id = ?",
      [req.params.id, req.user.userId]
    );
    res.json({ success: true, message: "Session revoked" });
  } catch (err) {
    console.error("Revoke session error:", err);
    res.status(500).json({ error: "Failed to revoke session" });
  }
});

// ── API Keys ───────────────────────────────────────────────

// GET /user/api-keys — list user's API keys
router.get("/api-keys", authMiddleware, async (req, res) => {
  try {
    const [rows] = await pool.query(
      `SELECT id, name, scopes, last_used_at, expires_at, revoked, created_at,
              CONCAT(SUBSTRING(key_hash, 1, 8), '...') AS key_preview
       FROM api_keys 
       WHERE user_id = ? 
       ORDER BY created_at DESC`,
      [req.user.userId]
    );
    res.json({ keys: rows });
  } catch (err) {
    console.error("List API keys error:", err);
    res.status(500).json({ error: "Failed to fetch API keys" });
  }
});

// POST /user/api-keys — create a new API key
router.post("/api-keys", authMiddleware, async (req, res) => {
  try {
    const { name, scopes } = req.body;
    const keyName = (name || "Default Key").slice(0, 100);

    // Check key limit (max 5 active keys per user)
    const [existing] = await pool.query(
      "SELECT COUNT(*) AS count FROM api_keys WHERE user_id = ? AND revoked = 0",
      [req.user.userId]
    );
    if (existing[0].count >= 5) {
      return res.status(400).json({ error: "Maximum 5 active API keys allowed" });
    }

    // Generate a secure API key: gk_<32 random hex chars>
    const rawKey = "gk_" + crypto.randomBytes(24).toString("hex");
    const keyHash = await bcrypt.hash(rawKey, 10);

    const scopeJson = scopes ? JSON.stringify(scopes) : null;

    const [result] = await pool.query(
      `INSERT INTO api_keys (user_id, key_hash, name, scopes, expires_at)
       VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 1 YEAR))`,
      [req.user.userId, keyHash, keyName, scopeJson]
    );

    // Return the raw key ONCE — user must copy it now
    res.status(201).json({
      success: true,
      key: {
        id: result.insertId,
        name: keyName,
        api_key: rawKey,
        scopes: scopes || null,
        expires_at: new Date(Date.now() + 365 * 24 * 60 * 60 * 1000).toISOString(),
        created_at: new Date().toISOString(),
      },
      message: "Copy your API key now — it won't be shown again."
    });
  } catch (err) {
    console.error("Create API key error:", err);
    res.status(500).json({ error: "Failed to create API key" });
  }
});

// DELETE /user/api-keys/:id — revoke an API key
router.delete("/api-keys/:id", authMiddleware, async (req, res) => {
  try {
    const [result] = await pool.query(
      "UPDATE api_keys SET revoked = 1 WHERE id = ? AND user_id = ?",
      [req.params.id, req.user.userId]
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({ error: "API key not found" });
    }

    res.json({ success: true, message: "API key revoked" });
  } catch (err) {
    console.error("Revoke API key error:", err);
    res.status(500).json({ error: "Failed to revoke API key" });
  }
});

module.exports = router;
