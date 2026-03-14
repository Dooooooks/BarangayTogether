<?php
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';

function layout_header(string $title, string $active = ''): void {
  $flash = flash_get();
  $loggedIn = is_logged_in();
  $profile = $loggedIn ? current_user_profile() : null;
  $brand = 'BarangayTogether';
  ?>
  <!doctype html>
  <html lang="en">
    <head>
      <meta charset="utf-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1" />
      <title><?= e($title) ?></title>
      <link rel="stylesheet" href="/assets/app.css" />
    </head>
    <body class="min-h-dvh bg-slate-50 text-slate-900">
      <header class="sticky top-0 z-50 border-b border-slate-200 bg-white/85 backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-3 px-4 py-3">
          <a href="/" class="group inline-flex items-center gap-2">
            <span class="grid size-9 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
              <img src="/icon/Icon.png" alt="BarangayTogether" class="h-full w-full scale-125 object-cover" />
            </span>
            <div class="leading-tight">
              <p class="text-sm font-semibold tracking-tight"><?= e($brand) ?></p>
              <p class="text-xs text-slate-500">Volunteer Management</p>
            </div>
          </a>

            <nav class="hidden items-center gap-6 text-sm font-medium text-slate-700 md:flex">
              <a class="<?= $active === 'home' ? 'text-emerald-700' : 'hover:text-emerald-700' ?>" href="/">Home</a>
              <a class="<?= $active === 'activities' ? 'text-emerald-700' : 'hover:text-emerald-700' ?>" href="/activities/index.php">Activities</a>
              <a class="<?= $active === 'past' ? 'text-emerald-700' : 'hover:text-emerald-700' ?>" href="/past.php">Volunteers in the Past</a>
              <?php if ($loggedIn): ?>
                <a class="<?= $active === 'my' ? 'text-emerald-700' : 'hover:text-emerald-700' ?>" href="/activities/my.php">My Activities</a>
                <a class="<?= $active === 'profile' ? 'text-emerald-700' : 'hover:text-emerald-700' ?>" href="/profile.php">Profile</a>
                <?php if (($profile['role'] ?? '') === 'admin'): ?>
                  <a class="<?= $active === 'admin' ? 'text-emerald-700' : 'hover:text-emerald-700' ?>" href="/admin/index.php">Admin</a>
                <?php endif; ?>
              <?php endif; ?>
            </nav>

              <div class="flex items-center gap-2">
              <?php if ($loggedIn): ?>
                <span class="hidden rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-700 sm:inline">Hi, <?= e((string)(($profile['full_name'] ?? '') ?: 'Volunteer')) ?></span>
                <a class="inline-flex h-9 items-center rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold hover:bg-slate-50" href="/auth/logout.php">Logout</a>
              <?php else: ?>
                <a class="inline-flex h-9 items-center rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold hover:bg-slate-50" href="/auth/login.php">Login</a>
                <a class="inline-flex h-9 items-center rounded-xl bg-emerald-600 px-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500" href="/auth/register.php">Sign Up</a>
              <?php endif; ?>
            </div>
        </div>
      </header>

      <?php if ($flash):
        $type = (string)($flash['type'] ?? '');
        $bg = $type === 'error'
          ? 'bg-rose-50 border-rose-200 text-rose-800'
          : ($type === 'verify'
            ? 'bg-amber-50 border-amber-200 text-amber-900'
            : 'bg-emerald-50 border-emerald-200 text-emerald-800');
      ?>
        <div class="mx-auto max-w-6xl px-4 pt-4">
          <div class="rounded-2xl border px-4 py-3 text-sm <?= $bg ?>">
            <?= e((string)($flash['message'] ?? '')) ?>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($flash && (string)($flash['type'] ?? '') === 'verify'): ?>
        <div class="fixed inset-0 z-[60] grid place-items-center bg-slate-900/35 p-4" role="dialog" aria-modal="true" aria-label="Email verification required" data-verify-modal>
          <div class="w-full max-w-md rounded-3xl border border-amber-200 bg-white p-6 shadow-xl">
            <p class="text-xs font-semibold tracking-widest text-amber-700">EMAIL VERIFICATION</p>
            <p class="mt-2 text-lg font-semibold text-slate-900">Please verify your email</p>
            <p class="mt-2 text-sm text-slate-700"><?= e((string)($flash['message'] ?? 'Please verify your email address to continue.')) ?></p>
            <div class="mt-5 flex flex-wrap gap-3">
              <a href="/auth/login.php" class="inline-flex h-10 items-center justify-center rounded-xl bg-amber-600 px-4 text-sm font-semibold text-white shadow-sm hover:bg-amber-500">Go to Login</a>
              <button type="button" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 hover:bg-slate-50" data-verify-close>Close</button>
            </div>
          </div>
        </div>
        <script>
          (function () {
            var modal = document.querySelector('[data-verify-modal]');
            if (!modal) return;
            var closeBtn = modal.querySelector('[data-verify-close]');
            var close = function () { modal.remove(); };
            if (closeBtn) closeBtn.addEventListener('click', close);
            modal.addEventListener('click', function (e) {
              if (e.target === modal) close();
            });
            document.addEventListener('keydown', function (e) {
              if (e.key === 'Escape') close();
            });
          })();
        </script>
      <?php endif; ?>

      <main>
  <?php
}

function layout_footer(): void {
  ?>
      </main>
      <footer class="mt-16 border-t border-slate-200 bg-white">
        <div class="mx-auto grid max-w-6xl gap-10 px-4 py-10 md:grid-cols-2">
          <section>
            <p class="text-xs font-semibold tracking-widest text-slate-500">LOCATION MAP</p>
            <div class="mt-3 overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 shadow-sm">
              <div class="aspect-[4/3] w-full">
                <iframe
                  title="Barangay 806 Location Map"
                  src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3130.6900248623656!2d121.00125422607276!3d14.571745156530605!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397c99b85fb65e1%3A0x7afb07ffc83eacd1!2sBrgy.%20806%20Zone%2087!5e0!3m2!1sen!2sph!4v1773496223646!5m2!1sen!2sph"
                  style="border:0; width: 100%; height: 100%;"
                  allowfullscreen=""
                  loading="lazy"
                  referrerpolicy="no-referrer-when-downgrade"
                ></iframe>
              </div>
            </div>
          </section>

          <section>
            <div class="flex items-start justify-between gap-6">
              <div>
                <p class="text-xs font-semibold tracking-widest text-slate-500">CONTACT US</p>

                <dl class="mt-3 space-y-2 text-sm text-slate-700">
                  <div class="flex gap-2">
                    <dt class="font-semibold text-slate-900">Telephone Number:</dt>
                    <dd><a class="hover:text-emerald-700" href="tel:09270144791">09270144791</a></dd>
                  </div>
                  <div class="flex gap-2">
                    <dt class="font-semibold text-slate-900">Email:</dt>
                    <dd><a class="break-all hover:text-emerald-700" href="mailto:barangay806zone87district5@gmail.com">barangay806zone87district5@gmail.com</a></dd>
                  </div>
                  <div class="flex gap-2">
                    <dt class="font-semibold text-slate-900">Address:</dt>
                    <dd>Barangay 806, located at Zone 87, Rubi Street, San Andres Bukid, Manila</dd>
                  </div>
                </dl>
              </div>

              <a
                class="mt-6 inline-flex size-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm hover:border-emerald-200 hover:text-emerald-700"
                href="#"
                target="_blank"
                rel="noreferrer noopener"
                aria-label="Facebook"
              >
                <img src="/icon/Icon.png" alt="Facebook" class="size-5 object-cover" />
              </a>
            </div>

            <div class="mt-10 border-t border-slate-200 pt-6 text-sm text-slate-600">
              <p class="font-semibold text-slate-700">BarangayTogether</p>
              <p class="mt-2 max-w-2xl">A simple volunteer platform to help barangay officials organize community activities and track participation.</p>
            </div>
          </section>
        </div>
      </footer>
    </body>
  </html>
  <?php
}
