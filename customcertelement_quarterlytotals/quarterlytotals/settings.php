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
 * Admin settings for customcertelement_quarterlytotals.
 *
 * $settings is provided by Moodle's plugin settings loader before this
 * file is included - no need to create it ourselves (same convention as
 * every other subplugin's settings.php, e.g. assignsubmission_file).
 *
 * These settings apply site-wide, to every "Quarterly totals" element on
 * every certificate - so a principal only has to set their school's
 * colours once, rather than per element.
 *
 * @package   customcertelement_quarterlytotals
 * @copyright 2026 Finley Myers <finleymwork@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$settings->add(new admin_setting_configcolourpicker(
    'customcertelement_quarterlytotals/headingtextcolor',
    get_string('setting_headingtextcolor', 'customcertelement_quarterlytotals'),
    get_string('setting_headingtextcolor_desc', 'customcertelement_quarterlytotals'),
    '#000000'
));

$settings->add(new admin_setting_configcolourpicker(
    'customcertelement_quarterlytotals/headingbgcolor',
    get_string('setting_headingbgcolor', 'customcertelement_quarterlytotals'),
    get_string('setting_headingbgcolor_desc', 'customcertelement_quarterlytotals'),
    '#f2f2f2'
));

$settings->add(new admin_setting_configcolourpicker(
    'customcertelement_quarterlytotals/bodytextcolor',
    get_string('setting_bodytextcolor', 'customcertelement_quarterlytotals'),
    get_string('setting_bodytextcolor_desc', 'customcertelement_quarterlytotals'),
    '#000000'
));

$settings->add(new admin_setting_configcolourpicker(
    'customcertelement_quarterlytotals/bordercolor',
    get_string('setting_bordercolor', 'customcertelement_quarterlytotals'),
    get_string('setting_bordercolor_desc', 'customcertelement_quarterlytotals'),
    '#999999'
));

$settings->add(new admin_setting_configtext(
    'customcertelement_quarterlytotals/namecolumnwidth',
    get_string('setting_namecolumnwidth', 'customcertelement_quarterlytotals'),
    get_string('setting_namecolumnwidth_desc', 'customcertelement_quarterlytotals'),
    40,
    PARAM_INT
));
