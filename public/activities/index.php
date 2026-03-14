<?php
declare(strict_types=1);
require_once __DIR__ . '/../_layout.php';

layout_header('Activities', 'activities');

global $supabase;
$today = (new DateTimeImmutable('now'))->format('Y-m-d');
$items = [];

if ($supabase) {
  $res = $supabase->restGet('activities_public', [
    'select' => 'id,title,description,activity_date,start_time,end_time,location,volunteers_needed,status,joined_count,slots_left',
    'activity_date' => 'gte.' . $today,
    'order' => 'activity_date.asc,start_time.asc',
    'limit' => '100',
  ], session_access_token());
  if ($res['ok'] && is_array($res['data'])) $items = $res['data'];
}
?>

<section class="mx-auto max-w-6xl px-4 py-10">
  <div class="flex flex-wrap items-end justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">Volunteer Activities</h1>
      <p class="mt-2 text-slate-600">Browse upcoming programs and sign up to help.</p>
    </div>
    <?php if (!is_logged_in()): ?>
      <a href="/auth/login.php" class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold hover:bg-slate-50">Login to Join</a>
    <?php endif; ?>
  </div>

  <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    <?php if (!$supabase): ?>
      <div class="rounded-2xl border border-slate-200 bg-white p-6 text-sm text-slate-700">
        Set <code class="rounded bg-slate-100 px-2 py-0.5">SUPABASE_URL</code> and <code class="rounded bg-slate-100 px-2 py-0.5">SUPABASE_ANON_KEY</code> in <code class="rounded bg-slate-100 px-2 py-0.5">.env</code>.
      </div>
    <?php elseif (!$items): ?>
      <div class="rounded-2xl border border-slate-200 bg-white p-6 text-sm text-slate-700">No activities found.</div>
    <?php else: foreach ($items as $a):
      $label = activity_status_label(is_array($a) ? $a : []);
      $badge = $label === 'Open' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700';
    ?>
      <a href="/activities/show.php?id=<?= e((string)$a['id']) ?>" class="group rounded-2xl border border-slate-200 bg-white p-5 hover:border-emerald-200 hover:shadow-sm">
        <div class="flex items-center justify-between gap-3">
          <p class="text-xs font-semibold tracking-widest text-slate-500"><?= e((string)$a['activity_date']) ?></p>
          <span class="rounded-full px-2 py-1 text-xs font-semibold <?= $badge ?>"><?= e($label) ?></span>
        </div>
        <h2 class="mt-2 text-lg font-semibold group-hover:text-emerald-700"><?= e((string)$a['title']) ?></h2>
        <p class="mt-2 line-clamp-3 text-sm text-slate-700"><?= e((string)$a['description']) ?></p>
        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-sm">
          <p class="text-slate-600"><?= e((string)$a['start_time']) ?><?= ($a['end_time'] ?? '') ? ' - ' . e((string)$a['end_time']) : '' ?></p>
          <span class="rounded-full bg-slate-100 px-2 py-1 text-xs text-slate-700"><?= e((string)$a['slots_left']) ?> slots left</span>
        </div>
        <p class="mt-3 text-sm text-slate-600"><span class="font-semibold text-slate-700">Location:</span> <?= e((string)$a['location']) ?></p>
      </a>
    <?php endforeach; endif; ?>
  </div>
</section>

<?php layout_footer(); ?>
