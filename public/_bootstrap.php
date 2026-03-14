<?php
declare(strict_types=1);

// Basic bootstrap: env, session, helpers, Supabase HTTP client.

ini_set('display_errors', '0');
error_reporting(E_ALL);

session_start();

function env_load(string $path): void {
  if (!is_file($path)) return;
  $vars = parse_ini_file($path, false, INI_SCANNER_RAW);
  if (!is_array($vars)) return;
  foreach ($vars as $k => $v) {
    if (!is_string($k)) continue;
    if (getenv($k) !== false) continue;
    putenv($k . '=' . $v);
    $_ENV[$k] = $v;
  }
}

env_load(__DIR__ . '/../.env');

function config(string $key, ?string $default = null): ?string {
  $v = getenv($key);
  if ($v === false) return $default;
  return $v;
}

function e(string $v): string {
  return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

function redirect(string $to): never {
  header('Location: ' . $to);
  exit;
}

function app_base_url(): string {
  $cfg = (string)config('APP_URL', '');
  if ($cfg !== '') return rtrim($cfg, '/');

  $https = (string)($_SERVER['HTTPS'] ?? '');
  $scheme = ($https !== '' && $https !== 'off') ? 'https' : 'http';
  $host = (string)($_SERVER['HTTP_HOST'] ?? 'localhost:8000');
  return $scheme . '://' . $host;
}

function flash_set(string $type, string $message): void {
  $_SESSION['_flash'] = ['type' => $type, 'message' => $message];
}

function flash_get(): ?array {
  $v = $_SESSION['_flash'] ?? null;
  unset($_SESSION['_flash']);
  return is_array($v) ? $v : null;
}

function csrf_token(): string {
  if (!isset($_SESSION['_csrf'])) {
    $_SESSION['_csrf'] = bin2hex(random_bytes(16));
  }
  return (string)$_SESSION['_csrf'];
}

function csrf_verify(): void {
  $token = (string)($_POST['_csrf'] ?? '');
  if ($token === '' || !hash_equals((string)($_SESSION['_csrf'] ?? ''), $token)) {
    http_response_code(400);
    echo 'Invalid CSRF token.';
    exit;
  }
}

final class SupabaseClient {
  private string $url;
  private string $anonKey;

  public function __construct(string $url, string $anonKey) {
    $this->url = rtrim($url, '/');
    $this->anonKey = $anonKey;
  }

  public function authSignUp(string $email, string $password, array $data, ?string $emailRedirectTo = null): array {
    $payload = [
      'email' => $email,
      'password' => $password,
      'data' => $data,
    ];
    if ($emailRedirectTo) $payload['email_redirect_to'] = $emailRedirectTo;
    return $this->request('POST', '/auth/v1/signup', $payload, null);
  }

  public function authSignIn(string $email, string $password): array {
    return $this->request('POST', '/auth/v1/token?grant_type=password', [
      'email' => $email,
      'password' => $password,
    ], null);
  }

  public function authRefresh(string $refreshToken): array {
    return $this->request('POST', '/auth/v1/token?grant_type=refresh_token', [
      'refresh_token' => $refreshToken,
    ], null);
  }

  public function authGetUser(string $accessToken): array {
    return $this->request('GET', '/auth/v1/user', null, $accessToken);
  }

  public function restGet(string $path, array $query = [], ?string $accessToken = null): array {
    $q = $query ? ('?' . http_build_query($query)) : '';
    return $this->request('GET', '/rest/v1/' . ltrim($path, '/') . $q, null, $accessToken, [
      'Accept: application/json',
    ]);
  }

  public function restPost(string $path, array $payload, ?string $accessToken = null, array $extraHeaders = []): array {
    return $this->request('POST', '/rest/v1/' . ltrim($path, '/'), $payload, $accessToken, array_merge([
      'Prefer: return=representation',
    ], $extraHeaders));
  }

  public function restPatch(string $pathWithQuery, array $payload, ?string $accessToken = null): array {
    return $this->request('PATCH', '/rest/v1/' . ltrim($pathWithQuery, '/'), $payload, $accessToken, [
      'Prefer: return=representation',
    ]);
  }

  public function restDelete(string $pathWithQuery, ?string $accessToken = null): array {
    return $this->request('DELETE', '/rest/v1/' . ltrim($pathWithQuery, '/'), null, $accessToken);
  }

  public function rpc(string $fn, array $payload, ?string $accessToken = null): array {
    return $this->request('POST', '/rest/v1/rpc/' . $fn, $payload, $accessToken);
  }

  public function restCount(string $path, array $query = [], ?string $accessToken = null): ?int {
    // Uses Content-Range total from PostgREST when Prefer: count=exact is set.
    // Add a tiny payload default if caller didn't specify.
    if (!array_key_exists('select', $query)) $query['select'] = 'id';
    if (!array_key_exists('limit', $query)) $query['limit'] = '1';
    $q = $query ? ('?' . http_build_query($query)) : '';
    $res = $this->request('GET', '/rest/v1/' . ltrim($path, '/') . $q, null, $accessToken, [
      'Accept: application/json',
      'Prefer: count=exact',
    ]);
    if (!$res['ok']) return null;
    $cr = $res['headers']['content-range'] ?? $res['headers']['Content-Range'] ?? null;
    if (!is_string($cr) || $cr === '') return null;
    if (preg_match('/\/(\d+)\s*$/', $cr, $m)) return (int)$m[1];
    return null;
  }

  private function request(string $method, string $path, ?array $json, ?string $accessToken, array $extraHeaders = []): array {
    $ch = curl_init();
    $url = $this->url . $path;

    $respHeaders = [];

    $headers = array_merge([
      'apikey: ' . $this->anonKey,
      'Content-Type: application/json',
    ], $extraHeaders);
    if ($accessToken) {
      $headers[] = 'Authorization: Bearer ' . $accessToken;
    }

    curl_setopt_array($ch, [
      CURLOPT_URL => $url,
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER => $headers,
      CURLOPT_TIMEOUT => 20,
      CURLOPT_HEADERFUNCTION => static function ($ch, string $headerLine) use (&$respHeaders): int {
        $len = strlen($headerLine);
        $headerLine = trim($headerLine);
        if ($headerLine === '' || !str_contains($headerLine, ':')) return $len;
        [$name, $value] = explode(':', $headerLine, 2);
        $name = trim($name);
        $value = trim($value);
        if ($name !== '') {
          // Keep a lower-case key too for convenience.
          $respHeaders[$name] = $value;
          $respHeaders[strtolower($name)] = $value;
        }
        return $len;
      },
    ]);
    if ($json !== null) {
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
    }

    $body = curl_exec($ch);
    $err = curl_error($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($body === false) {
      return ['ok' => false, 'status' => 0, 'error' => $err ?: 'Unknown cURL error', 'data' => null];
    }

    $data = null;
    if ($body !== '') {
      $decoded = json_decode($body, true);
      $data = (json_last_error() === JSON_ERROR_NONE) ? $decoded : $body;
    }

    $ok = $status >= 200 && $status < 300;
    return ['ok' => $ok, 'status' => $status, 'data' => $data, 'raw' => $body, 'headers' => $respHeaders];
  }
}

function activity_status_label(array $activity): string {
  $status = (string)($activity['status'] ?? '');
  $slotsLeft = (int)($activity['slots_left'] ?? -1);
  if ($status === 'open' && $slotsLeft === 0) return 'Full';
  if ($status === 'done') return 'Completed';
  if ($status === 'open') return 'Open';
  if ($status === 'closed') return 'Closed';
  if ($status === 'cancelled') return 'Cancelled';
  return $status !== '' ? $status : 'Unknown';
}

$SUPABASE_URL = (string)config('SUPABASE_URL', '');
$SUPABASE_ANON_KEY = (string)config('SUPABASE_ANON_KEY', '');
if ($SUPABASE_URL === '' || $SUPABASE_ANON_KEY === '') {
  // Allow pages to render a friendly setup prompt.
  $supabase = null;
} else {
  $supabase = new SupabaseClient($SUPABASE_URL, $SUPABASE_ANON_KEY);
}

function session_access_token(): ?string {
  $t = $_SESSION['sb_access_token'] ?? null;
  return is_string($t) && $t !== '' ? $t : null;
}

function session_refresh_token(): ?string {
  $t = $_SESSION['sb_refresh_token'] ?? null;
  return is_string($t) && $t !== '' ? $t : null;
}

function session_expires_at(): int {
  $v = $_SESSION['sb_expires_at'] ?? 0;
  return is_int($v) ? $v : 0;
}

function auth_store_session(array $tokenResponse): void {
  $_SESSION['sb_access_token'] = (string)($tokenResponse['access_token'] ?? '');
  $_SESSION['sb_refresh_token'] = (string)($tokenResponse['refresh_token'] ?? '');
  $expiresIn = (int)($tokenResponse['expires_in'] ?? 0);
  $_SESSION['sb_expires_at'] = time() + max(0, $expiresIn);
}

function auth_clear_session(): void {
  unset($_SESSION['sb_access_token'], $_SESSION['sb_refresh_token'], $_SESSION['sb_expires_at']);
}

function auth_maybe_refresh(): void {
  global $supabase;
  if (!$supabase) return;
  $access = session_access_token();
  $refresh = session_refresh_token();
  if (!$access || !$refresh) return;

  // Refresh if expiring within 60 seconds
  if (session_expires_at() > 0 && session_expires_at() - time() > 60) return;

  $res = $supabase->authRefresh($refresh);
  if ($res['ok'] && is_array($res['data'])) {
    auth_store_session($res['data']);
  } else {
    auth_clear_session();
  }
}

function auth_current_user(): ?array {
  global $supabase;
  if (!$supabase) return null;
  $access = session_access_token();
  if (!$access) return null;
  $res = $supabase->authGetUser($access);
  if (!$res['ok'] || !is_array($res['data'])) return null;
  $d = $res['data'];
  if (isset($d['user']) && is_array($d['user'])) return $d['user'];
  return $d;
}

function auth_is_email_verified(?array $user): ?bool {
  if (!$user) return null;
  $v = $user['email_confirmed_at'] ?? ($user['confirmed_at'] ?? null);
  if ($v === null) return null;
  return is_string($v) ? ($v !== '') : (bool)$v;
}

function current_user_profile(): ?array {
  global $supabase;
  auth_maybe_refresh();
  if (!$supabase) return null;
  $access = session_access_token();
  if (!$access) return null;

  $uid = (string)($GLOBALS['_cached_uid'] ?? '');
  if ($uid === '') {
    $u = $supabase->authGetUser($access);
    if (!$u['ok'] || !is_array($u['data'])) return null;
    $uid = (string)($u['data']['id'] ?? ($u['data']['user']['id'] ?? ''));
    if ($uid === '') return null;
    $GLOBALS['_cached_uid'] = $uid;
  }

  $res = $supabase->restGet('profiles', [
    'select' => 'id,role,status,email,full_name,contact_number,address,created_at,updated_at',
    'id' => 'eq.' . $uid,
    'limit' => '1',
  ], $access);

  if (!$res['ok'] || !is_array($res['data']) || !isset($res['data'][0]) || !is_array($res['data'][0])) return null;
  return $res['data'][0];
}

function require_login(): void {
  if (!session_access_token()) {
    flash_set('error', 'Please log in to continue.');
    redirect('/auth/login.php');
  }
}

function require_admin(): void {
  require_login();
  $p = current_user_profile();
  if (!$p || ($p['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo 'Forbidden';
    exit;
  }
}

function is_logged_in(): bool {
  return session_access_token() !== null;
}

function is_admin_user(): bool {
  $p = current_user_profile();
  return $p && ($p['role'] ?? '') === 'admin';
}

function ensure_active_account(): void {
  if (!is_logged_in()) return;
  $p = current_user_profile();
  if ($p && ($p['status'] ?? 'active') !== 'active') {
    auth_clear_session();
    flash_set('error', 'Your account is disabled. Please contact the barangay office.');
    redirect('/auth/login.php');
  }
}

function ensure_verified_email(): void {
  if (!is_logged_in()) return;
  auth_maybe_refresh();
  $user = auth_current_user();
  $verified = auth_is_email_verified($user);
  if ($verified === false) {
    auth_clear_session();
    flash_set('verify', 'Please verify your email address to continue.');
    redirect('/auth/login.php');
  }
}

ensure_active_account();
ensure_verified_email();
