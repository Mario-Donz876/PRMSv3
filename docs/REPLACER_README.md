Replace requireRole helper
=========================

This repo includes `tools/replace_require_role.php`, a small CLI helper to convert legacy
`requireRole('NAME')` calls into the project's `permission-guard` pattern.

Usage
-----

Dry-run (no changes):

```sh
php tools/replace_require_role.php
```

Apply changes in-place:

```sh
php tools/replace_require_role.php --apply
```

Notes
-----
- The script skips `vendor`, `.git`, and several large asset directories.
- If a file already references `page_guard.php` or declares `$REQUIRE_PERMISSION` the file is skipped.
- Review changes before committing; the script performs simple textual transforms and may need
  human review for complex patterns.
