<?php
declare(strict_types=1);
require_once __DIR__ . '/../_layout.php';

require_admin();

global $supabase;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_verify();
  if (!$supabase) {
    flash_set('error', 'Supabase is not configured.');
    redirect('/admin/users.php');
  }
  $uid = (string)($_POST['uid'] ?? '');
  $status = (string)($_POST['status'] ?? '');
  $role = (string)($_POST['role'] ?? '');
  if ($uid !== '') {
    $payload = [];
    if (in_array($status, ['active','disabled'], true)) $payload['status'] = $status;
    if (in_array($role, ['resident','admin'], true)) $payload['role'] = $role;
    if ($payload) {
      $u = $supabase->restPatch('profiles?id=eq.' . rawurlencode($uid), $payload, session_access_token());
      if ($u['ok']) flash_set('success', 'User updated.');
      else flash_set('error', 'Unable to update user.');
    }
  }
  redirect('/admin/users.php');
}

$items = [];
if ($supabase) {
  $res = $supabase->restGet('profiles', [
    'select' => 'id,email,full_name,contact_number,address,role,status,created_at',
    'order' => 'created_at.desc',
    'limit' => '300',
  ], session_access_token());
  if ($res['ok'] && is_array($res['data'])) $items = $res['data'];
}

layout_header('Admin - Users', 'admin');
?>

<section class="mx-auto max-w-6xl px-4 py-10">
  <div class="flex flex-wrap items-end justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold tracking-tight">Users</h1>
      <p class="mt-2 text-slate-600">Manage resident accounts (disable if necessary).</p>
    </div>
    <a href="/admin/index.php" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold hover:bg-slate-50">Back</a>
  </div>

  <div class="mt-8 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
      <table class="min-w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-600">
          <tr>
            <th class="px-4 py-3">Name</th>
            <th class="px-4 py-3">Email</th>
            <th class="px-4 py-3">Role</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
          <?php if (!$supabase): ?>
            <tr><td class="px-4 py-4" colspan="5">Supabase is not configured.</td></tr>
          <?php elseif (!$items): ?>
            <tr><td class="px-4 py-4" colspan="5">No users yet.</td></tr>
          <?php else: foreach ($items as $u): ?>
            <tr class="hover:bg-slate-50/60">
              <td class="px-4 py-4">
                <p class="font-semibold text-slate-900"><?= e((string)($u['full_name'] ?? '')) ?></p>
                <p class="text-xs text-slate-600"><?= e((string)($u['contact_number'] ?? '')) ?></p>
              </td>
              <td class="px-4 py-4 whitespace-nowrap"><?= e((string)($u['email'] ?? '')) ?></td>
              <td class="px-4 py-4 whitespace-nowrap"><?= e((string)($u['role'] ?? 'resident')) ?></td>
              <td class="px-4 py-4 whitespace-nowrap"><?= e((string)($u['status'] ?? 'active')) ?></td>
              <td class="px-4 py-4 whitespace-nowrap">
                <form method="post" class="flex flex-wrap items-center gap-2">
                  <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>" />
                  <input type="hidden" name="uid" value="<?= e((string)($u['id'] ?? '')) ?>" />
                  <select name="role" class="h-9 rounded-xl border border-slate-300 bg-white px-2 text-xs">
                    <option value="resident" <?= (($u['role'] ?? '') === 'resident') ? 'selected' : '' ?>>resident</option>
                    <option value="admin" <?= (($u['role'] ?? '') === 'admin') ? 'selected' : '' ?>>admin</option>
                  </select>
                  <select name="status" class="h-9 rounded-xl border border-slate-300 bg-white px-2 text-xs">
                    <option value="active" <?= (($u['status'] ?? '') === 'active') ? 'selected' : '' ?>>active</option>
                    <option value="disabled" <?= (($u['status'] ?? '') === 'disabled') ? 'selected' : '' ?>>disabled</option>
                  </select>
                  <button class="inline-flex h-9 items-center rounded-xl bg-emerald-600 px-3 text-xs font-semibold text-white hover:bg-emerald-500" type="submit">Update</button>
                </form>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<?php layout_footer(); ?>
