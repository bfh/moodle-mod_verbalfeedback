@mod @mod_verbalfeedback
Feature: Testing overview integration in verbalfeedback activity
  In order to summarize the verbalfeedback activity
  As a user
  I need to be able to see the verbalfeedback activity overview

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | enablecompletion |
      | Course 1 | C1        | 0        | 1                |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a verbalfeedback activity to course "Course 1" section "1" and I fill the form with:
      | Name        | Test verbal feedback             |
      | Description | Test verbal feedback description |
      | Template    | Default template                 |

  @javascript
  Scenario: The verbalfeedback activity overview report should generate log events
    Given the site is running Moodle version 5.0 or higher
    And I am on the "Course 1" "course > activities > verbalfeedback" page logged in as "teacher1"
    When I am on the "Course 1" "course" page logged in as "teacher1"
    And I navigate to "Reports" in current page administration
    And I click on "Logs" "link"
    And I click on "Get these logs" "button"
    Then I should see "Course activities overview page viewed"
    And I should see "viewed the instance list for the module 'verbalfeedback'"

  @javascript
  Scenario: The verbalfeedback activity index redirect to the activities overview
    Given the site is running Moodle version 5.0
    And I am on "Course 1" course homepage with editing mode on
    And I add the "Activities" block
    And I click on "Verbal feedbacks" "link" in the "Activities" "block"
    Then I should see "An overview of all activities in the course"
    And I should see "Name" in the "verbalfeedback_overview_collapsible" "region"
    And I should see "Actions" in the "verbalfeedback_overview_collapsible" "region"
