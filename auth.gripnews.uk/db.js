// db.js — MySQL connection pool with promise support
const mysql = require("mysql2/promise");
require("dotenv").config();

const pool = mysql.createPool({
  host: process.env.DB_HOST || "localhost",
  port: process.env.DB_PORT || 3306,
  user: process.env.DB_USER,
  password: process.env.DB_PASS,
  database: process.env.DB_NAME,
  waitForConnections: true,
  connectionLimit: 20,
  queueLimit: 0,
  enableKeepAlive: true,
  keepAliveInitialDelay: 10000,
});

// Health check helper
async function healthCheck() {
  try {
    const [rows] = await pool.query("SELECT 1");
    return true;
  } catch (err) {
    console.error("DB health check failed:", err.message);
    return false;
  }
}

module.exports = { pool, healthCheck };
