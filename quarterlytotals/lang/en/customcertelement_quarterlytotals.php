<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language strings.
 *
 * @package   customcertelement_quarterlytotals
 * @copyright 2026 Finley Myers <finleymwork@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Quarterly totals';

$string['coursemode'] = 'Which courses to show';
$string['coursemode_help'] = 'Auto-detect (the default) lists every course the certificate recipient is enrolled in - so as students change courses year to year, the certificate keeps up automatically with no template changes needed. Choose "Only these courses" if you want to hand-pick an exact, fixed list instead.';
$string['coursemode_auto'] = 'Auto-detect: every course the student is enrolled in';
$string['coursemode_specific'] = 'Only these courses';

$string['specificcourseids'] = 'Courses to show';
$string['specificcourseids_help'] = 'Only used when "Which courses to show" above is set to "Only these courses". Hold Ctrl (or Cmd on Mac) to select multiple courses. Rows are always shown alphabetically by course name, regardless of the order you select them in.';

$string['excludedcourseids'] = 'Courses to exclude';
$string['excludedcourseids_help'] = 'Only used when "Which courses to show" above is set to auto-detect. Use this to hide specific courses from the certificate even though the student is enrolled in them - for example, a non-academic homeroom or tracking course. Hold Ctrl (or Cmd on Mac) to select multiple courses.';

$string['coursesheader'] = 'Courses';

$string['columnsheading'] = 'Columns to display';
$string['showquarter1'] = 'Show Quarter 1';
$string['showquarter2'] = 'Show Quarter 2';
$string['showquarter3'] = 'Show Quarter 3';
$string['showquarter4'] = 'Show Quarter 4';
$string['showfinalgrade'] = 'Show Final Grade';

$string['quarter1short'] = 'Q1';
$string['quarter2short'] = 'Q2';
$string['quarter3short'] = 'Q3';
$string['quarter4short'] = 'Q4';
$string['finalgradeshort'] = 'Final';

$string['nocoursesfound'] = 'No courses were found to display for this student.';

$string['setting_headingtextcolor'] = 'Heading text colour';
$string['setting_headingtextcolor_desc'] = 'Colour of the "Q1", "Q2", "Q3", "Q4", "Final" column headings shown at the top of every Quarterly totals table.';

$string['setting_headingbgcolor'] = 'Heading background colour';
$string['setting_headingbgcolor_desc'] = 'Background colour behind the column headings row.';

$string['setting_bodytextcolor'] = 'Grade text colour';
$string['setting_bodytextcolor_desc'] = 'Colour of the course name and the grade values themselves.';

$string['setting_bordercolor'] = 'Border colour';
$string['setting_bordercolor_desc'] = 'Colour of the lines around each cell in the table.';

$string['setting_namecolumnwidth'] = 'Course name column width (%)';
$string['setting_namecolumnwidth_desc'] = 'How much of the table\'s width the course name column takes up, as a percentage (10-90). The remaining width is split evenly between the visible quarter/final columns. Keeping this the same across every course keeps a stack of Quarterly totals elements lined up like a real report card.';

$string['privacy:metadata'] = 'The Quarterly totals certificate element only stores which courses to show (or exclude) and column display preferences chosen by the certificate designer. It does not store any personal data of its own - the grade data it displays at render time belongs to Moodle\'s core gradebook, which already declares that data to the privacy API.';
