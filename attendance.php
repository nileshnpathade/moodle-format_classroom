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
 * Assign and Unassign users for session.
 *
 * @package    format_classroom
 * @copyright  2018 eNyota Learning Pvt. Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../../config.php');

global $CFG, $USER, $DB, $PAGE, $COURSE;
$context = context_system::instance();
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$sesseid = required_param('sess_id',  PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
require_login();
$PAGE->set_context($context);
$PAGE->set_url('/course/format/classroom/attendance.php?courseid='.$courseid.'&sess_id='.$sesseid.'&token=1', array());
$PAGE->set_title(get_string('update_location', 'format_classroom'));
$PAGE->set_heading(get_string('update_location', 'format_classroom'));
$PAGE->set_pagelayout('course');

$submit = optional_param('submit', '', PARAM_RAW);
if (!empty($submit)) {
    $sessid = optional_param('session_name', 0, PARAM_RAW);
    $checksession = $DB->get_records('classroom_attendance', array('sessionid' => $sessid));
    if (empty($checksession)) {
        if (!empty($sessid)) {
            $users = optional_param_array('userid', 0, PARAM_RAW);
            $status = optional_param_array('status', 0, PARAM_RAW);
            $comment = optional_param_array('comment', 0, PARAM_RAW);
            for ($v = 1; $v <= count($users); $v++) {
                $userid = isset($users[$v]) ? $users[$v] : '0';
                $status = isset($status[$v]) ? $status[$v] : 'A';
                $comment = isset($comment[$v]) ? $comment[$v] : '';
                $attendance = 'A';
                if ($status == 'P') {
                    $attendance = 'P';
                }

                $classroomattendance = new stdClass();
                $classroomattendance->userid = $userid;
                $classroomattendance->attendance = $attendance;
                $classroomattendance->sessionid = $sessid;
                $classroomattendance->courseid = $courseid;
                $classroomattendance->comment = $comment;
                $insertedid = $DB->insert_record('classroom_attendance', $classroomattendance);

                // Present Mail.
                if ($status == 'P') {
                    $userto = $DB->get_record('user', array('id' => $userid));
                    $getsessiondetails  = $DB->get_record('classroom_session', array('id' => $sessid));
                    $messagehtml = "Dear $userto->firstname,<br/><br/>
                        Thank you for attending session $getsessiondetails->session .<br/>
                        Here is comment for you given by Admin: $comment<br/><br/>
                        Regards,<br/>
                        $SITE->fullname.
                    ";
                    email_to_user($userto, $USER, 'Thank you for attending session '.$getsessiondetails->session,
                        'Thank you for attending session', $messagehtml);
                } else {
                    $userto = $DB->get_record('user', array('id' => $userid));
                    $getsessiondetails  = $DB->get_record('classroom_session', array('id' => $sessid));
                    $messagehtml = "Dear $userto->firstname,<br/><br/>
                        You have miss session $getsessiondetails->session .<br/>
                        If you have interested again than contact to admin.<br/>
                        Here is comment for you given by Admin: $comment<br/><br/>
                        Regards,<br/>
                        $SITE->fullname.
                    ";
                    email_to_user($userto, $USER, 'Absent for session '.$getsessiondetails->session, 'Absent for  session',
                        $messagehtml);
                }
            }
            if (isset($insertedid)) {
                $redirecturl = 'course/view.php?id='.$courseid.'&editmenumode=true&menuaction=assginusertosession&token=1';
                redirect($CFG->wwwroot.'/'.$redirecturl, 'Attendance has been done successfully.',
                    null, \core\output\notification::NOTIFY_SUCCESS);
            }
        } else {
            $redirecturl = 'course/view.php?id='.$courseid.'&editmenumode=true&menuaction=assginusertosession&token=1';
            redirect($CFG->wwwroot.'/'.$redirecturl,
            'Select Session', null, \core\output\notification::NOTIFY_ERROR);
        }
    } else {
        if (!empty($sessid)) {
            $users = optional_param_array('userid', 0, PARAM_RAW);
            $status = optional_param_array('status', 0, PARAM_RAW);
            $comment = optional_param_array('comment', 0, PARAM_RAW);
            $v = 1;
            foreach ($users as $key => $value) {
                $getattendanceid = $DB->get_record('classroom_attendance',
                array('sessionid' => $sessid, 'userid' => $value));
                $userid = isset($users[$v]) ? $users[$v] : '0';
                $status = isset($status[$v]) ? $status[$v] : 'A';
                $comment = isset($comment[$v]) ? $comment[$v] : '';
                $attendance = 'A';
                if ($status == 'P') {
                    $attendance = 'P';
                }
                if (isset($getattendanceid->id)) {
                    $classroomattendanceupdate = new stdClass();
                    $classroomattendanceupdate->id = $getattendanceid->id;
                    $classroomattendanceupdate->userid = $userid;
                    $classroomattendanceupdate->attendance = $attendance;
                    $classroomattendanceupdate->sessionid = $sessid;
                    $classroomattendanceupdate->courseid = $courseid;
                    $classroomattendanceupdate->comment = $comment;
                    $updateid = $DB->update_record('classroom_attendance', $classroomattendanceupdate);
                } else {
                    $classroomattendance2 = new stdClass();
                    $classroomattendance2->userid = $userid;
                    $classroomattendance2->attendance = $attendance;
                    $classroomattendance2->sessionid = $sessid;
                    $classroomattendance2->courseid = $courseid;
                    $classroomattendance2->comment = $comment;
                    $insertedid = $DB->insert_record('classroom_attendance', $classroomattendance2);

                    // Present Mail.
                    if ($status == 'P') {
                        $userto = $DB->get_record('user', array('id' => $userid));
                        $getsessiondetails  = $DB->get_record('classroom_session', array('id' => $sessid));
                        $messagehtml = "Dear $userto->firstname,<br/><br/>
                            Thank you for attending session $getsessiondetails->session .<br/>
                            Here is comment for you given by Admin: $comment<br/><br/>
                            Regards,<br/>
                            $SITE->fullname.
                        ";
                        email_to_user($userto, $USER, 'Thank you for attending session '.$getsessiondetails->session,
                            'Thank you for attending session', $messagehtml);
                    } else {
                        $userto = $DB->get_record('user', array('id' => $userid));
                        $getsessiondetails  = $DB->get_record('classroom_session', array('id' => $sessid));
                        $messagehtml = "Dear $userto->firstname,<br/><br/>
                            You have miss session $getsessiondetails->session .<br/>
                            If you have interested again than contact to admin.<br/>
                            Here is comment for you given by Admin: $comment<br/><br/>
                            Regards,<br/>
                            $SITE->fullname.
                        ";
                        email_to_user($userto, $USER, 'Absent for session '.$getsessiondetails->session,
                            'Absent for  session', $messagehtml);
                    }
                }
                $v++;
            }
            $fredirect = $CFG->wwwroot.'/course/view.php?id='.$courseid.'&editmenumode=true&menuaction=assginusertosession&token=1';
            redirect($fredirect, 'Attendance has been updated successfully',
                null, \core\output\notification::NOTIFY_SUCCESS);
        }
    }
}