@mod @mod_verbalfeedback
Feature: Access to verbal feedback reports depending on the view_all_reports capability
  In order to protect the feedback given by individual reviewers
  As a teacher
  I need the report access to respect the mod/verbalfeedback:view_all_reports capability

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | teacher2 | Teacher   | 2        | teacher2@example.com |
      | teacher3 | Teacher   | 3        | teacher3@example.com |
      | student1 | Student   | 1        | student1@example.com |
    # teacher1 and teacher2 are editing teachers (they have view_all_reports).
    # teacher3 is a non-editing teacher (view_all_reports is prohibited for the teacher archetype).
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | teacher2 | C1     | editingteacher |
      | teacher3 | C1     | teacher        |
      | student1 | C1     | student        |
    And the following "activities" exist:
      | activity       | name                 | course | idnumber | template         |
      | verbalfeedback | Test verbal feedback | C1     | vf1      | Default template |
    # All three trainers submit a completed feedback for the student, so the student's report
    # has three reviewers that can be broken down individually.
    And the following "mod_verbalfeedback > submissions" exist:
      | verbalfeedback       | userfrom | userto   | rating |
      | Test verbal feedback | teacher1 | student1 | 5      |
      | Test verbal feedback | teacher2 | student1 | 3      |
      | Test verbal feedback | teacher3 | student1 | 4      |

  Scenario: An editing teacher can break the student report down by each reviewer
    Given I log in as "teacher1"
    When I visit the verbal feedback report for "student1" in "Test verbal feedback"
    Then I should see "View feedback for user"
    And I should see "Student 1"
    # The reviewer selector offers the combined report plus every trainer that gave feedback,
    # including the teacher's own submission ("Review by Teacher 1") and the other trainers.
    And I should see "Combined review"
    And I should see "Review by Teacher 1"
    And I should see "Review by Teacher 2"
    And I should see "Review by Teacher 3"

  Scenario: An editing teacher can view and download a single reviewer's feedback
    Given I log in as "teacher1"
    When I visit the verbal feedback report for "student1" rated by "teacher2" in "Test verbal feedback"
    Then I should see "Student 1"
    And I should see "Download report"
    # The download is allowed for any reviewer because the editing teacher has view_all_reports.
    And I can download the verbal feedback report for "student1" rated by "teacher2" in "Test verbal feedback"

  Scenario: A non-editing teacher only sees the combined report, without a reviewer breakdown
    Given I log in as "teacher3"
    When I visit the verbal feedback report for "student1" in "Test verbal feedback"
    Then I should see "View feedback for user"
    And I should see "Student 1"
    # Without view_all_reports there is no reviewer selector: the feedback stays combined and anonymous.
    And I should not see "Combined review"
    And I should not see "Review by Teacher 1"
    And I should not see "Review by Teacher 2"

  Scenario: A non-editing teacher can download the combined report and their own submission
    Given I log in as "teacher3"
    # The combined report of all trainers is available to any reviewer.
    When I can download the verbal feedback report for "student1" rated by "" in "Test verbal feedback"
    # Their own submission as a single reviewer is available too.
    And I can download the verbal feedback report for "student1" rated by "teacher3" in "Test verbal feedback"

  Scenario: A non-editing teacher cannot download another trainer's single submission
    Given I log in as "teacher3"
    # Requesting another reviewer's submission is denied because teacher3 lacks view_all_reports.
    Then I get an exception "do not currently have permissions to do that" when I visit the verbal feedback report "report_download" for "student1" rated by "teacher1" in "Test verbal feedback"
