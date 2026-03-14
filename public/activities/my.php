<?php
declare(strict_types=1);
require_once __DIR__ . '/../_layout.php';

require_login();

layout_header('My Activities', 'my');

global $supabase;
$items = [];

if ($supabase) {
  $res = $supabase->restGet('activity_participants', [
    'select' => 'status,joined_at,activity:activities(id,title,description,activity_date,start_time,end_time,location,status,volunteers_needed)',
    'order' => 'joined_at.desc',
    'limit' => '100',
  ], session_access_token());
  if ($res['ok'] && is_array($res['data'])) $items = $res['data'];
}
?>

<section class="mx-auto max-w-6xl px-4 py-10">
  <div class="flex items-end justify-between gap-6">
    <div>
      <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">My Activities</h1>
      <p class="mt-2 text-slate-600">Your volunteer sign-ups and participation status.</p>
    </div>
    <a href="/activities/index.php" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">Browse Activities</a>
  </div>

  <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    <?php if (!$supabase): ?>
      <div class="rounded-2xl border border-slate-200 bg-white p-6 text-sm text-slate-700">
        Set <code class="rounded bg-slate-100 px-2 py-0.5">SUPABASE_URL</code> and <code class="rounded bg-slate-100 px-2 py-0.5">SUPABASE_ANON_KEY</code> in <code class="rounded bg-slate-100 px-2 py-0.5">.env</code>.
      </div>
    <?php elseif (!$items): ?>
      <div class="rounded-2xl border border-slate-200 bg-white p-6 text-sm text-slate-700">No sign-ups yet.</div>
    <?php else: foreach ($items as $row):
      $a = $row['activity'] ?? null;
      if (!is_array($a)) continue;
      $pStatus = (string)($row['status'] ?? '');
      $badge = $pStatus === 'joined' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700';
    ?>
      <a href="/activities/show.php?id=<?= e((string)$a['id']) ?>" class="group rounded-2xl border border-slate-200 bg-white p-5 hover:border-emerald-200 hover:shadow-sm">
        <div class="flex items-center justify-between gap-3">
          <p class="text-xs font-semibold tracking-widest text-slate-500"><?= e((string)$a['activity_date']) ?></p>
          <span class="rounded-full px-2 py-1 text-xs font-semibold <?= $badge ?>"><?= e($pStatus) ?></span>
        </div>
        <h2 class="mt-2 text-lg font-semibold group-hover:text-emerald-700"><?= e((string)$a['title']) ?></h2>
        <p class="mt-2 line-clamp-3 text-sm text-slate-700"><?= e((string)$a['description']) ?></p>
        <p class="mt-4 text-sm text-slate-600"><span class="font-semibold text-slate-700">Location:</span> <?= e((string)$a['location']) ?></p>
      </a>
    <?php endforeach; endif; ?>
  </div>
</section>

<?php layout_footer(); ?>
