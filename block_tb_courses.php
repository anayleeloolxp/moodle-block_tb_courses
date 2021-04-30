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
 * Courses View Block
 *
 * @package    block_tb_courses
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @author     Leeloo LXP <info@leeloolxp.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot . '/blocks/tb_courses/locallib.php');
require_once($CFG->libdir . '/externallib.php');

/**
 * Courses View Block
 *
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @author     Leeloo LXP <info@leeloolxp.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_tb_courses extends block_base {

    /**
     * Block initialization
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_tb_courses');
    }

    /**
     * Block Config Allow
     */
    public function instance_allow_config() {
        return true;
    }

    /**
     * Block allow multiple instance
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * Return contents of tb_courses block
     *
     * @return stdClass contents of block
     */
    public function get_content() {
        global $CFG;

        if ($this->content !== null) {
            return $this->content;
        }

        $leeloolxplicense = get_config('block_tb_courses')->license;
        $settingsjson = get_config('block_tb_courses')->settingsjson;

        $resposedata = json_decode(base64_decode($settingsjson));

        if (empty($resposedata->data->courses_settings)) {
            $this->title = get_string('displayname', 'block_tb_courses');
            $this->content = new stdClass();
            $this->content->text = '';
            $this->content->footer = '';
            return $this->content;
        }

        $this->title = '';
        $this->content = new stdClass();

        $this->content->text = $this->get_coursecontent($resposedata);

        $this->content->footer = '';

        return $this->content;
    }

    /**
     * Generate HTML for Courses
     *
     * @param string $resposedata Settings from LeelooLXP
     * @return string
     */
    public function get_coursecontent($resposedata) {
        $html = '';

        global $DB;
        $this->page->requires->js(new moodle_url($CFG->wwwroot . '/blocks/tb_courses/js/jquery.min.js'));
        $this->page->requires->js(new moodle_url($CFG->wwwroot . '/blocks/tb_courses/js/owl.carousel.js'));

        $allcourses = get_courses();
        $enrolledcourses = enrol_get_my_courses();

        foreach ($resposedata->data->courses_settings as $section) {
            if (!empty($this->config->sections)) {
                if (!in_array($section->courses_settings_id, $this->config->sections)) {
                    continue;
                }
            }

            $coursessettingsid = $section->courses_settings_id;
            $coursetitle = $section->course_title;
            $categoryid = $section->course_cat_id;
            $defaultmaxcourses = $section->defaultmaxcourses;
            $courseimagedefault = $section->courseimagedefault;
            $tbacoursesbgimage = $section->tb_a_courses_bgimage;
            if ($tbacoursesbgimage == 1) {
                $embed = 'embeded';
            } else {
                $embed = 'notembeded';
            }

            $summarylimit = $section->summary_limit;
            $showteachers = $section->showteachers;
            if ($showteachers == 0) {
                $showteachercss = 'style="display:none;"';
            } else {
                $showteachercss = '';
            }
            $progressenabled = $section->progressenabled;
            if ($progressenabled == 0) {
                $progresscss = 'style="display:none;"';
            } else {
                $progresscss = '';
            }
            $coursegridwidth = $section->coursegridwidth;
            $showasslider = $section->showasslider;
            $styleint = $section->style;
            if ($styleint == 1) {
                $style = 'dark';
            } else {
                $style = 'light';
            }
            $autoslide = $section->autoslide;
            $viewas = $section->view_as;
            $sectiontitle = $section->section_title;
            $sectioncoursestype = $section->section_courses_type;
            $featuredcourses = $section->featured_courses;

            $categorythispath = '/' . $categoryid . '/';

            $courses = array();

            if ($sectioncoursestype == 'available') {
                $loopcourses = $allcourses;
            } else if ($sectioncoursestype == 'completed') {
                $loopcourses = $allcourses;
            } else if ($sectioncoursestype == 'featured') {
                $loopcourses = $allcourses;
            } else if ($sectioncoursestype == 'inprogress') {
                $loopcourses = $allcourses;
            } else if ($sectioncoursestype == 'mycourses') {
                $loopcourses = $enrolledcourses;
            } else if ($sectioncoursestype == 'upcoming') {
                $loopcourses = $enrolledcourses;
            }

            foreach ($loopcourses as $courseid => $courseall) {
                $countc = count($courses);
                if ($countc >= $defaultmaxcourses) {
                    continue;
                }

                $progress = block_tb_courses_progress_percent($courseall);

                if ($sectioncoursestype == 'completed' && $progress != 100) {
                    continue;
                }

                if ($sectioncoursestype == 'inprogress' && ($progress == 0 || $progress == 100 || $progress == 101)) {
                    continue;
                }

                if ($sectioncoursestype == 'upcoming' && $progress != 0) {
                    continue;
                }

                $courseall->progress = $progress;

                $category = $DB->get_record('course_categories', array('id' => $courseall->category));

                if ($category) {
                    $path = trim($category->path) . '/';
                } else {
                    $path = 0;
                }

                if ($sectioncoursestype == 'available') {
                    if ($categoryid == 0) {
                        if (!array_key_exists($courseid, $enrolledcourses)) {
                            $courses[$courseid] = $courseall;
                        }
                    } else {
                        if (!array_key_exists($courseid, $enrolledcourses) && (strpos($path, $categorythispath) !== false)) {
                            $courses[$courseid] = $courseall;
                        }
                    }
                } else if ($sectioncoursestype == 'completed' || $sectioncoursestype == 'inprogress') {
                    if ($categoryid == 0) {
                        if (array_key_exists($courseid, $enrolledcourses)) {
                            $courses[$courseid] = $courseall;
                        }
                    } else {
                        if (array_key_exists($courseid, $enrolledcourses) && (strpos($path, $categorythispath) !== false)) {
                            $courses[$courseid] = $courseall;
                        }
                    }
                } else if ($sectioncoursestype == 'featured') {
                    $featuredcoursesarr = explode(',', $featuredcourses);
                    if (in_array($courseall->id, $featuredcoursesarr)) {
                        $courses[$courseid] = $courseall;
                    }
                } else if ($sectioncoursestype == 'mycourses' || $sectioncoursestype == 'upcoming') {
                    if ($categoryid == 0) {
                        $courses[$courseid] = $courseall;
                    } else {
                        if (strpos($path, $categorythispath) !== false) {
                            $courses[$courseid] = $courseall;
                        }
                    }
                }
            }

            if (!empty($courses)) {
                if ($viewas == 'grid') {
                    $viewclass = 'grid row-fluid';
                    $gridclass = 'col-' . $coursegridwidth;
                } else if ($viewas == 'list') {
                    $viewclass = 'list row-fluid';
                    $gridclass = 'col-12';
                } else if ($viewas == 'grid_slider') {
                    $viewclass = 'tb_courses_slider_' . $coursessettingsid . ' owl-carousel owl-theme';
                    $gridclass = '';

                    if ($autoslide == 1) {
                        $autoslidejs = 'autoplay: true,';
                    } else {
                        $autoslidejs = 'autoplay: false,';
                    }

                    $this->page->requires->js_init_code("$('.tb_courses_slider_" . $coursessettingsid . "').owlCarousel({
                        loop: true,
                        margin: 10,
                        responsiveClass: true,
                        " . $autoslidejs . "
                        responsive: {
                            0: {
                                items: 1,
                                nav: true
                            },
                            600: {
                                items: 3,
                                nav: false
                            },
                            1000: {
                                items: 3,
                                nav: true,
                                dots: false,
                                margin: 20
                            },
                            1300: {
                                items: 4,
                                nav: true,
                                dots: false,
                                margin: 20
                            }
                        }
                    });");
                }

                $html .= '<div class="tb_courses_sectioncontainer">';
                $html .= '<h5 class="tb_courses_section_title card-title">' . $sectiontitle . '</h5>';
                $html .= '<div class="tb_courses_section ' . $viewclass . '">';
                foreach ($courses as $course) {
                    $teachershtml = block_tb_courses_teachers($course);
                    if (block_tb_course_image($course)) {
                        $imgurl = block_tb_course_image($course);
                    } else {
                        $imgurl = $courseimagedefault;
                    }

                    $coursename = $course->fullname;
                    $courseurl = new moodle_url('/course/view.php', array('id' => $course->id));
                    $coursename = format_string(get_course_display_name_for_list($course), true, $course->id);
                    $coursesummary = submarylimit(strip_tags($course->summary), $summarylimit);

                    $html .= '<div class="tb_course_sin ' . $gridclass . ' emstyle_' . $embed . ' style_' . $style . '" style="background-image: url(' . $imgurl . ');">
                    <div class="courseimage"><img src="' . $imgurl . '"/></div>
                    <div class="courseteacher" ' . $showteachercss . ' >' . $teachershtml . '</div>
                    <div class="coursetitle"><a href="' . $courseurl . '">' . $coursename . '</a></div>
                    <div class="coursedesc">' . $coursesummary . '</div>
                    <div class="courseprogress" ' . $progresscss . '><div class="couresprogressbar" style="width:' . $progress . '%">' . $progress . '%</div></div>
                    </div>';
                }
                $html .= '</div>';
                $html .= '</div>';
            }
        }

        return $html;
    }

    /**
     * Allow the block to have a configuration page
     *
     * @return boolean
     */
    public function has_config() {
        return true;
    }

    /**
     * Locations where block can be displayed
     *
     * @return array
     */
    public function applicable_formats() {
        return array('all' => true);
    }
}
