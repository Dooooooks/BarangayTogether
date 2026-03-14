<?php
declare(strict_types=1);
require_once __DIR__ . '/../_layout.php';

global $supabase;
if (is_logged_in()) redirect('/activities/index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_verify();

  $fullName = trim((string)($_POST['full_name'] ?? ''));
  $contact = trim((string)($_POST['contact_number'] ?? ''));
  $address = trim((string)($_POST['address'] ?? ''));
  $email = trim((string)($_POST['email'] ?? ''));
  $password = (string)($_POST['password'] ?? '');

  if (!$supabase) {
    flash_set('error', 'Supabase is not configured.');
    redirect('/auth/register.php');
  }

  if ($email === '' || $password === '' || $fullName === '') {
    flash_set('error', 'Full name, email, and password are required.');
    redirect('/auth/register.php');
  }

  $res = $supabase->authSignUp($email, $password, [
    'full_name' => $fullName,
    'contact_number' => $contact,
    'address' => $address,
  ], app_base_url() . '/auth/verified.php');

  if ($res['ok'] && is_array($res['data'])) {
    // If email confirmation is OFF, Supabase returns a session.
    if (isset($res['data']['access_token'])) {
      auth_store_session($res['data']);
      session_regenerate_id(true);
      flash_set('success', 'Account created. Welcome!');
      redirect('/activities/index.php');
    }
    flash_set('verify', 'Account created. Please check your email to verify your account before logging in.');
    redirect('/auth/login.php');
  }

  $msg = 'Registration failed.';
  $code = null;
  if (is_array($res['data']) && isset($res['data']['msg'])) $msg = (string)$res['data']['msg'];
  if (is_array($res['data']) && isset($res['data']['error_description'])) $msg = (string)$res['data']['error_description'];
  if (is_array($res['data']) && isset($res['data']['error_code'])) $code = (string)$res['data']['error_code'];

  if (stripos($msg, 'rate') !== false && stripos($msg, 'limit') !== false) {
    $msg = 'Email rate limit exceeded. Please wait and try again later, or confirm the user manually in Supabase Dashboard (Auth > Users).';
  }
  flash_set('error', $msg);
  redirect('/auth/register.php');
}

layout_header('Sign Up', '');
?>

<section class="mx-auto max-w-md px-4 py-12">
  <div class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
    <h1 class="text-2xl font-semibold tracking-tight">Create Account</h1>
    <p class="mt-2 text-sm text-slate-600">Register to join volunteer activities in the barangay.</p>

    <form method="post" class="mt-6 grid gap-4">
      <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>" />

      <label class="grid gap-1.5">
        <span class="text-sm font-semibold text-slate-800">Full name</span>
        <input name="full_name" class="h-11 rounded-xl border border-slate-300 bg-white px-3 text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/15" placeholder="Juan Dela Cruz" required />
      </label>

      <label class="grid gap-1.5">
        <span class="text-sm font-semibold text-slate-800">Contact number</span>
        <input name="contact_number" class="h-11 rounded-xl border border-slate-300 bg-white px-3 text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/15" placeholder="09xxxxxxxxx" />
      </label>

      <label class="grid gap-1.5">
        <span class="text-sm font-semibold text-slate-800">Address</span>
        <input name="address" class="h-11 rounded-xl border border-slate-300 bg-white px-3 text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/15" placeholder="Purok / Street" />
      </label>

      <label class="grid gap-1.5">
        <span class="text-sm font-semibold text-slate-800">Email</span>
        <input name="email" type="email" autocomplete="email" class="h-11 rounded-xl border border-slate-300 bg-white px-3 text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/15" placeholder="name@email.com" required />
      </label>

      <label class="grid gap-1.5">
        <span class="text-sm font-semibold text-slate-800">Password</span>
        <input name="password" type="password" autocomplete="new-password" class="h-11 rounded-xl border border-slate-300 bg-white px-3 text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/15" required />
      </label>

      <button class="mt-2 inline-flex h-11 items-center justify-center rounded-xl bg-emerald-600 px-5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500" type="submit">Sign Up</button>
    </form>

    <p class="mt-6 text-sm text-slate-600">Already have an account? <a class="font-semibold text-emerald-700 hover:text-emerald-800" href="/auth/login.php">Login</a></p>
  </div>
</section>

<?php layout_footer(); ?>
