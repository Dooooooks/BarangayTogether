<?php
declare(strict_types=1);
require_once __DIR__ . '/_layout.php';

layout_header('Volunteers in the Past', 'past');

global $supabase;
$items = [];
$today = (new DateTimeImmutable('now'))->format('Y-m-d');

if ($supabase) {
  $res = $supabase->restGet('activities_public', [
    'select' => 'id,title,description,activity_date,start_time,end_time,location,volunteers_needed,status,joined_count',
    'or' => sprintf('(activity_date.lt.%s,status.eq.done)', $today),
    'order' => 'activity_date.desc,start_time.desc',
    'limit' => '30',
  ], session_access_token());
  if ($res['ok'] && is_array($res['data'])) $items = $res['data'];
}
?>

<section class="mx-auto max-w-6xl px-4 py-10">
  <div class="flex items-end justify-between gap-6">
    <div>
      <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">Volunteers in the Past</h1>
      <p class="mt-2 text-slate-600">Completed and previous community programs.</p>
    </div>
    <a href="/activities/index.php" class="hidden rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold hover:bg-slate-50 sm:inline">Browse Upcoming</a>
  </div>

  <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    <?php if (!$supabase): ?>
      <div class="rounded-2xl border border-slate-200 bg-white p-6 text-sm text-slate-700">
        Set <code class="rounded bg-slate-100 px-2 py-0.5">SUPABASE_URL</code> and <code class="rounded bg-slate-100 px-2 py-0.5">SUPABASE_ANON_KEY</code> in <code class="rounded bg-slate-100 px-2 py-0.5">.env</code>.
      </div>
    <?php elseif (!$items): ?>
      <div class="rounded-2xl border border-slate-200 bg-white p-6 text-sm text-slate-700">No past activities yet.</div>
    <?php else: foreach ($items as $a): ?>
      <a class="group rounded-2xl border border-slate-200 bg-white p-5 hover:border-emerald-200 hover:shadow-sm" href="/activities/show.php?id=<?= e((string)$a['id']) ?>">
        <div class="flex items-center justify-between gap-4">
          <p class="text-xs font-semibold tracking-widest text-slate-500"><?= e((string)$a['activity_date']) ?></p>
          <span class="rounded-full bg-slate-100 px-2 py-1 text-xs text-slate-700"><?= e((string)$a['joined_count']) ?> joined</span>
        </div>
        <h2 class="mt-2 text-lg font-semibold group-hover:text-emerald-700"><?= e((string)$a['title']) ?></h2>
        <p class="mt-2 line-clamp-3 text-sm text-slate-600"><?= e((string)$a['description']) ?></p>
        <p class="mt-4 text-sm text-slate-700"><span class="font-semibold">Location:</span> <?= e((string)$a['location']) ?></p>
      </a>
    <?php endforeach; endif; ?>
  </div>
</section>

<?php layout_footer(); ?>
