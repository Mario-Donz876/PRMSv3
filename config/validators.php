<?php
function preventBackDating($date) {
    if ($date < date('Y-m-d')) {
        pop('Back-dating is not allowed.', $_SERVER['HTTP_REFERER'] ?? '/dashboard/index.php', POP_DEFAULT_DELAY_MS, 'error');
        exit;
    }
}
