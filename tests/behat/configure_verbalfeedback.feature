@mod @mod_verbalfeedback
Feature: Configure a verbal feedback activity
  In order to collect verbal feedback to the users in a course
  As a teacher
  I need to configure a verbal feedback activity

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

  @javascript
  Scenario: Add a verbal feedback to a course without questions
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Verbal feedback" to section "1" and I fill the form with:
      | Name        | Test verbal feedback             |
      | Description | Test verbal feedback description |
      | Template    | Default template                 |
    And I am on "Course 1" course homepage
    And I follow "Test verbal feedback"
    And I follow "Edit verbal feedback items"
    Then I should see "Edit verbal feedback items"

  @javascript
  Scenario: Add a verbal feedback to a course using the default template and weight one category with 0 and one criteria with 0
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Verbal feedback" to section "1" and I fill the form with:
      | Name        | Test verbal feedback             |
      | Description | Test verbal feedback description |
      | Template    | Default template                 |
    And I am on "Course 1" course homepage
    And I follow "Test verbal feedback"
    And I follow "Edit verbal feedback items"
    And I should see "Edit verbal feedback items"
    And I set the field "Percentage" in the "Structure" "table_row" to "0%"
    And I set the field "Percentage" in the "Body language" "table_row" to "25%"
    And I set the field "Percentage" in the "Content" "table_row" to "25%"
    And I set the field "Percentage" in the "Speech" "table_row" to "25%"
    And I set the field "Percentage" in the "Media" "table_row" to "25%"
    And I set the field "Multiplier" in the "The main points build on each other and are in line with your purpose." "table_row" to "0.00"
    And I follow "Preview"
    And I should not see "Structure"
    Then I should not see "The main parts build on each other and are purposeful."
