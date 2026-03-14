<?php
declare(strict_types=1);
require_once __DIR__ . '/../../_layout.php';

require_admin();

global $supabase;
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) redirect('/admin/activities/index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_verify();
  if (!$supabase) {
    flash_set('error', 'Supabase is not configured.');
    redirect('/admin/activities/edit.php?id=' . $id);
  }

  $action = (string)($_POST['action'] ?? 'save');
  if ($action === 'delete') {
    $d = $supabase->restDelete('activities?id=eq.' . $id, session_access_token());
    if ($d['ok']) {
      flash_set('success', 'Activity deleted.');
      redirect('/admin/activities/index.php');
    }
    flash_set('error', 'Unable to delete activity.');
    redirect('/admin/activities/edit.php?id=' . $id);
  }

  $payload = [
    'title' => trim((string)($_POST['title'] ?? '')),
    'description' => trim((string)($_POST['description'] ?? '')),
    'activity_date' => (string)($_POST['activity_date'] ?? ''),
    'start_time' => (string)($_POST['start_time'] ?? ''),
    'end_time' => (string)($_POST['end_time'] ?? ''),
    'location' => trim((string)($_POST['location'] ?? '')),
    'volunteers_needed' => (int)($_POST['volunteers_needed'] ?? 0),
    'status' => (string)($_POST['status'] ?? 'open'),
  ];
  if ($payload['end_time'] === '') $payload['end_time'] = null;

  $u = $supabase->restPatch('activities?id=eq.' . $id, $payload, session_access_token());
  if ($u['ok']) {
    flash_set('success', 'Activity updated.');
    redirect('/admin/activities/edit.php?id=' . $id);
  }
  flash_set('error', 'Unable to update activity.');
  redirect('/admin/activities/edit.php?id=' . $id);
}

$activity = null;
if ($supabase) {
  $res = $supabase->restGet('activities', [
    'select' => 'id,title,description,activity_date,start_time,end_time,location,volunteers_needed,status',
    'id' => 'eq.' . $id,
    'limit' => '1',
  ], session_access_token());
  if ($res['ok'] && is_array($res['data']) && isset($res['data'][0])) $activity = $res['data'][0];
}

layout_header('Admin - Edit Activity', 'admin');
?>

<section class="mx-auto max-w-3xl px-4 py-10">
  <div class="flex items-end justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold tracking-tight">Edit Activity</h1>
      <p class="mt-2 text-slate-600">Update details, status, or delete the activity.</p>
    </div>
    <a href="/admin/activities/index.php" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold hover:bg-slate-50">Back</a>
  </div>

  <?php if (!$supabase): ?>
    <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 text-sm text-slate-700">Supabase is not configured.</div>
  <?php elseif (!$activity): ?>
    <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 text-sm text-slate-700">Activity not found.</div>
  <?php else: ?>
    <form method="post" class="mt-8 grid gap-4 rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
      <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>" />

      <label class="grid gap-1.5">
        <span class="text-sm font-semibold text-slate-800">Title</span>
        <input name="title" class="h-11 rounded-xl border border-slate-300 bg-white px-3 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/15" value="<?= e((string)$activity['title']) ?>" required />
      </label>

      <label class="grid gap-1.5">
        <span class="text-sm font-semibold text-slate-800">Description</span>
        <textarea name="description" class="min-h-28 rounded-xl border border-slate-300 bg-white px-3 py-2 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/15" required><?= e((string)$activity['description']) ?></textarea>
      </label>

      <div class="grid gap-4 sm:grid-cols-2">
        <label class="grid gap-1.5">
          <span class="text-sm font-semibold text-slate-800">Date</span>
          <input name="activity_date" type="date" class="h-11 rounded-xl border border-slate-300 bg-white px-3 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/15" value="<?= e((string)$activity['activity_date']) ?>" required />
        </label>
        <label class="grid gap-1.5">
          <span class="text-sm font-semibold text-slate-800">Volunteers needed</span>
          <input name="volunteers_needed" type="number" min="1" class="h-11 rounded-xl border border-slate-300 bg-white px-3 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/15" value="<?= e((string)$activity['volunteers_needed']) ?>" required />
        </label>
      </div>

      <div class="grid gap-4 sm:grid-cols-2">
        <label class="grid gap-1.5">
          <span class="text-sm font-semibold text-slate-800">Start time</span>
          <input name="start_time" type="time" class="h-11 rounded-xl border border-slate-300 bg-white px-3 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/15" value="<?= e((string)$activity['start_time']) ?>" required />
        </label>
        <label class="grid gap-1.5">
          <span class="text-sm font-semibold text-slate-800">End time (optional)</span>
          <input name="end_time" type="time" class="h-11 rounded-xl border border-slate-300 bg-white px-3 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/15" value="<?= e((string)($activity['end_time'] ?? '')) ?>" />
        </label>
      </div>

      <label class="grid gap-1.5">
        <span class="text-sm font-semibold text-slate-800">Location</span>
        <input name="location" class="h-11 rounded-xl border border-slate-300 bg-white px-3 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/15" value="<?= e((string)$activity['location']) ?>" required />
      </label>

      <label class="grid gap-1.5">
        <span class="text-sm font-semibold text-slate-800">Status</span>
        <select name="status" class="h-11 rounded-xl border border-slate-300 bg-white px-3 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/15">
          <?php
            $labels = ['open' => 'Open', 'closed' => 'Closed', 'cancelled' => 'Cancelled', 'done' => 'Completed'];
            foreach ($labels as $s => $label):
          ?>
            <option value="<?= e($s) ?>" <?= ($activity['status'] === $s) ? 'selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
      </label>

      <div class="mt-2 flex flex-wrap items-center gap-3">
        <button class="inline-flex h-11 items-center justify-center rounded-xl bg-emerald-600 px-5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500" type="submit">Save</button>
        <button name="action" value="delete" class="inline-flex h-11 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 px-5 text-sm font-semibold text-rose-700 hover:bg-rose-100" type="submit" onclick="return confirm('Delete this activity?')">Delete</button>
      </div>
    </form>
  <?php endif; ?>
</section>

<?php layout_footer(); ?>
