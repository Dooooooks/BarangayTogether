<?php
declare(strict_types=1);
require_once __DIR__ . '/_layout.php';

layout_header('BarangayTogether', 'home');

global $supabase;
$featured = [];
$today = (new DateTimeImmutable('now'))->format('Y-m-d');
if ($supabase) {
  $res = $supabase->restGet('activities_public', [
    'select' => 'id,title,description,activity_date,start_time,location,volunteers_needed,joined_count,slots_left,status',
    'activity_date' => 'gte.' . $today,
    'status' => 'eq.open',
    'order' => 'activity_date.asc,start_time.asc',
    'limit' => '3',
  ], session_access_token());
  if ($res['ok'] && is_array($res['data'])) $featured = $res['data'];
}
?>

<section class="bg-gradient-to-b from-sky-100 via-sky-50 to-white">
  <div class="mx-auto max-w-6xl px-4 py-10 sm:py-14">
    <div class="grid items-center gap-10 lg:grid-cols-2">
      <div>
        <p class="text-xs font-semibold tracking-widest text-emerald-700">BARANGAY VOLUNTEERS</p>
        <h1 class="mt-3 text-balance text-3xl font-semibold tracking-tight text-slate-900 sm:text-5xl">
          Make a Difference in Our Barangay
        </h1>
        <p class="mt-4 max-w-xl text-pretty text-slate-700">
          BarangayTogether connects residents with community volunteer activities like clean-up drives, tree planting, and disaster preparedness.
          Join an activity, bring a friend, and help improve the barangay.
        </p>
        <div class="mt-6 flex flex-wrap items-center gap-3">
          <a href="/activities/index.php" class="inline-flex h-11 items-center justify-center rounded-xl bg-emerald-600 px-5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">Browse Activities</a>
          <?php if (!is_logged_in()): ?>
            <a href="/auth/register.php" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 text-sm font-semibold text-slate-900 hover:bg-slate-50">Create Account</a>
          <?php else: ?>
            <a href="/activities/my.php" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 text-sm font-semibold text-slate-900 hover:bg-slate-50">My Activities</a>
          <?php endif; ?>
        </div>
      </div>

      <div class="relative">
        <div class="aspect-[4/3] overflow-hidden rounded-3xl border border-sky-200 bg-white shadow-sm">
          <img src="/imgs/Heropage.jpg" alt="BarangayTogether community" class="h-full w-full object-cover" loading="lazy" />
        </div>
        <div class="pointer-events-none absolute -bottom-6 -left-6 h-24 w-24 rounded-3xl bg-emerald-200/60 blur-xl"></div>
        <div class="pointer-events-none absolute -top-8 -right-8 h-28 w-28 rounded-3xl bg-sky-200/70 blur-xl"></div>
      </div>
    </div>
  </div>
</section>

<section class="mx-auto max-w-6xl px-4 py-12">
  <div class="grid gap-10 lg:grid-cols-2">
    <div>
      <h2 class="text-2xl font-semibold tracking-tight text-slate-900">Welcome to BarangayTogether</h2>
      <p class="mt-3 text-slate-700">
        This platform helps barangay officials announce volunteer programs and lets residents sign up easily.
        Participation records are tracked so the barangay can coordinate volunteers quickly.
      </p>
      <div class="mt-6 grid grid-cols-2 gap-4">
        <div class="aspect-[4/3] overflow-hidden rounded-2xl border border-slate-200 bg-slate-100">
          <img src="/imgs/image1.jpg" alt="Barangay activity" class="h-full w-full object-cover" loading="lazy" />
        </div>
        <div class="aspect-[4/3] overflow-hidden rounded-2xl border border-slate-200 bg-slate-100">
          <img src="/imgs/image2.jpg" alt="Barangay activity" class="h-full w-full object-cover" loading="lazy" />
        </div>
        <div class="aspect-[4/3] overflow-hidden rounded-2xl border border-slate-200 bg-slate-100">
          <img src="/imgs/image3.jpg" alt="Barangay activity" class="h-full w-full object-cover" loading="lazy" />
        </div>
        <div class="aspect-[4/3] overflow-hidden rounded-2xl border border-slate-200 bg-slate-100">
          <img src="/imgs/image4.jpg" alt="Barangay activity" class="h-full w-full object-cover" loading="lazy" />
        </div>
      </div>
    </div>

    <div>
      <h2 class="text-2xl font-semibold tracking-tight text-slate-900">Upcoming Activities</h2>
      <p class="mt-3 text-slate-700">A few open activities you can join right now.</p>

      <div class="mt-6 grid gap-4">
        <?php if (!$supabase): ?>
          <div class="rounded-2xl border border-slate-200 bg-white p-6 text-sm text-slate-700">
            Set <code class="rounded bg-slate-100 px-2 py-0.5">SUPABASE_URL</code> and <code class="rounded bg-slate-100 px-2 py-0.5">SUPABASE_ANON_KEY</code> in <code class="rounded bg-slate-100 px-2 py-0.5">.env</code>.
          </div>
        <?php elseif (!$featured): ?>
          <div class="rounded-2xl border border-slate-200 bg-white p-6 text-sm text-slate-700">No open activities yet.</div>
        <?php else: foreach ($featured as $a): ?>
          <a href="/activities/show.php?id=<?= e((string)$a['id']) ?>" class="group rounded-2xl border border-slate-200 bg-white p-5 hover:border-emerald-200 hover:shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-2">
              <p class="text-xs font-semibold tracking-widest text-slate-500"><?= e((string)$a['activity_date']) ?> • <?= e((string)$a['start_time']) ?></p>
              <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700"><?= e((string)$a['slots_left']) ?> slots left</span>
            </div>
            <h3 class="mt-2 text-lg font-semibold group-hover:text-emerald-700"><?= e((string)$a['title']) ?></h3>
            <p class="mt-2 line-clamp-2 text-sm text-slate-700"><?= e((string)$a['description']) ?></p>
            <p class="mt-3 text-sm text-slate-600"><span class="font-semibold">Location:</span> <?= e((string)$a['location']) ?></p>
          </a>
        <?php endforeach; endif; ?>
        <a href="/activities/index.php" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold hover:bg-slate-50">View All Activities</a>
      </div>
    </div>
  </div>
</section>

<section class="bg-slate-100">
  <div class="mx-auto max-w-6xl px-4 py-12">
    <div class="flex items-end justify-between gap-6">
      <div>
        <h2 class="text-2xl font-semibold tracking-tight text-slate-900">Volunteer FAQs</h2>
        <p class="mt-2 text-slate-700">Quick answers for first-time volunteers.</p>
      </div>
    </div>

    <div class="mt-6 grid gap-3">
      <?php
        $faqs = [
          ['HOW TO VOLUNTEER', 'On the top right of the website, click the Sign Up button to create your account. Make sure to choose "Sign Up as Volunteer." Then, fill in all the required information.'],
          ['WHO CAN JOIN', 'Volunteering activities are open to everyone, including individuals who are not residents of the barangay. Anyone who is willing to contribute their time and effort to help the community is encouraged to participate.'],
          ['AGE REQUIREMENT', '18 years old and above are allowed to volunteer independently. Participants below 18 years old are also welcome to join, but they must be accompanied by a parent or legal guardian.'],
          ['VOLUNTEER SCHEDULE', 'Volunteer activities are generally scheduled from Monday to Saturday, depending on the programs or projects organized by the barangay.'],
          ['ACTIVITY LOCATION', 'Most volunteer activities will take place at the Barangay 806 Barangay Hall. However, some activities may also be conducted in different locations within the barangay depending on the needs of the community program or project.'],
          ['ARRIVAL TIME', 'Volunteers may arrive as early as 8:00 AM. The exact time may vary depending on the specific activity scheduled for the day.'],
          ['VOLUNTEER DURATION', 'A volunteering session may last around 2-3 hours, which is usually acceptable for most activities. However, many activities such as community clean-ups, organizing events, or other physical work typically take around 3-4 hours to complete. The required time may vary depending on the nature of the task.'],
          ['NUMBER OF VOLUNTEERS', 'The number of volunteers accepted for each activity may vary depending on the type of project and the resources available for that specific event.'],
          ['VOLUNTEER CERTIFICATION', 'After completing a volunteer activity, participants may request a certificate or documentation from the barangay confirming their participation.'],
        ];
      ?>
      <?php foreach ($faqs as [$q, $a]): ?>
        <details class="group rounded-2xl border border-slate-200 bg-white px-5 py-4">
          <summary class="flex cursor-pointer list-none items-center justify-between gap-4">
            <span class="text-sm font-semibold text-slate-900"><?= e($q) ?></span>
            <span class="grid size-8 place-items-center rounded-xl border border-slate-200 bg-slate-50 text-slate-700 group-open:bg-emerald-50 group-open:text-emerald-700">+</span>
          </summary>
          <p class="mt-3 text-sm text-slate-700"><?= e($a) ?></p>
        </details>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php layout_footer(); ?>
