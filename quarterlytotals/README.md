# customcertelement_quarterlytotals

A `mod_customcert` element that displays a student's quarterly totals
(Quarter 1, Quarter 2, Quarter 3, Quarter 4, Final Grade) for **every
course they're enrolled in**, all as one continuous report-card-style
table on the certificate - matching a real printed report card.

This plugin is **fully self-contained** - it does not require
block_reportcard. It has its own copy of the quarter-grade-fetching logic
(grade categories named "Quarter 1"-"Quarter 4", same hidden-grade
rules), mirroring what block_reportcard does, so the numbers agree - but
each plugin can be installed/updated independently.

## v2.0: one element, every course, one table

**This is a breaking change from v1.** Earlier versions of this element
reported on one course per instance, so a full report card needed one
element dragged onto the template per course. As of v2.0, you add the
**Quarterly totals** element **once**, and it automatically lists every
course the certificate recipient is enrolled in as its own row, all
sharing a single Q1/Q2/Q3/Q4/Final header - no more repeated header
blocks, no template updates needed when a student's course list changes.

**If you already used the v1 version of this element on a certificate
template**, those old elements were saved in a different data format
(a single `courseid` rather than a course list) and won't render
correctly after upgrading. Remove them and re-add the element fresh.

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

## Notes / things you may want to adjust

- Course rows are always sorted alphabetically by course name. If you
  want a specific custom order (matching, say, the exact row order on
  your printed report cards), let me know - that would need a small
  addition (e.g. a reorderable list) rather than relying on alphabetical
  sorting.
- Preview/demo data (Math/English/Science with sample grades) is shown
  in the template editor and "preview PDF" so you can see the layout
  without needing a real student's data.
