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
 * Local Library file for additional Functions
 *
 * @package    block_tb_courses
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @author     Leeloo LXP <info@leeloolxp.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/completionlib.php');
use core_completion\progress;

/**
 * The course progress check
 *
 * @param object $course The course whose progress we want
 * @return string
 */
function block_tb_courses_progress_percent($course) {
    global $CFG;

    require_once($CFG->dirroot . '/grade/querylib.php');
    require_once($CFG->dirroot . '/grade/lib.php');

    $percentage = progress::get_course_progress_percentage($course);
    if (!is_null($percentage)) {
        $percentage = floor($percentage);
    } else {
        $percentage = 0;
    }
    return $percentage;
}

/**
 * Get teachers html
 *
 * @param object $course The course whose progress we want
 * @return string
 */
function block_tb_courses_teachers($course) {
    global $PAGE;
    $teacherhtml = '';
    $teacherimages = html_writer::start_div('teacher_image_wrap');
    $teachernames = '';
    $context = context_course::instance($course->id);
    $teachers = get_role_users($role->id, $context, false, $fields);
    foreach ($teachers as $key => $teacher) {
        $teachername = get_string('defaultcourseteacher') . ': ' . fullname($teacher);
        $teachernames .= html_writer::tag('p', $teachername, array('class' => 'teacher_name'));
        
        $user_picture = new user_picture($teacher, array('size' => 50, 'class' => ''));
        $src = $user_picture->get_url($PAGE);
        $teacherimages .= html_writer::div('<img src="'.$src.'"/>', 'c_teacher_image');
    }
    $teacherimages .= html_writer::end_div();
    $teacherhtml .= $teacherimages;
    return $teacherhtml;
}

/**
 * Get the image for a course if it exists
 *
 * @param object $course The course whose image we want
 * @return string|void
 */
function block_tb_course_image($course) {
    global $CFG;

    $course = new core_course_list_element($course);
    // Check to see if a file has been set on the course level.
    if ($course->id > 0 && $course->get_course_overviewfiles()) {
        foreach ($course->get_course_overviewfiles() as $file) {
            $isimage = $file->is_valid_image();
            $url = file_encode_url("$CFG->wwwroot/pluginfile.php",
                '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                $file->get_filearea() . $file->get_filepath() . $file->get_filename(), !$isimage);
            if ($isimage) {
                return $url;
            } else {
                return '';
            }
        }
    } else {
        // Lets try to find some default images eh?.
        return '';
    }
    // Where are the default at even?.
    return print_error('error');
}

/**
 * Get the image for a course if it exists
 *
 * @param string $coursesummary The course summary
 * @param string $length The length
 * @return string|void
 */
function submarylimit($coursesummary, $length) {
    if (strlen($coursesummary) <= $length) {
        return $coursesummary;
    } else {
        return substr($coursesummary, 0, $length) . '...';
    }
}

/**
 * Fetch and Update Configration From L
 */
function updateconfcourses() {
    if (isset(get_config('block_tb_courses')->license)) {
        $leeloolxplicense = get_config('block_tb_courses')->license;
    } else {
        return;
    }

    $url = 'https://leeloolxp.com/api_moodle.php/?action=page_info';
    $postdata = [
        'license_key' => $leeloolxplicense,
    ];
    $curl = new curl;
    $options = array(
        'CURLOPT_RETURNTRANSFER' => true,
        'CURLOPT_HEADER' => false,
        'CURLOPT_POST' => count($postdata),
    );
    if (!$output = $curl->post($url, $postdata, $options)) {
        return;
    }
    $infoleeloolxp = json_decode($output);
    if ($infoleeloolxp->status != 'false') {
        $leeloolxpurl = $infoleeloolxp->data->install_url;
    } else {
        set_config('settingsjson', base64_encode($output), 'block_tb_courses');
        return;
    }
    $url = $leeloolxpurl . '/admin/Theme_setup/get_all_courses_settings';
    $postdata = [
        'license_key' => $leeloolxplicense,
    ];
    $curl = new curl;
    $options = array(
        'CURLOPT_RETURNTRANSFER' => true,
        'CURLOPT_HEADER' => false,
        'CURLOPT_POST' => count($postdata),
    );
    if (!$output = $curl->post($url, $postdata, $options)) {
        return;
    }
    set_config('settingsjson', base64_encode($output), 'block_tb_courses');
}