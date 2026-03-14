<?php
declare(strict_types=1);
require_once __DIR__ . '/_layout.php';

require_login();

global $supabase;
$profile = current_user_profile();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_verify();
  if (!$supabase || !$profile) {
    flash_set('error', 'Unable to update profile.');
    redirect('/profile.php');
  }

  $fullName = trim((string)($_POST['full_name'] ?? ''));
  $contact = trim((string)($_POST['contact_number'] ?? ''));
  $address = trim((string)($_POST['address'] ?? ''));

  $uid = (string)($profile['id'] ?? '');
  if ($uid === '') {
    flash_set('error', 'Invalid session.');
    auth_clear_session();
    redirect('/auth/login.php');
  }

  $res = $supabase->restPatch('profiles?id=eq.' . rawurlencode($uid), [
    'full_name' => $fullName,
    'contact_number' => $contact,
    'address' => $address,
  ], session_access_token());

  if ($res['ok']) {
    flash_set('success', 'Profile updated.');
  } else {
    flash_set('error', 'Unable to update profile.');
  }
  redirect('/profile.php');
}

layout_header('Profile', 'profile');
?>

<section class="mx-auto max-w-3xl px-4 py-10">
  <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">Profile</h1>
  <p class="mt-2 text-slate-600">Keep your contact details updated for volunteer coordination.</p>

  <div class="mt-8 rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
    <?php if (!$supabase): ?>
      <p class="text-sm text-slate-700">Supabase is not configured.</p>
    <?php elseif (!$profile): ?>
      <p class="text-sm text-slate-700">Unable to load your profile.</p>
    <?php else: ?>
      <form method="post" class="grid gap-4">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>" />

        <div class="grid gap-4 sm:grid-cols-2">
          <label class="grid gap-1.5">
            <span class="text-sm font-semibold text-slate-800">Full name</span>
            <input name="full_name" class="h-11 rounded-xl border border-slate-300 bg-white px-3 text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/15" value="<?= e((string)($profile['full_name'] ?? '')) ?>" required />
          </label>
          <label class="grid gap-1.5">
            <span class="text-sm font-semibold text-slate-800">Contact number</span>
            <input name="contact_number" class="h-11 rounded-xl border border-slate-300 bg-white px-3 text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/15" value="<?= e((string)($profile['contact_number'] ?? '')) ?>" />
          </label>
        </div>

        <label class="grid gap-1.5">
          <span class="text-sm font-semibold text-slate-800">Address</span>
          <input name="address" class="h-11 rounded-xl border border-slate-300 bg-white px-3 text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/15" value="<?= e((string)($profile['address'] ?? '')) ?>" />
        </label>

        <div class="grid gap-2 rounded-2xl bg-slate-50 p-4 text-sm text-slate-700">
          <p><span class="font-semibold">Email:</span> <?= e((string)($profile['email'] ?? '')) ?></p>
          <p><span class="font-semibold">Role:</span> <?= e((string)($profile['role'] ?? 'resident')) ?></p>
          <p><span class="font-semibold">Status:</span> <?= e((string)($profile['status'] ?? 'active')) ?></p>
        </div>

        <button class="mt-2 inline-flex h-11 items-center justify-center rounded-xl bg-emerald-600 px-5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500" type="submit">Save Changes</button>
      </form>
    <?php endif; ?>
  </div>
</section>

<?php layout_footer(); ?>
