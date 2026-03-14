<?php
declare(strict_types=1);
require_once __DIR__ . '/../../_layout.php';

require_admin();

layout_header('Admin - Activities', 'admin');

global $supabase;
$items = [];

if ($supabase) {
  $res = $supabase->restGet('activities_public', [
    'select' => 'id,title,activity_date,start_time,location,volunteers_needed,status,joined_count,slots_left',
    'order' => 'activity_date.desc,start_time.desc',
    'limit' => '200',
  ], session_access_token());
  if ($res['ok'] && is_array($res['data'])) $items = $res['data'];
}
?>

<section class="mx-auto max-w-6xl px-4 py-10">
  <div class="flex flex-wrap items-end justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">Activities</h1>
      <p class="mt-2 text-slate-600">Create and manage volunteer activities.</p>
    </div>
    <a href="/admin/activities/new.php" class="inline-flex h-10 items-center rounded-xl bg-emerald-600 px-4 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">New Activity</a>
  </div>

  <div class="mt-8 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
      <table class="min-w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-600">
          <tr>
            <th class="px-4 py-3">Date</th>
            <th class="px-4 py-3">Title</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Joined</th>
            <th class="px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
          <?php if (!$supabase): ?>
            <tr><td class="px-4 py-4" colspan="5">Supabase is not configured.</td></tr>
          <?php elseif (!$items): ?>
            <tr><td class="px-4 py-4" colspan="5">No activities yet.</td></tr>
          <?php else: foreach ($items as $a): ?>
            <tr class="hover:bg-slate-50/60">
              <td class="px-4 py-4 whitespace-nowrap"><?= e((string)$a['activity_date']) ?> <?= e((string)$a['start_time']) ?></td>
              <td class="px-4 py-4">
                <p class="font-semibold text-slate-900"><?= e((string)$a['title']) ?></p>
                <p class="text-xs text-slate-600"><?= e((string)$a['location']) ?></p>
              </td>
              <td class="px-4 py-4 whitespace-nowrap"><?= e(activity_status_label(is_array($a) ? $a : [])) ?></td>
              <td class="px-4 py-4 whitespace-nowrap"><?= e((string)$a['joined_count']) ?> / <?= e((string)$a['volunteers_needed']) ?></td>
              <td class="px-4 py-4 whitespace-nowrap">
                <div class="flex flex-wrap gap-2">
                  <a class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold hover:bg-slate-50" href="/admin/activities/edit.php?id=<?= e((string)$a['id']) ?>">Edit</a>
                  <a class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold hover:bg-slate-50" href="/admin/activities/volunteers.php?id=<?= e((string)$a['id']) ?>">Volunteers</a>
                </div>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<?php layout_footer(); ?>
