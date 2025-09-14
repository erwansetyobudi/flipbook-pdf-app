<?php
declare(strict_types=1);

/*
 * -----------------------------------------------------------------------------
 *  Flipbook Library - Configuration
 * -----------------------------------------------------------------------------
 *  Author  : Erwan Setyo Budi
 *  Email   : erwans818@gmail.com
 *  File    : config.php
 *  License : MIT
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to do so, subject to the
 *  following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in
 *  all copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 * -----------------------------------------------------------------------------
 */

/* ---------------------------------------------------------------------------
 | Block direct access to this file (allow CLI include/require only)
 * --------------------------------------------------------------------------- */
if (
  php_sapi_name() !== 'cli' &&
  isset($_SERVER['SCRIPT_FILENAME']) &&
  realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])
) {
  http_response_code(403);
  header('Content-Type: text/plain; charset=utf-8');
  echo "403 Forbidden\nThis file cannot be accessed directly.";
  exit;
}

/* ---------------------------------------------------------------------------
 | App URL helpers (behind proxy safe-ish)
 * --------------------------------------------------------------------------- */
function app_url(): string {
  $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
           || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

  $scheme = $https ? 'https' : 'http';
  $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
  $base   = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');

  $url = rtrim("{$scheme}://{$host}{$base}", '/');

  // buang /admin di ekor jika ada (agar app_url() menunjuk root aplikasi)
  return rtrim(preg_replace('~\/admin$~', '', $url), '/');
}

/** Convenience: absolute base for current script (may include /admin) */
function current_base_url(): string {
  $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
           || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

  $scheme = $https ? 'https' : 'http';
  $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
  $base   = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
  return rtrim("{$scheme}://{$host}{$base}", '/');
}

/* Old $SITE_URL compatibility (kept for legacy includes) */
$SITE_URL = current_base_url();

/* ---------------------------------------------------------------------------
 | Security: strict output escaping helper
 * --------------------------------------------------------------------------- */
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

/* ---------------------------------------------------------------------------
 | Database configuration (use environment variables in production)
 |
 | Put these in your environment (Apache/Nginx/OS), e.g.:
 |   DB_HOST=127.0.0.1
 |   DB_NAME=flipbook
 |   DB_USER=flip_user
 |   DB_PASS=super-secret
 * --------------------------------------------------------------------------- */
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'flipbook');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'root');
define('DB_CHARSET', 'utf8mb4');

define('DB_DSN', sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET));

/* ---------------------------------------------------------------------------
 | Session security
 * --------------------------------------------------------------------------- */
define('SESSION_NAME', 'flipbook_admin'); // change name if hosting multiple apps

/**
 * Start secure PHP session with hardened cookie parameters.
 */
function ensure_session_started(): void {
  if (session_status() !== PHP_SESSION_ACTIVE) {
    // Harden cookie params
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
             || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    session_name(SESSION_NAME);
    session_set_cookie_params([
      'lifetime' => 0,
      'path'     => '/',
      'domain'   => '',
      'secure'   => $https,      // true on HTTPS
      'httponly' => true,
      'samesite' => 'Strict',    // or 'Lax' if you need cross-site redirects
    ]);

    // Mitigate fixation
    if (!headers_sent()) {
      ini_set('session.use_strict_mode', '1');
      ini_set('session.cookie_httponly', '1');
      ini_set('session.use_only_cookies', '1');
    }

    session_start();
  }
}

/* ---------------------------------------------------------------------------
 | PDO singleton with safe defaults
 * --------------------------------------------------------------------------- */
function get_db(): PDO {
  static $pdo = null;
  if ($pdo instanceof PDO) { return $pdo; }

  $options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,        // native prepared statements
    PDO::ATTR_PERSISTENT         => false,        // avoid tricky connection reuse
  ];

  // Optional: network/connect timeout (if driver supports)
  try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, $options);
  } catch (PDOException $e) {
    // Avoid leaking credentials/DSN in error output
    error_log('[DB] Connection failed: ' . $e->getMessage());
    throw new PDOException('Database connection failed.');
  }

  return $pdo;
}

/* ---------------------------------------------------------------------------
 | Settings helper (reads from DB `settings` table)
 * --------------------------------------------------------------------------- */
function get_setting(string $key, string $default = ''): string {
  $db = get_db();
  $st = $db->prepare('SELECT value FROM settings WHERE name = ? LIMIT 1');
  $st->execute([$key]);
  $val = $st->fetchColumn();
  return ($val !== false && $val !== null) ? (string)$val : $default;
}

/* ---------------------------------------------------------------------------
 | Optional: common security headers (call this in entry pages, not here)
 * --------------------------------------------------------------------------- */
/*
function send_security_headers(): void {
  if (headers_sent()) return;
  header('X-Content-Type-Options: nosniff');
  header('X-Frame-Options: SAMEORIGIN');
  header('Referrer-Policy: no-referrer-when-downgrade');
  header('X-XSS-Protection: 0'); // modern browsers ignore; CSP is preferred
  // header("Content-Security-Policy: default-src 'self'; img-src 'self' data: blob:; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'");
}
*/
