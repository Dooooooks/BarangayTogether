<?php
declare(strict_types=1);
require_once __DIR__ . '/../../_layout.php';

require_admin();

global $supabase;
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) redirect('/admin/activities/index.php');

$activity = null;
$rows = [];

if ($supabase) {
  $aRes = $supabase->restGet('activities', [
    'select' => 'id,title,activity_date,start_time,location,status',
    'id' => 'eq.' . $id,
    'limit' => '1',
  ], session_access_token());
  if ($aRes['ok'] && is_array($aRes['data']) && isset($aRes['data'][0])) $activity = $aRes['data'][0];

  $vRes = $supabase->restGet('activity_participants', [
    'select' => 'status,joined_at,profiles(full_name,email,contact_number,address)',
    'activity_id' => 'eq.' . $id,
    'status' => 'eq.joined',
    'order' => 'joined_at.asc',
    'limit' => '500',
  ], session_access_token());
  if ($vRes['ok'] && is_array($vRes['data'])) $rows = $vRes['data'];
}

layout_header('Admin - Volunteers', 'admin');
?>

<section class="mx-auto max-w-6xl px-4 py-10">
  <div class="flex flex-wrap items-end justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold tracking-tight">Volunteers</h1>
      <?php if ($activity): ?>
        <p class="mt-2 text-slate-600"><?= e((string)$activity['title']) ?> • <?= e((string)$activity['activity_date']) ?> <?= e((string)$activity['start_time']) ?> • <?= e((string)$activity['location']) ?></p>
      <?php else: ?>
        <p class="mt-2 text-slate-600">Volunteer list per activity.</p>
      <?php endif; ?>
    </div>
    <a href="/admin/activities/index.php" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold hover:bg-slate-50">Back</a>
  </div>

  <div class="mt-8 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
      <table class="min-w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-600">
          <tr>
            <th class="px-4 py-3">Name</th>
            <th class="px-4 py-3">Contact</th>
            <th class="px-4 py-3">Address</th>
            <th class="px-4 py-3">Joined at</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
          <?php if (!$supabase): ?>
            <tr><td class="px-4 py-4" colspan="4">Supabase is not configured.</td></tr>
          <?php elseif (!$rows): ?>
            <tr><td class="px-4 py-4" colspan="4">No volunteers yet.</td></tr>
          <?php else: foreach ($rows as $r):
            $p = $r['profiles'] ?? null;
            if (!is_array($p)) $p = [];
          ?>
            <tr class="hover:bg-slate-50/60">
              <td class="px-4 py-4">
                <p class="font-semibold text-slate-900"><?= e((string)($p['full_name'] ?? '')) ?></p>
                <p class="text-xs text-slate-600"><?= e((string)($p['email'] ?? '')) ?></p>
              </td>
              <td class="px-4 py-4 whitespace-nowrap"><?= e((string)($p['contact_number'] ?? '')) ?></td>
              <td class="px-4 py-4"><?= e((string)($p['address'] ?? '')) ?></td>
              <td class="px-4 py-4 whitespace-nowrap"><?= e((string)($r['joined_at'] ?? '')) ?></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<?php layout_footer(); ?>
