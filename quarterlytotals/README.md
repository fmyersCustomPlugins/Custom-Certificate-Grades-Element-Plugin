# customcertelement_quarterlytotals

A `mod_customcert` element that displays a student's quarterly totals
(Quarter 1, Quarter 2, Quarter 3, Quarter 4, Final Grade) for **every
course they're enrolled in**, all as one continuous report-card-style
table on the certificate - matching a real printed report card.

This plugin is **fully self-contained** - it does not require
any other plugins in order to work.

## How it works

- **Auto-detect (default):** lists every course the student is enrolled
  in, sorted alphabetically. Nothing to configure - just drag it on.
- **Only these courses:** hand-pick an exact, fixed list of courses
  instead, if you don't want auto-detection.
- **Courses to exclude:** (auto-detect mode only) hide specific courses
  even though the student is enrolled in them - useful for a
  non-academic homeroom/tracking course that shouldn't appear on a report
  card.
- Checkboxes let you toggle which columns appear (Q1-Q4, Final Grade) -
  useful for e.g. a "midterm" certificate showing only Q1/Q2.
- Every cell has an explicit width percentage, so the table stays a
  uniform size and every row lines up regardless of how long each course
  name is.
- Colours are set once, site-wide, via **Site administration > Plugins >
  Activity modules > Certificate** (search "quarterlytotals" in the
  admin settings search box if you can't find it):
  - Heading text colour (the Q1/Q2/Q3/Q4/Final labels)
  - Heading background colour
  - Grade/course-name text colour
  - Border colour
  - Course name column width (%)

## Installation - via the "Install plugins" upload page

1. Log in as an admin and go to **Site administration > Plugins >
   Install plugins**.
2. Upload `customcertelement_quarterlytotals.zip`.
3. Moodle will detect it as a certificate element subplugin
   (component `customcertelement_quarterlytotals`) automatically.
   Confirm and follow the on-screen install steps.
4. Go to a certificate template's editor, choose **Quarterly totals**
   from the "Add element" dropdown, and position it. Add it once - not
   once per course.
