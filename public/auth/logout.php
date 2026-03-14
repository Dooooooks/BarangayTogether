<?php
declare(strict_types=1);
require_once __DIR__ . '/../_bootstrap.php';

auth_clear_session();
flash_set('success', 'Logged out.');
redirect('/');
