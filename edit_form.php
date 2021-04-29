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
 * Form for editing tag block instances.
 *
 * @package   block_tb_courses
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @author     Leeloo LXP <info@leeloolxp.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Edit Form class
 *
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_tb_courses_edit_form extends block_edit_form {

    /**
     * If this is passed as mynumber then showallcourses, irrespective of limit by user.
     *
     * @param object $mform Edit Form
     */
    protected function specific_definition($mform) {

        $settingsjson = get_config('block_tb_courses')->settingsjson;
        $resposedata = json_decode(base64_decode($settingsjson));

        $availablecourseslist = array();
        $attributes = array();
        foreach ($resposedata->data->courses_settings as $c) {
            $availablecourseslist[$c->courses_settings_id] = $c->section_title;
        }

        $select = $mform->addElement('select', 'config_sections', get_string('sections', 'block_tb_courses'), $availablecourseslist, $attributes);
        $select->setMultiple(true);
    }
}
