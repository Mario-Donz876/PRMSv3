<?php
require_once __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| Permission-based Page Guard
|--------------------------------------------------------------------------
| Each page must define:
|     $REQUIRE_PERMISSION = 'permission_name';
|--------------------------------------------------------------------------
*/

if (isset($REQUIRE_PERMISSION)) {
    require_permission($REQUIRE_PERMISSION);
}
