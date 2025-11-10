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

use mod_verbalfeedback\model\template\template_criterion;
use mod_verbalfeedback\repository\language_repository;

/**
 * The template criterion view model class
 * @package    mod_verbalfeedback
 * @copyright  2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class template_criterion_view_model {
    /**
     * @var int The criterion id
     */
    public $id;
    /**
     * @var string The criterion text
     */
    public $text;
    /**
     * @var string The edit url
     */
    public $editurl;
    /**
     * @var string The delete url
     */
    public $deleteurl;

    /** @var int */
    public $subratingcount;

    /**
     * The template criterion view model class constructor
     *
     * @param template_criterion $criteria
     * @throws \moodle_exception
     */
    public function __construct(template_criterion $criteria) {
        $languagerepository = new language_repository();
        $this->id = $criteria->get_id();
        $currentlanguage = current_language();

        foreach ($criteria->get_descriptions() as $s) {
            $descriptionlanguage = $languagerepository->get_by_id($s->get_language_id());
            if ($descriptionlanguage->get_language() == $currentlanguage) {
                $this->text = $s->get_string();
                break;
            }
        }

        $this->subratingcount = count($criteria->get_subratings());

        $url = new \moodle_url('/mod/verbalfeedback/template_criterion_edit.php', ["id" => $criteria->get_id()]);
        $this->editurl = $url->out();

        $url = new \moodle_url('/mod/verbalfeedback/template_criterion_delete.php', ["id" => $criteria->get_id()]);
        $this->deleteurl = $url->out();
    }
}
