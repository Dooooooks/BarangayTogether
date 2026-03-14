<?php
declare(strict_types=1);
require_once __DIR__ . '/../../_layout.php';

require_admin();

global $supabase;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_verify();
  if (!$supabase) {
    flash_set('error', 'Supabase is not configured.');
    redirect('/admin/activities/new.php');
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
  if ($payload['end_time'] === '') unset($payload['end_time']);

  $res = $supabase->restPost('activities', $payload, session_access_token());
  if ($res['ok']) {
    flash_set('success', 'Activity created.');
    redirect('/admin/activities/index.php');
  }
  flash_set('error', 'Unable to create activity.');
  redirect('/admin/activities/new.php');
}

layout_header('Admin - New Activity', 'admin');
?>

<section class="mx-auto max-w-3xl px-4 py-10">
  <div class="flex items-end justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold tracking-tight">New Activity</h1>
      <p class="mt-2 text-slate-600">Post a volunteer program for residents.</p>
    </div>
    <a href="/admin/activities/index.php" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold hover:bg-slate-50">Back</a>
  </div>

  <form method="post" class="mt-8 grid gap-4 rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>" />

    <label class="grid gap-1.5">
      <span class="text-sm font-semibold text-slate-800">Title</span>
      <input name="title" class="h-11 rounded-xl border border-slate-300 bg-white px-3 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/15" required />
    </label>

    <label class="grid gap-1.5">
      <span class="text-sm font-semibold text-slate-800">Description</span>
      <textarea name="description" class="min-h-28 rounded-xl border border-slate-300 bg-white px-3 py-2 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/15" required></textarea>
    </label>

    <div class="grid gap-4 sm:grid-cols-2">
      <label class="grid gap-1.5">
        <span class="text-sm font-semibold text-slate-800">Date</span>
        <input name="activity_date" type="date" class="h-11 rounded-xl border border-slate-300 bg-white px-3 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/15" required />
      </label>
      <label class="grid gap-1.5">
        <span class="text-sm font-semibold text-slate-800">Volunteers needed</span>
        <input name="volunteers_needed" type="number" min="1" class="h-11 rounded-xl border border-slate-300 bg-white px-3 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/15" required />
      </label>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
      <label class="grid gap-1.5">
        <span class="text-sm font-semibold text-slate-800">Start time</span>
        <input name="start_time" type="time" class="h-11 rounded-xl border border-slate-300 bg-white px-3 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/15" required />
      </label>
      <label class="grid gap-1.5">
        <span class="text-sm font-semibold text-slate-800">End time (optional)</span>
        <input name="end_time" type="time" class="h-11 rounded-xl border border-slate-300 bg-white px-3 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/15" />
      </label>
    </div>

    <label class="grid gap-1.5">
      <span class="text-sm font-semibold text-slate-800">Location</span>
      <input name="location" class="h-11 rounded-xl border border-slate-300 bg-white px-3 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/15" required />
    </label>

    <label class="grid gap-1.5">
      <span class="text-sm font-semibold text-slate-800">Status</span>
      <select name="status" class="h-11 rounded-xl border border-slate-300 bg-white px-3 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/15">
        <option value="open">Open</option>
        <option value="closed">Closed</option>
        <option value="cancelled">Cancelled</option>
        <option value="done">Completed</option>
      </select>
    </label>

    <button class="mt-2 inline-flex h-11 items-center justify-center rounded-xl bg-emerald-600 px-5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500" type="submit">Create</button>
  </form>
</section>

<?php layout_footer(); ?>
