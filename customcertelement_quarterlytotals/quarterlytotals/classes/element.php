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
 * element.php
 *
 * A customcert element that shows a student's quarterly totals
 * (Quarter 1-4 + Final Grade) for EVERY course they're enrolled in, all
 * as one continuous report-card-style table on the certificate.
 *
 * Design (v2): earlier versions of this element reported on one course
 * per instance, requiring one element to be added per course. This
 * version auto-detects every course the certificate recipient is
 * enrolled in and lists them all as rows in a single table - matching
 * a real printed report card, and meaning whoever builds the certificate
 * template doesn't need to remember to add a row for every course, or
 * update the template when a student's course list changes.
 *
 * You can still narrow this down via the element's settings: show only
 * specific courses, or auto-detect all enrolled courses except some
 * excluded ones (e.g. a non-academic tracking course).
 *
 * NOTE: elements saved by the old (v1) per-course version of this plugin
 * use a different data format ({"courseid":X,...}) and will need to be
 * re-added to any certificate template that already used them.
 *
 * This plugin is fully self-contained - it does NOT depend on
 * block_reportcard. The grade-fetching logic below (get_quarterly_grades())
 * mirrors what block_reportcard does, so certificates and the dashboard
 * block agree on what a "Quarter 1 total" means, but each plugin keeps
 * its own copy so this element can be installed/updated independently.
 *
 * @package   customcertelement_quarterlytotals
 * @copyright 2026 Finley Myers <finleymwork@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customcertelement_quarterlytotals;

defined('MOODLE_INTERNAL') || die();

require_once($GLOBALS['CFG']->libdir . '/gradelib.php');
require_once($GLOBALS['CFG']->dirroot . '/grade/querylib.php');
require_once($GLOBALS['CFG']->dirroot . '/grade/lib.php');

class element extends \mod_customcert\element {

    /**
     * Single source of truth for every "column" this element can show:
     * the settings-form checkbox name, the checkbox's own label string,
     * the short column-heading string shown on the certificate, and the
     * matching key used by get_quarterly_grades() below.
     *
     * Centralising this means save_unique_data(), definition_after_data(),
     * and the renderer all automatically stay in sync - add a new quarter
     * or column here once and everything else picks it up.
     *
     * @return array field name => [checkbox label key, short heading key, grades array key]
     */
    private static function get_column_defs() {
        return [
            'showquarter1'   => ['showquarter1', 'quarter1short', 'Quarter 1'],
            'showquarter2'   => ['showquarter2', 'quarter2short', 'Quarter 2'],
            'showquarter3'   => ['showquarter3', 'quarter3short', 'Quarter 3'],
            'showquarter4'   => ['showquarter4', 'quarter4short', 'Quarter 4'],
            'showfinalgrade' => ['showfinalgrade', 'finalgradeshort', 'Final Grade'],
        ];
    }

    /**
     * Build the list of courses to choose from (every course except the
     * site "course", sorted alphabetically). Used for both the
     * "specific courses" and "excluded courses" multi-select fields.
     *
     * @return array courseid => display name
     */
    private static function get_course_list() {
        global $DB, $SITE;

        $options = [];

        $records = $DB->get_records_select(
            'course',
            'id <> :siteid',
            ['siteid' => $SITE->id],
            'fullname ASC',
            'id, fullname'
        );

        foreach ($records as $course) {
            $options[$course->id] = format_string(
                $course->fullname,
                true,
                ['context' => \context_course::instance($course->id)]
            );
        }

        return $options;
    }

    /**
     * This function renders the form elements when adding a customcert element.
     *
     * @param \MoodleQuickForm $mform the edit_form instance
     */
    public function render_form_elements($mform) {
        $courselist = self::get_course_list();

        $mform->addElement(
            'select',
            'coursemode',
            get_string('coursemode', 'customcertelement_quarterlytotals'),
            [
                'auto'     => get_string('coursemode_auto', 'customcertelement_quarterlytotals'),
                'specific' => get_string('coursemode_specific', 'customcertelement_quarterlytotals'),
            ]
        );
        $mform->setType('coursemode', PARAM_ALPHA);
        $mform->addHelpButton('coursemode', 'coursemode', 'customcertelement_quarterlytotals');
        $mform->setDefault('coursemode', 'auto');

        $mform->addElement(
            'select',
            'specificcourseids',
            get_string('specificcourseids', 'customcertelement_quarterlytotals'),
            $courselist,
            ['multiple' => true, 'size' => min(10, max(4, count($courselist)))]
        );
        $mform->addHelpButton('specificcourseids', 'specificcourseids', 'customcertelement_quarterlytotals');
        $mform->hideIf('specificcourseids', 'coursemode', 'neq', 'specific');

        $mform->addElement(
            'select',
            'excludedcourseids',
            get_string('excludedcourseids', 'customcertelement_quarterlytotals'),
            $courselist,
            ['multiple' => true, 'size' => min(10, max(4, count($courselist)))]
        );
        $mform->addHelpButton('excludedcourseids', 'excludedcourseids', 'customcertelement_quarterlytotals');
        $mform->hideIf('excludedcourseids', 'coursemode', 'neq', 'auto');

        $mform->addElement(
            'header',
            'quarterlytotalscolumnsheader',
            get_string('columnsheading', 'customcertelement_quarterlytotals')
        );
        $mform->setExpanded('quarterlytotalscolumnsheader');

        foreach (self::get_column_defs() as $field => $def) {
            [$labelkey, ] = $def;
            $mform->addElement('advcheckbox', $field, '', get_string($labelkey, 'customcertelement_quarterlytotals'));
            $mform->setDefault($field, 1);
        }

        parent::render_form_elements($mform);
    }

    /**
     * Sets the data on the form when editing an existing element.
     *
     * @param \MoodleQuickForm $mform the edit_form instance
     */
    public function definition_after_data($mform) {
        if (!empty($this->get_data())) {
            $info = json_decode($this->get_data());

            $mform->getElement('coursemode')->setValue($info->coursemode ?? 'auto');
            $mform->getElement('specificcourseids')->setValue($info->specificcourseids ?? []);
            $mform->getElement('excludedcourseids')->setValue($info->excludedcourseids ?? []);

            foreach (self::get_column_defs() as $field => $def) {
                if (isset($info->$field)) {
                    $el = $mform->getElement($field);
                    $el->setValue($info->$field);
                }
            }
        }

        parent::definition_after_data($mform);
    }

    /**
     * This will handle how form data will be saved into the data column in
     * the customcert_elements table.
     *
     * @param \stdClass $data the form data.
     * @return string the json encoded array
     */
    public function save_unique_data($data) {
        $arrtostore = [
            'coursemode'         => ($data->coursemode === 'specific') ? 'specific' : 'auto',
            'specificcourseids'  => array_map('intval', (array) ($data->specificcourseids ?? [])),
            'excludedcourseids'  => array_map('intval', (array) ($data->excludedcourseids ?? [])),
        ];

        foreach (self::get_column_defs() as $field => $def) {
            $arrtostore[$field] = !empty($data->$field) ? 1 : 0;
        }

        return json_encode($arrtostore);
    }

    /**
     * Handles rendering the element on the pdf.
     *
     * @param \pdf $pdf the pdf object
     * @param bool $preview true if it is a preview, false otherwise
     * @param \stdClass $user the user we are rendering this for
     */
    public function render($pdf, $preview, $user) {
        if (empty($this->get_data())) {
            return;
        }

        $info = json_decode($this->get_data());
        $html = $this->build_table_html($info, $preview, $user);

        if ($html === null) {
            return;
        }

        \mod_customcert\element_helper::render_content($pdf, $this, $html);
    }

    /**
     * Render the element in html.
     *
     * This function is used to render the element when we are using the
     * drag and drop interface to position it.
     *
     * @return string the html
     */
    public function render_html() {
        global $USER;

        if (empty($this->get_data())) {
            return;
        }

        $info = json_decode($this->get_data());
        $html = $this->build_table_html($info, true, $USER);

        if ($html === null) {
            return;
        }

        return \mod_customcert\element_helper::render_html_content($this, $html);
    }

    /**
     * Work out which courses to show, in display order.
     *
     * - "specific" mode: exactly the courses picked in the element's settings.
     * - "auto" mode (default): every course the given user is actively
     *   enrolled in, minus the site course and anything on the excluded list.
     *
     * Either way, courses are sorted alphabetically by name so the table
     * has a predictable, repeatable order every time a certificate is issued.
     *
     * @param \stdClass $info the decoded element settings
     * @param int $userid the certificate recipient
     * @return array courseid => course record (id, fullname)
     */
    private static function get_courses_to_show($info, $userid) {
        global $DB, $SITE;

        if (($info->coursemode ?? 'auto') === 'specific') {
            $ids = $info->specificcourseids ?? [];
            if (empty($ids)) {
                return [];
            }
            [$insql, $inparams] = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED, 'course');
            $courses = $DB->get_records_select('course', "id $insql", $inparams, 'fullname ASC', 'id, fullname');
            return $courses;
        }

        // Auto mode: every course this user is actively enrolled in.
        $enrolled = enrol_get_users_courses($userid, true, ['id', 'fullname']);

        $excluded = array_flip($info->excludedcourseids ?? []);

        $courses = [];
        foreach ($enrolled as $course) {
            if ($course->id == $SITE->id) {
                continue;
            }
            if (isset($excluded[$course->id])) {
                continue;
            }
            $courses[$course->id] = $course;
        }

        uasort($courses, function ($a, $b) {
            return strcasecmp($a->fullname, $b->fullname);
        });

        return $courses;
    }

    /**
     * Builds the single report-card-style HTML table listing every course
     * to show, each as one row, sharing one set of column headings.
     *
     * @param \stdClass $info the decoded element settings
     * @param bool $preview true if this is a preview render (uses demo courses+grades)
     * @param \stdClass $user the user to fetch real grades for (ignored when $preview is true)
     * @return string|null HTML, or null if there's nothing to render
     */
    private function build_table_html($info, $preview, $user) {
        $columns = [];
        foreach (self::get_column_defs() as $field => $def) {
            [, $headingkey, $gradekey] = $def;
            if (!empty($info->$field)) {
                $columns[$gradekey] = get_string($headingkey, 'customcertelement_quarterlytotals');
            }
        }

        if ($preview) {
            // Demo data so the template designer and the "preview PDF" have
            // something to look at, without needing a real student's grades.
            $rows = [
                ['name' => 'Math', 'grades' => [
                    'Quarter 1' => '90.00', 'Quarter 2' => '92.00', 'Quarter 3' => '88.00',
                    'Quarter 4' => '95.00', 'Final Grade' => '91.25',
                ]],
                ['name' => 'English', 'grades' => [
                    'Quarter 1' => '85.00', 'Quarter 2' => '87.50', 'Quarter 3' => '89.00',
                    'Quarter 4' => '91.00', 'Final Grade' => '88.13',
                ]],
                ['name' => 'Science', 'grades' => [
                    'Quarter 1' => '93.00', 'Quarter 2' => '90.00', 'Quarter 3' => '94.00',
                    'Quarter 4' => '92.00', 'Final Grade' => '92.25',
                ]],
            ];

            return self::render_table($rows, $columns);
        }

        $courses = self::get_courses_to_show($info, $user->id);

        if (empty($courses)) {
            return \html_writer::tag('em', get_string('nocoursesfound', 'customcertelement_quarterlytotals'));
        }

        $rows = [];
        foreach ($courses as $course) {
            $rows[] = [
                'name'   => format_string($course->fullname, true, ['context' => \context_course::instance($course->id)]),
                'grades' => self::get_quarterly_grades($course->id, $user->id),
            ];
        }

        return self::render_table($rows, $columns);
    }

    /**
     * Get the colours/sizing this element should use, pulled from this
     * plugin's admin settings (Site administration > Plugins > Activity
     * modules > Certificate), with sensible fallbacks if a site has never
     * touched the settings page.
     *
     * @return \stdClass
     */
    private static function get_display_settings() {
        $defaults = [
            'headingtextcolor' => '#000000',
            'headingbgcolor'   => '#f2f2f2',
            'bodytextcolor'    => '#000000',
            'bordercolor'      => '#999999',
            'namecolumnwidth'  => 40,
        ];

        $config = get_config('customcertelement_quarterlytotals');

        $out = new \stdClass();
        foreach ($defaults as $key => $default) {
            $value = isset($config->$key) ? $config->$key : '';
            $out->$key = ($value === '' || $value === false) ? $default : $value;
        }

        // Keep the name column within sane bounds even if someone types a
        // silly value into the settings field.
        $out->namecolumnwidth = max(10, min(90, (int) $out->namecolumnwidth));

        return $out;
    }

    /**
     * Build ONE report-card-style HTML table: a single header row (Q1/Q2/
     * Q3/Q4/Final), then one row per course, each with the course name in
     * a fixed-width left cell.
     *
     * Uses an explicit width percentage on every cell (not just a CSS
     * table-layout rule) because TCPDF's HTML table renderer - which is
     * what actually draws this, via writeHTMLCell() - reliably honours a
     * width set directly on td/th cells, but does not reliably honour
     * <colgroup>/<col> the way a browser would.
     *
     * element_helper::render_content() pushes this straight into TCPDF's
     * writeHTMLCell(), which understands basic HTML tables directly - so
     * this prints as an actual table on the PDF, growing downward to fit
     * however many courses the student has, positioned/sized using
     * whatever x/y/width/font was set for this element.
     *
     * @param array $rows list of ['name' => course name, 'grades' => [gradekey => value]]
     * @param array $columns gradekey => short column heading, in display order
     * @return string
     */
    private static function render_table(array $rows, array $columns) {
        $settings = self::get_display_settings();

        $namewidth = $settings->namecolumnwidth;
        $numcols = max(1, count($columns));
        $colwidth = (100 - $namewidth) / $numcols;

        $labelstyle = sprintf(
            'border:0.5pt solid %s;padding:2px 6px;font-weight:bold;background-color:%s;color:%s;width:%s%%;',
            $settings->bordercolor,
            $settings->headingbgcolor,
            $settings->headingtextcolor,
            $namewidth
        );
        $namestyle = sprintf(
            'border:0.5pt solid %s;padding:2px 6px;text-align:left;color:%s;width:%s%%;',
            $settings->bordercolor,
            $settings->bodytextcolor,
            $namewidth
        );
        $colheaderstyle = sprintf(
            'border:0.5pt solid %s;padding:2px 6px;font-weight:bold;background-color:%s;color:%s;width:%s%%;',
            $settings->bordercolor,
            $settings->headingbgcolor,
            $settings->headingtextcolor,
            $colwidth
        );
        $valuestyle = sprintf(
            'border:0.5pt solid %s;padding:2px 6px;text-align:center;color:%s;width:%s%%;',
            $settings->bordercolor,
            $settings->bodytextcolor,
            $colwidth
        );

        // One header row for the whole table.
        $headrow = \html_writer::tag('td', get_string('coursesheader', 'customcertelement_quarterlytotals'), ['style' => $labelstyle]);
        foreach ($columns as $label) {
            $headrow .= \html_writer::tag('td', $label, ['style' => $colheaderstyle]);
        }

        // One data row per course.
        $bodyrows = '';
        foreach ($rows as $row) {
            $datarow = \html_writer::tag('td', $row['name'], ['style' => $namestyle]);

            foreach ($columns as $gradekey => $label) {
                $value = $row['grades'][$gradekey] ?? '-';
                $datarow .= \html_writer::tag('td', $value, ['style' => $valuestyle]);
            }

            $bodyrows .= \html_writer::tag('tr', $datarow);
        }

        $html = \html_writer::start_tag('table', ['style' => 'border-collapse:collapse;width:100%;']);
        $html .= \html_writer::tag('tr', $headrow);
        $html .= $bodyrows;
        $html .= \html_writer::end_tag('table');

        return $html;
    }

    /**
     * Check whether a "hidden" field value (from grade_items.hidden or
     * grade_grades.hidden) currently means "hidden right now".
     *
     * Moodle encodes both fields the same way:
     *   0             = not hidden.
     *   1             = permanently hidden.
     *   timestamp > 1 = hidden until that unix time.
     *
     * @param int $hiddenvalue
     * @return bool
     */
    private static function is_hidden_value($hiddenvalue) {
        $hiddenvalue = (int) $hiddenvalue;

        if ($hiddenvalue == 1) {
            return true;
        }

        if ($hiddenvalue > 1 && $hiddenvalue > time()) {
            return true;
        }

        return false;
    }

    /**
     * Get the quarterly totals + final grade for one student in one course.
     *
     * A teacher's course is expected to have grade categories named
     * "Quarter 1", "Quarter 2", "Quarter 3", "Quarter 4" (matched
     * case-insensitively, substring match) - the same convention used by
     * block_reportcard.
     *
     * @param int $courseid
     * @param int $studentid
     * @return array Keys: 'Quarter 1', 'Quarter 2', 'Quarter 3', 'Quarter 4',
     *               'Final Grade'. Each value is either a formatted grade
     *               string (e.g. "87.00") or '-' if unavailable/hidden.
     */
    private static function get_quarterly_grades($courseid, $studentid) {
        global $DB;

        $grades = [
            'Quarter 1'   => '-',
            'Quarter 2'   => '-',
            'Quarter 3'   => '-',
            'Quarter 4'   => '-',
            'Final Grade' => '-',
        ];

        // ---------------------------
        // QUARTER CATEGORY GRADES
        // ---------------------------
        $categories = $DB->get_records('grade_categories', ['courseid' => $courseid]);

        foreach ($categories as $cat) {

            $name = strtolower(trim($cat->fullname));

            $key = null;

            if (strpos($name, 'quarter 1') !== false) $key = 'Quarter 1';
            else if (strpos($name, 'quarter 2') !== false) $key = 'Quarter 2';
            else if (strpos($name, 'quarter 3') !== false) $key = 'Quarter 3';
            else if (strpos($name, 'quarter 4') !== false) $key = 'Quarter 4';

            if (!$key) {
                continue;
            }

            $gradeitem = $DB->get_record('grade_items', [
                'courseid'     => $courseid,
                'itemtype'     => 'category',
                'iteminstance' => $cat->id,
            ]);

            if (!$gradeitem) {
                continue;
            }

            $grade = $DB->get_record('grade_grades', [
                'itemid' => $gradeitem->id,
                'userid' => $studentid,
            ]);

            if ($grade && $grade->finalgrade !== null) {

                // Don't reveal a grade the teacher has hidden - not even
                // on a certificate - since that would defeat the point of
                // hiding it.
                if (self::is_hidden_value($gradeitem->hidden) || self::is_hidden_value($grade->hidden)) {
                    continue;
                }

                $gradeitemobj = new \grade_item($gradeitem);

                $grades[$key] = grade_format_gradevalue($grade->finalgrade, $gradeitemobj);
            }
        }

        // ---------------------------
        // FINAL COURSE GRADE
        // ---------------------------
        $finalitem = $DB->get_record('grade_items', [
            'courseid' => $courseid,
            'itemtype' => 'course',
        ]);

        if ($finalitem) {

            $finalgrade = $DB->get_record('grade_grades', [
                'itemid' => $finalitem->id,
                'userid' => $studentid,
            ]);

            if ($finalgrade && $finalgrade->finalgrade !== null) {

                if (self::is_hidden_value($finalitem->hidden) || self::is_hidden_value($finalgrade->hidden)) {
                    // Leave $grades['Final Grade'] as '-'.
                } else {
                    $grades['Final Grade'] = grade_format_gradevalue(
                        $finalgrade->finalgrade,
                        new \grade_item($finalitem)
                    );
                }
            }
        }

        return $grades;
    }
}
