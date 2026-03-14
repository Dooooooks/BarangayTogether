<?php
declare(strict_types=1);
require_once __DIR__ . '/../_layout.php';

global $supabase;
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) redirect('/activities/index.php');

$activity = null;
$myParticipation = null;

if ($supabase) {
  $res = $supabase->restGet('activities_public', [
    'select' => 'id,title,description,activity_date,start_time,end_time,location,volunteers_needed,status,joined_count,slots_left',
    'id' => 'eq.' . $id,
    'limit' => '1',
  ], session_access_token());
  if ($res['ok'] && is_array($res['data']) && isset($res['data'][0])) $activity = $res['data'][0];

  if (is_logged_in()) {
    $pRes = $supabase->restGet('activity_participants', [
      'select' => 'status,joined_at',
      'activity_id' => 'eq.' . $id,
      'limit' => '1',
    ], session_access_token());
    if ($pRes['ok'] && is_array($pRes['data']) && isset($pRes['data'][0])) $myParticipation = $pRes['data'][0];
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_verify();
  require_login();
  if (!$supabase) {
    flash_set('error', 'Supabase is not configured.');
    redirect('/activities/show.php?id=' . $id);
  }

  $action = (string)($_POST['action'] ?? '');
  if ($action === 'join') {
    $r = $supabase->rpc('join_activity', ['p_activity_id' => $id], session_access_token());
    if ($r['ok']) flash_set('success', 'You joined the activity.');
    else flash_set('error', 'Unable to join: ' . (is_array($r['data']) ? (string)($r['data']['message'] ?? 'Error') : 'Error'));
  } elseif ($action === 'cancel') {
    $r = $supabase->rpc('cancel_activity', ['p_activity_id' => $id], session_access_token());
    if ($r['ok']) flash_set('success', 'Participation cancelled.');
    else flash_set('error', 'Unable to cancel: ' . (is_array($r['data']) ? (string)($r['data']['message'] ?? 'Error') : 'Error'));
  }
  redirect('/activities/show.php?id=' . $id);
}

layout_header(($activity['title'] ?? 'Activity') ?: 'Activity', 'activities');

?>

<section class="mx-auto max-w-4xl px-4 py-10">
  <?php if (!$supabase): ?>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 text-sm text-slate-700">
      Set <code class="rounded bg-slate-100 px-2 py-0.5">SUPABASE_URL</code> and <code class="rounded bg-slate-100 px-2 py-0.5">SUPABASE_ANON_KEY</code> in <code class="rounded bg-slate-100 px-2 py-0.5">.env</code>.
    </div>
  <?php elseif (!$activity): ?>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 text-sm text-slate-700">Activity not found.</div>
  <?php else:
    $isOpen = ($activity['status'] ?? '') === 'open';
    $isJoined = $myParticipation && ($myParticipation['status'] ?? '') === 'joined';
    $canJoin = $isOpen && (int)($activity['slots_left'] ?? 0) > 0 && !$isJoined;
    $label = activity_status_label(is_array($activity) ? $activity : []);
  ?>
    <a href="/activities/index.php" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">← Back to Activities</a>

    <div class="mt-4 rounded-3xl border border-slate-200 bg-white p-7">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <p class="text-xs font-semibold tracking-widest text-slate-500"><?= e((string)$activity['activity_date']) ?> • <?= e((string)$activity['start_time']) ?><?= ($activity['end_time'] ?? '') ? ' - ' . e((string)$activity['end_time']) : '' ?></p>
          <h1 class="mt-2 text-2xl font-semibold tracking-tight sm:text-3xl"><?= e((string)$activity['title']) ?></h1>
          <p class="mt-3 text-slate-700"><?= e((string)$activity['description']) ?></p>
        </div>
        <div class="flex flex-col gap-2 rounded-2xl bg-slate-50 p-4 text-sm">
          <p><span class="font-semibold">Location:</span> <?= e((string)$activity['location']) ?></p>
          <p><span class="font-semibold">Needed:</span> <?= e((string)$activity['volunteers_needed']) ?></p>
          <p><span class="font-semibold">Joined:</span> <?= e((string)$activity['joined_count']) ?></p>
          <p><span class="font-semibold">Slots left:</span> <?= e((string)$activity['slots_left']) ?></p>
          <p><span class="font-semibold">Status:</span> <?= e($label) ?></p>
        </div>
      </div>

      <div class="mt-6 flex flex-wrap items-center gap-3">
        <?php if (!is_logged_in()): ?>
          <a href="/auth/login.php" class="inline-flex h-11 items-center justify-center rounded-xl bg-emerald-600 px-5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">Login to Join</a>
        <?php else: ?>
          <?php if ($canJoin): ?>
            <form method="post" class="inline">
              <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>" />
              <input type="hidden" name="action" value="join" />
              <button class="inline-flex h-11 items-center justify-center rounded-xl bg-emerald-600 px-5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500" type="submit">Join Activity</button>
            </form>
          <?php elseif ($isJoined): ?>
            <span class="inline-flex h-11 items-center rounded-xl bg-emerald-50 px-5 text-sm font-semibold text-emerald-700">You are signed up</span>
            <form method="post" class="inline">
              <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>" />
              <input type="hidden" name="action" value="cancel" />
              <button class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold hover:bg-slate-50" type="submit">Cancel</button>
            </form>
          <?php else: ?>
            <span class="inline-flex h-11 items-center rounded-xl bg-slate-100 px-5 text-sm font-semibold text-slate-700">Not available to join</span>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</section>

<?php layout_footer(); ?>
