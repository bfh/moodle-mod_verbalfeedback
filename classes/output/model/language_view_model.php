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

namespace mod_verbalfeedback\output\model;

use mod_verbalfeedback\model\language;

/**
 * The language view model class
 * @package    mod_verbalfeedback
 * @copyright  2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class language_view_model {
    /** @var int|null The language id */
    public $id;
    /** @var string The language string */
    public $language;
    /** @var string The language edit url */
    public $editurl;
    /** @var string The language delete url */
    public $deleteurl;

    /**
     * The class constructor
     *
     * @param language $language The language object
     * @throws \moodle_exception
     */
    public function __construct(language $language) {
        $this->id = $language->get_id();
        $this->language = $language->get_language();

        $url = new \moodle_url('/mod/verbalfeedback/language_edit.php', ["id" => $language->get_id()]);
        $this->editurl = $url->out();

        $url = new \moodle_url('/mod/verbalfeedback/language_delete.php', ["id" => $language->get_id()]);
        $this->deleteurl = $url->out();
    }
}
