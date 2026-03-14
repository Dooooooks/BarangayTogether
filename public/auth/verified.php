<?php
declare(strict_types=1);
require_once __DIR__ . '/../_layout.php';

layout_header('Email Verification', '');
?>

<section class="mx-auto max-w-lg px-4 py-12">
  <div class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
    <p class="text-xs font-semibold tracking-widest text-emerald-700">VERIFICATION</p>
    <h1 class="mt-2 text-2xl font-semibold tracking-tight">Email verification</h1>
    <p class="mt-3 text-sm text-slate-700" id="verify-msg">
      If your email was confirmed successfully, you can now log in.
    </p>

    <div class="mt-6 flex flex-wrap gap-3">
      <a href="/auth/login.php" class="inline-flex h-11 items-center justify-center rounded-xl bg-emerald-600 px-5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">Go to Login</a>
      <a href="/" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold hover:bg-slate-50">Home</a>
    </div>
  </div>
</section>

<script>
  (function () {
    var el = document.getElementById('verify-msg');
    if (!el) return;

    var hash = window.location.hash || '';
    if (!hash || hash.length < 2) return;

    var params = new URLSearchParams(hash.slice(1));
    var err = params.get('error');
    if (!err) return;

    var desc = params.get('error_description') || 'Email link is invalid or has expired.';
    el.textContent = decodeURIComponent(desc.replace(/\+/g, ' '));
  })();
</script>

<?php layout_footer(); ?>
