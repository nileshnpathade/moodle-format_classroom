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
 * Delect classroom.
 *
 * @package format_classroom
 * @copyright 2018 eNyota Learning Pvt Ltd.
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
$id = required_param('cid', PARAM_INT); // Location ID.
$delete = optional_param('delete', '', PARAM_ALPHANUM); // Location name hash to confirm.
$locationid = required_param('location_id', PARAM_INT);
global $OUTPUT, $PAGE, $DB;
$getclassroom = $DB->get_record('format_classroom', array('id' => $id));
require_login();
$PAGE->set_url('/course/format/classroom/delect_class.php?cid='.$id, array());
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_title(get_string('manage_classroom', 'format_classroom'));
$PAGE->set_heading(get_string('manage_classroom', 'format_classroom'));
$PAGE->set_pagelayout('course');
$PAGE->navbar->add('Site administration', new moodle_url('/admin/search.php'));
$PAGE->navbar->add('Plugins', new moodle_url('/admin/category.php?category=modules'));
$PAGE->navbar->add('Classroom format', new moodle_url('/admin/settings.php?section=formatsettingclassroom'));
$PAGE->navbar->add('Manage Location', new moodle_url('/course/format/classroom/manage_location.php'));
$PAGE->navbar->add('Manage Classroom', new moodle_url('/course/format/classroom/manage_classroom.php?location_id='.$locationid));
$PAGE->navbar->add('Delete '.$getclassroom->classroom);

$categoryurl = new moodle_url('/course/format/classroom/manage_classroom.php', array('location_id' => $locationid));
if ($delete === md5($getclassroom->classroom)) {
    $updatedclassroomlocation = new stdClass();
    $updatedclassroomlocation->id = $id;
    $updatedclassroomlocation->isdeleted = 0;
    $success = $DB->update_record('format_classroom', $updatedclassroomlocation);
    if ($success) {
        redirect($categoryurl, 'Classroom deleted successfully', null, \core\output\notification::NOTIFY_SUCCESS);
    }
}

$message = get_string('confirmdeleteclassroom', 'format_classroom');
$continueurl = new moodle_url('/course/format/classroom/delect_class.php',
    array('cid' => $id, 'location_id' => $locationid, 'delete' => md5($getclassroom->classroom)));
$continuebutton = new single_button($continueurl, get_string('delete'), 'post');
$PAGE->set_heading($SITE->fullname);
echo $OUTPUT->header();
echo $OUTPUT->confirm($message, $continuebutton, $categoryurl);
echo $OUTPUT->footer();
exit;
