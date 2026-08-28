# Enrolment with date selection for Moodle

[![Moodle plugin CI](https://github.com/sgtlomzik/moodle-local_enroldate/actions/workflows/moodle-ci.yml/badge.svg)](https://github.com/sgtlomzik/moodle-local_enroldate/actions/workflows/moodle-ci.yml)

Enrol users into a course with an explicit start date — including one in the past.

Moodle's own manual enrolment screen always starts an enrolment "now" or at the course start
date. When you are recording training that actually happened last March, that is the wrong
answer. This plugin adds a course-level page where you pick the users, the role, the status and
the exact start and end of the enrolment period, and it keeps those learners visible in grade
reports afterwards.

## Requirements

- Moodle 4.5 (LTS) or later.
- The core **Manual enrolments** method enabled in the course.
- The `enrol/manual:enrol` capability, which teachers and managers have by default.

## Installation

### From the ZIP file

1. Download the ZIP of this repository.
2. Go to **Site administration → Plugins → Install plugins** and upload the ZIP.
3. Follow the on-screen upgrade steps.

### From Git

```bash
cd /path/to/moodle
git clone https://github.com/sgtlomzik/moodle-local_enroldate.git local/enroldate
```

Then visit **Site administration → Notifications** (or run `php admin/cli/upgrade.php`) to
complete the installation.

## Usage

In a course, open **Participants** and choose **Enrolment with date selection** from the menu
(the link is added under the course's Users navigation node). The form has three parts:

1. **Search for a user** — type part of a first name, last name or email address and press
   Search. Up to 20 matches are listed with a checkbox each.
2. **Access settings** — the role to assign, whether the enrolment is active or suspended, the
   start date, and either an enrolment duration or an explicit end date. An explicit end date
   wins over the duration; leaving both empty enrols without an end date.
3. **Or enter a list** — paste email addresses or usernames, separated by commas or new lines,
   to enrol people in bulk. Identifiers that match no account are reported back to you.

Press **Enrol users** to apply. Existing enrolments for the same users are updated rather than
duplicated, exactly as the core manual enrolment plugin does.

## Settings

**Site administration → Plugins → Local plugins → Enrolment with date selection**

| Setting | Default | Description |
| --- | --- | --- |
| Keep past enrolments visible in grade reports | Yes | Sets the core `grade_report_showonlyactiveenrol` option to *No*, so learners whose enrolment period has ended still appear in grade reports. |

> **Note**
> That setting deliberately changes a *site-wide core* option, because an enrolment that has
> already ended is otherwise invisible in the gradebook, which defeats the purpose of recording
> it. If you manage that option yourself, turn this setting off and the plugin will leave it
> alone.

## Privacy

The plugin stores no data of its own. The enrolments it creates belong to the core manual
enrolment plugin, which exports and deletes them through the Privacy API.

## Bug tracker

Please report issues at
<https://github.com/sgtlomzik/moodle-local_enroldate/issues>.

## License

2026 SgtLomzik <lomzike@gmail.com>

This program is free software: you can redistribute it and/or modify it under the terms of the
GNU General Public License as published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not,
see <https://www.gnu.org/licenses/>.
