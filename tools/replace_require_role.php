<?php
/**
 * replace_require_role.php
 *
 * Scans the repository for occurrences of `requireRole('NAME')` and replaces them
 * with the `$REQUIRE_PERMISSION` + `require_once page_guard.php` pattern.
 *
 * Usage:
 *   php tools/replace_require_role.php         # dry-run, shows potential changes
 *   php tools/replace_require_role.php --apply # apply changes in-place
 */

$root = realpath(__DIR__ . '/..');
$apply = in_array('--apply', $argv, true);

$excludeDirs = [
    $root . '/vendor',
    $root . '/.git',
    $root . '/node_modules',
    $root . '/uploads',
    $root . '/logo',
    $root . '/assets',
];

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
$pattern = '/requireRole\s*\(\s*(["\'])([^"\']+)\1\s*\)\s*;?/';
$changedFiles = 0;

foreach ($iterator as $file) {
    if (!$file->isFile()) continue;
    $path = $file->getRealPath();
    if (substr($path, -4) !== '.php') continue;
    $skip = false;
    foreach ($excludeDirs as $ex) {
        if (strpos($path, $ex) === 0) { $skip = true; break; }
    }
    if ($skip) continue;

    $content = file_get_contents($path);
    if ($content === false) continue;

    if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
        // Skip files that already use the page guard pattern or declare $REQUIRE_PERMISSION
        if (strpos($content, "page_guard.php") !== false || strpos($content, '$REQUIRE_PERMISSION') !== false) {
            echo "SKIP (already guarded): $path\n";
            continue;
        }

        echo ($apply ? "MODIFY: " : "DRY-RUN: ") . "$path\n";
        $new = $content;
        $firstInserted = false;

        foreach ($matches as $m) {
            $role = $m[2];
            // Remove the original requireRole(...) call
            $new = preg_replace('/' . preg_quote($m[0], '/') . '/', '', $new, 1);

            if (!$firstInserted) {
                // Insert guard after the opening <?php tag
                if (preg_match('/^\s*<\?php\b(.*?)(\r?\n)/s', $new, $pm, PREG_OFFSET_CAPTURE)) {
                    $insertPos = $pm[0][1] + strlen($pm[0][0]);
                    $guard = "\n\$REQUIRE_PERMISSION = '" . addslashes($role) . "';\nrequire_once \$_SERVER['DOCUMENT_ROOT'].\"/config/page_guard.php\";\n\n";
                    $new = substr($new, 0, $insertPos) . $guard . substr($new, $insertPos);
                    $firstInserted = true;
                } else {
                    // File doesn't start with <?php -- prepend
                    $guard = "<?php\n\$REQUIRE_PERMISSION = '" . addslashes($role) . "';\nrequire_once \$_SERVER['DOCUMENT_ROOT'].\"/config/page_guard.php\";\n\n";
                    $new = $guard . $new;
                    $firstInserted = true;
                }
            }
        }

        if ($apply) {
            file_put_contents($path, $new);
            $changedFiles++;
        }
    }
}

echo ($apply ? "Applied changes to $changedFiles file(s).\n" : "Dry-run complete. Rerun with --apply to make changes.\n");
