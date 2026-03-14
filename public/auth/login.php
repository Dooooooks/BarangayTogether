<?php
declare(strict_types=1);
require_once __DIR__ . '/../_layout.php';

global $supabase;

if (is_logged_in()) redirect('/activities/index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_verify();

  $email = trim((string)($_POST['email'] ?? ''));
  $password = (string)($_POST['password'] ?? '');

  if (!$supabase) {
    flash_set('error', 'Supabase is not configured.');
    redirect('/auth/login.php');
  }

  if ($email === '' || $password === '') {
    flash_set('error', 'Email and password are required.');
    redirect('/auth/login.php');
  }

  $res = $supabase->authSignIn($email, $password);
  if ($res['ok'] && is_array($res['data'])) {
    auth_store_session($res['data']);
    session_regenerate_id(true);

    $verified = auth_is_email_verified(auth_current_user());
    if ($verified === false) {
      auth_clear_session();
      flash_set('verify', 'Please verify your email address before logging in.');
      redirect('/auth/login.php');
    }

    $p = current_user_profile();
    if ($p && ($p['status'] ?? 'active') !== 'active') {
      auth_clear_session();
      flash_set('error', 'Your account is disabled. Please contact the barangay office.');
      redirect('/auth/login.php');
    }

    // Send admins to the admin dashboard for faster workflow.
    if ($p && ($p['role'] ?? '') === 'admin') {
      redirect('/admin/index.php');
    }

    redirect('/activities/index.php');
  }

  $msg = 'Login failed.';
  if (is_array($res['data']) && isset($res['data']['error_description'])) $msg = (string)$res['data']['error_description'];
  flash_set('error', $msg);
  redirect('/auth/login.php');
}

layout_header('Login', '');
?>

<section class="mx-auto max-w-md px-4 py-12">
  <div class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
    <h1 class="text-2xl font-semibold tracking-tight">Login</h1>
    <p class="mt-2 text-sm text-slate-600">Access your account to join volunteer activities.</p>

    <form method="post" class="mt-6 grid gap-4">
      <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>" />

      <label class="grid gap-1.5">
        <span class="text-sm font-semibold text-slate-800">Email</span>
        <input name="email" type="email" autocomplete="email" class="h-11 rounded-xl border border-slate-300 bg-white px-3 text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/15" placeholder="name@email.com" required />
      </label>

      <label class="grid gap-1.5">
        <span class="text-sm font-semibold text-slate-800">Password</span>
        <input name="password" type="password" autocomplete="current-password" class="h-11 rounded-xl border border-slate-300 bg-white px-3 text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/15" required />
      </label>

      <button class="mt-2 inline-flex h-11 items-center justify-center rounded-xl bg-emerald-600 px-5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500" type="submit">Login</button>
    </form>

    <p class="mt-6 text-sm text-slate-600">No account yet? <a class="font-semibold text-emerald-700 hover:text-emerald-800" href="/auth/register.php">Sign up</a></p>
  </div>
</section>

<?php layout_footer(); ?>
