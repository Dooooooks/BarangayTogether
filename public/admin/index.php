<?php
declare(strict_types=1);
require_once __DIR__ . '/../_layout.php';

require_admin();

global $supabase;

layout_header('Admin', 'admin');

$stats = [
  'total_users' => 0,
  'total_activities' => 0,
  'total_volunteers' => 0,
];

if ($supabase) {
  $cUsers = $supabase->restCount('profiles', [], session_access_token());
  if ($cUsers !== null) $stats['total_users'] = $cUsers;
  $cActs = $supabase->restCount('activities', [], session_access_token());
  if ($cActs !== null) $stats['total_activities'] = $cActs;
  $cVols = $supabase->restCount('activity_participants', ['status' => 'eq.joined'], session_access_token());
  if ($cVols !== null) $stats['total_volunteers'] = $cVols;
}
?>

<section class="mx-auto max-w-6xl px-4 py-10">
  <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">Admin Dashboard</h1>
  <p class="mt-2 text-slate-600">Create and manage volunteer activities and view volunteers.</p>

  <div class="mt-8 grid gap-4 sm:grid-cols-3">
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
      <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Registered Users</p>
      <p class="mt-2 text-3xl font-semibold tracking-tight"><?= e((string)$stats['total_users']) ?></p>
    </div>
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
      <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Activities</p>
      <p class="mt-2 text-3xl font-semibold tracking-tight"><?= e((string)$stats['total_activities']) ?></p>
    </div>
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
      <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Volunteers Joined</p>
      <p class="mt-2 text-3xl font-semibold tracking-tight"><?= e((string)$stats['total_volunteers']) ?></p>
    </div>
  </div>

  <div class="mt-8 grid gap-4 sm:grid-cols-2">
    <a href="/admin/activities/index.php" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm hover:border-emerald-200 hover:shadow">
      <h2 class="text-lg font-semibold">Manage Activities</h2>
      <p class="mt-2 text-sm text-slate-600">Create, edit, delete, and update activity status.</p>
    </a>
    <a href="/admin/users.php" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm hover:border-emerald-200 hover:shadow">
      <h2 class="text-lg font-semibold">Manage Users</h2>
      <p class="mt-2 text-sm text-slate-600">View profiles and disable accounts if necessary.</p>
    </a>
  </div>
</section>

<?php layout_footer(); ?>
