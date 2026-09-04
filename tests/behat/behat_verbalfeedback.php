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

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.
require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Mink\Exception\ExpectationException;

/**
 * Course-related steps definitions.
 *
 * @package    mod_verbalfeedback
 * @category   test
 * @copyright  2026 Stephan Robotta <stephan.robotta@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_verbalfeedback extends behat_base {
    /**
     * Visits a local URL that is expected to render a Moodle exception and asserts its message.
     *
     * Visiting a page that renders a Moodle exception would normally make the scenario fail,
     * because after every step the framework runs behat_session_trait::look_for_exceptions(),
     * which finds the rendered error (a "div[data-rel=fatalerror]" node) and re-throws it.
     * Therefore we cannot split this into a separate "I visit" step followed by a catch step:
     * the visit step would fail on its own. Instead this single step performs the navigation,
     * asserts that the expected exception was rendered, and then leaves a clean page behind so
     * that the after-step hook does not detect the error we just verified.
     *
     * @When /^I get an exception "(?P<message_string>(?:[^"]|\\")*)" when I visit "(?P<url_string>(?:[^"]|\\")*)"$/
     * @param string $message The expected exception message, or a substring of it.
     * @param string $url The local URL to visit.
     * @throws ExpectationException When no exception is rendered or the message does not match.
     */
    public function i_get_an_exception_when_i_visit($message, $url) {
        $session = $this->getSession();

        // Perform the navigation ourselves so we can inspect the rendered error page
        // before the after-step hook does.
        $localurl = new moodle_url($url);
        $session->visit($this->locate_path($localurl->out_as_local_url(false)));

        // Look for the Moodle fatal error that should have been rendered.
        $errornode = $session->getPage()->find('xpath', "//div[@data-rel='fatalerror']");
        if ($errornode === null) {
            throw new ExpectationException(
                "Expected a Moodle exception when visiting \"$url\", but none was rendered.",
                $session
            );
        }

        // Assert the expected message is present on the error page.
        if (strpos($session->getPage()->getText(), $message) === false) {
            throw new ExpectationException(
                "A Moodle exception was rendered when visiting \"$url\", but its message did not contain \"$message\".",
                $session
            );
        }

        // Navigate away to a clean page so the after-step hook does not detect the fatal
        // error we just verified and fail the scenario.
        $session->visit($this->locate_path('/'));
    }
}
