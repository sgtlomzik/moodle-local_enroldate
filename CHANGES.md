# Changelog

All notable changes to this plugin are documented in this file.

## [1.3.0] - 2026-08-28

### Added
- GPLv3 boilerplate, `@package`/`@copyright`/`@license` docblocks on every file and a `COPYING`
  file with the licence text.
- Privacy API provider (`null_provider`), which the plugin was missing entirely.
- Settings page with **Keep past enrolments visible in grade reports**, so the core
  `grade_report_showonlyactiveenrol` option is no longer changed behind the administrator's back.
- Help text on the enrolment end date explaining that it takes precedence over the duration.
- A notice when a search returns more matches than the 20 that are displayed.
- Unit tests for user search and for the grade-report-visibility setting; Moodle plugin CI
  workflow.
- `$plugin->supported` declaration.

### Changed
- `db/install.php` no longer writes to a core setting at install time; the behaviour is now
  governed by the plugin setting, which the upgrade step enables so existing sites are unaffected.
- Search moved out of `index.php` into a documented `local_enroldate_search_users()` function that
  escapes wildcards in the search term and orders results by name.
- Variable names follow the Moodle coding style, and the inline `style=""` block around the search
  results was dropped.

### Fixed
- **Cross-site scripting:** unmatched identifiers pasted into the bulk field were echoed into a
  notification without escaping. They are now passed through `s()`.
- **Missing sesskey check:** the search branch read `get_submitted_data()`, which skips form
  sesskey validation. It now calls `require_sesskey()` before running the query.
- `print_error()`, removed from Moodle in 4.5, replaced with `throw new moodle_exception()`.
- `tests/local_enroldate_logic_test.php` was a standalone CLI script that defined
  `MOODLE_INTERNAL` and ran assertions at include time. PHPUnit collects every `*_test.php` file
  in `tests/`, so this one broke the suite; it has been removed and its assertions folded into
  the PHPUnit test class.
- The success message counted enrolment calls rather than distinct users, over-reporting when the
  same user was both ticked and listed in the bulk field.
- The bulk lookup selected whole user records where only the id was used.
- Enrolment status choices use the `ENROL_USER_ACTIVE` / `ENROL_USER_SUSPENDED` constants instead
  of bare `0` and `1`, and the duration default unit uses `DAYSECS`.
- The page bails out with a clear error if the manual enrolment plugin itself is disabled site-wide,
  not only when the course has no manual instance.
- Minimum requirement raised to Moodle 4.5 (LTS); the declared minimum of 3.10 had long been out of
  support and the code no longer runs there.
