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
 * Class for creating graphs. For example, to display on the report.
 *
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_verbalfeedback\utils;

defined('MOODLE_INTERNAL') || die();


use Goat1000\SVGGraph\SVGGraph;
use mod_verbalfeedback\model\report;

require_once('./classes/vendor/autoload.php');

/**
 * Class for creating graphs. For example, to display on the report.
 *
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class graph_utils {

    /**
     * Creates an SVG radar graph image for the given report.
     *
     * @param report $report The report, for which the graph shall be created.
     * @return string The SVG image as XML string.
     */
    public static function create_radar_graph(report $report) : string {
        $settings = [
        'back_colour' => '#eee',
        'back_stroke_width' => 0,
        'back_stroke_colour' => '#eee',
        'stroke_colour' => '#000',
        'axis_colour' => '#333',
        'axis_overlap' => 0,
        'grid_colour' => '#666',
        'label_colour' => '#000',
        'axis_font' => 'Arial',
        'axis_font_size' => 13,
        'pad_right' => 20,
        'pad_left' => 20,
        'pad_bottom' => 20,
        'pad_top' => 20,
        'marker_type' => 'circle',
        'marker_size' => 3,
        'marker_colour' => 'blue',
        'link_base' => '/',
        'link_target' => '_top',
        'show_labels' => true,
        'label_space' => 50,
        'label_font' => 'Arial',
        'label_font_size' => '14',
        'minimum_grid_spacing' => 20,
        'show_subdivisions' => true,
        'show_grid_subdivisions' => true,
        'grid_subdivision_colour' => '#ccc',
        'axis_max_v' => 5,
        ];

        $width = 330;
        $height = 220;
        $type = 'RadarGraph';
        $values = [];

        foreach ($report->get_categories() as $category) {
            if ($category->get_weight() > 0) {
                // Don't show categories with weight 0.
                $label = $category->get_header(current_language())->get_string();
                $values[$label] = $category->get_weighted_result();
            }
        }

        $graph = new SVGGraph($width, $height, $settings);

        $graph->values($values);

        return $graph->fetch($type, true);
    }

}
