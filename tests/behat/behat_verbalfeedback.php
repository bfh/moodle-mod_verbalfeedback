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
        $this->assert_exception_when_visiting($message, new moodle_url($url));
    }

    /**
     * Visits the report page of a participant and asserts that the expected exception is rendered.
     *
     * phpcs:disable moodle.Files.LineLength.MaxExceeded, moodle.Files.LineLength.TooLong
     * @When /^I get an exception "(?P<message_string>(?:[^"]|\\")*)" when I visit the verbal feedback report "(?P<page_string>report|report_download)" for "(?P<touser_string>(?:[^"]|\\")*)" rated by "(?P<fromuser_string>(?:[^"]|\\")*)" in "(?P<activity_string>(?:[^"]|\\")*)"$/
     * @param string $message The expected exception message, or a substring of it.
     * @param string $page Either "report" or "report_download".
     * @param string $touser The username of the rated participant.
     * @param string $fromuser The username of the reviewer whose single feedback is being requested.
     * @param string $activity The name of the verbal feedback activity.
     */
    public function i_get_an_exception_visiting_report($message, $page, $touser, $fromuser, $activity) {
        $this->assert_exception_when_visiting($message, $this->report_url($page, $activity, $touser, $fromuser));
    }

    /**
     * Visits the report (HTML) page of a participant, optionally broken down by a single reviewer.
     *
     * @When /^I visit the verbal feedback report for "(?P<touser_string>(?:[^"]|\\")*)" in "(?P<activity_string>(?:[^"]|\\")*)"$/
     * @param string $touser The username of the rated participant.
     * @param string $activity The name of the verbal feedback activity.
     */
    public function i_visit_the_combined_report($touser, $activity) {
        $url = $this->report_url('report', $activity, $touser);
        $this->getSession()->visit($this->locate_path($url->out_as_local_url(false)));
    }

    /**
     * Visits the report (HTML) page of a participant broken down by a single reviewer.
     *
     * phpcs:disable moodle.Files.LineLength.MaxExceeded, moodle.Files.LineLength.TooLong
     * @When /^I visit the verbal feedback report for "(?P<touser_string>(?:[^"]|\\")*)" rated by "(?P<fromuser_string>(?:[^"]|\\")*)" in "(?P<activity_string>(?:[^"]|\\")*)"$/
     * @param string $touser The username of the rated participant.
     * @param string $fromuser The username of the reviewer whose single feedback is requested.
     * @param string $activity The name of the verbal feedback activity.
     */
    public function i_visit_the_single_reviewer_report($touser, $fromuser, $activity) {
        $url = $this->report_url('report', $activity, $touser, $fromuser);
        $this->getSession()->visit($this->locate_path($url->out_as_local_url(false)));
    }

    /**
     * Downloads the report (PDF) of a participant, optionally broken down by a single reviewer,
     * and asserts that no Moodle exception is rendered (i.e. access is granted).
     *
     * The generated PDF content itself is not inspected: Behat cannot read a binary PDF. We only
     * assert that the access control in report_download.php allowed the request to proceed.
     *
     * phpcs:disable moodle.Files.LineLength.MaxExceeded, moodle.Files.LineLength.TooLong
     * @Then /^I can download the verbal feedback report for "(?P<touser_string>(?:[^"]|\\")*)" rated by "(?P<fromuser_string>(?:[^"]|\\")*)" in "(?P<activity_string>(?:[^"]|\\")*)"$/
     * @param string $touser The username of the rated participant.
     * @param string $fromuser The username of the reviewer whose single feedback is requested.
     * @param string $activity The name of the verbal feedback activity.
     * @throws ExpectationException When an exception is rendered instead of the PDF.
     */
    public function i_can_download_the_report($touser, $fromuser, $activity) {
        $session = $this->getSession();
        $url = $this->report_url('report_download', $activity, $touser, $fromuser);
        $session->visit($this->locate_path($url->out_as_local_url(false)));

        $errornode = $session->getPage()->find('xpath', "//div[@data-rel='fatalerror']");
        if ($errornode !== null) {
            throw new ExpectationException(
                "Expected the report download for \"$touser\" rated by \"$fromuser\" to be allowed, " .
                    'but a Moodle exception was rendered.',
                $session
            );
        }

        // Navigate away to a clean page for the following steps.
        $session->visit($this->locate_path('/'));
    }

    /**
     * Performs the navigation to the given URL and asserts that the expected exception is rendered.
     *
     * Visiting a page that renders a Moodle exception would normally make the scenario fail,
     * because after every step the framework runs behat_session_trait::look_for_exceptions(),
     * which finds the rendered error (a "div[data-rel=fatalerror]" node) and re-throws it.
     * Therefore this helper performs the navigation itself, asserts that the expected exception
     * was rendered, and then leaves a clean page behind so that the after-step hook does not
     * detect the error we just verified.
     *
     * @param string $message The expected exception message, or a substring of it.
     * @param moodle_url $url The local URL to visit.
     * @throws ExpectationException When no exception is rendered or the message does not match.
     */
    protected function assert_exception_when_visiting($message, moodle_url $url) {
        $session = $this->getSession();

        // Perform the navigation ourselves so we can inspect the rendered error page
        // before the after-step hook does.
        $session->visit($this->locate_path($url->out_as_local_url(false)));

        // Look for the Moodle fatal error that should have been rendered.
        $errornode = $session->getPage()->find('xpath', "//div[@data-rel='fatalerror']");
        if ($errornode === null) {
            throw new ExpectationException(
                "Expected a Moodle exception when visiting \"" . $url->out(false) . "\", but none was rendered.",
                $session
            );
        }

        // Assert the expected message is present on the error page.
        if (strpos($session->getPage()->getText(), $message) === false) {
            throw new ExpectationException(
                "A Moodle exception was rendered when visiting \"" . $url->out(false) .
                    "\", but its message did not contain \"$message\".",
                $session
            );
        }

        // Navigate away to a clean page so the after-step hook does not detect the fatal
        // error we just verified and fail the scenario.
        $session->visit($this->locate_path('/'));
    }

    /**
     * Builds the local URL of a verbal feedback report page for the given participant and reviewer.
     *
     * @param string $page Either "report" (HTML view) or "report_download" (PDF).
     * @param string $activityname The name of the verbal feedback activity.
     * @param string $tousername The username of the rated participant.
     * @param string|null $fromusername The username of a single reviewer, or null for the combined report.
     * @return moodle_url The report URL.
     */
    protected function report_url($page, $activityname, $tousername, $fromusername = null) {
        $params = [
            'instance' => $this->get_verbalfeedback_instance_id($activityname),
            'touser' => $this->get_user_id_by_username($tousername),
        ];
        if ($fromusername !== null && $fromusername !== '') {
            $params['fromuser'] = $this->get_user_id_by_username($fromusername);
        }
        return new moodle_url('/mod/verbalfeedback/' . $page . '.php', $params);
    }

    /**
     * Returns the id of a verbal feedback instance given its name.
     *
     * @param string $activityname The verbal feedback activity name.
     * @return int The instance id.
     */
    protected function get_verbalfeedback_instance_id($activityname) {
        global $DB;
        // The verbalfeedback "name" column is a TEXT field, which cannot be compared directly
        // in a WHERE clause (e.g. on PostgreSQL), so use sql_compare_text().
        $select = $DB->sql_compare_text('name') . ' = ' . $DB->sql_compare_text(':name');
        return (int)$DB->get_field_select('verbalfeedback', 'id', $select, ['name' => $activityname], MUST_EXIST);
    }

    /**
     * Returns the id of a user given its username.
     *
     * @param string $username The username.
     * @return int The user id.
     */
    protected function get_user_id_by_username($username) {
        global $DB;
        return (int)$DB->get_field('user', 'id', ['username' => $username], MUST_EXIST);
    }
}
