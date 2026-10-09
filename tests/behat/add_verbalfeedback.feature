@mod @mod_verbalfeedback
Feature: Add a verbal feedback activity
  In order to collect verbal feedback to the users in a course
  As a teacher
  I need to add a verbal feedback activity to a moodle course

  @javascript
  Scenario: Add a verbalfeedback to a course without releasing it
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a verbalfeedback activity to course "Course 1" section "1" and I fill the form with:
      | Name                  | Test verbal feedback             |
      | Description           | Test verbal feedback description |
      | Template              | Default template                 |
      | Grade to pass         | 40                               |
    And I am on the "Test verbal feedback" "verbalfeedback activity editing" page logged in as teacher1
    And I expand all fieldsets
    And the field "Grade to pass" matches value "40"
    And I log out
    And I am on the "Test verbal feedback" "verbalfeedback activity" page logged in as student1
    Then I should see "The verbal feedback activity is not yet ready. Please try again later."

  @javascript
  Scenario: Add a verbalfeedback to a course and then release it
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a verbalfeedback activity to course "Course 1" section "1" and I fill the form with:
      | Name                  | Test verbal feedback             |
      | Description           | Test verbal feedback description |
      | Template              | Default template                 |
      | Grade to pass         | 40                               |
    And I am on "Course 1" course homepage with editing mode on
    And I am on the "Test verbal feedback" "verbalfeedback activity" page logged in as teacher1
    And I click on "Edit verbal feedback items" "link"
    And the field "Maximum grade" matches value "100.00"
    And I set the field "Maximum grade" to "50"
    And I click on "Save" "button"
    And I am on the "Test verbal feedback" "verbalfeedback activity" page logged in as teacher1
    And I click on "Edit verbal feedback items" "link"
    And the field "Maximum grade" matches value "50.00"

  @javascript
  Scenario: Add a verbalfeedback with only one category to a course
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And I log in as "admin"
    # Create a new template with a single category by going straight to the template list via URL.
    And I visit "/mod/verbalfeedback/template_list.php"
    And I follow "New template"
    And I set the field "Name" to "One category template"
    And I set the field "Description" to "Template with a single category"
    And I click on ".form-check-input" "css_element"
    And I press "Save changes"
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a verbalfeedback activity to course "Course 1" section "1" and I fill the form with:
      | Name                  | Test verbal feedback             |
      | Description           | Test verbal feedback description |
      | Template              | One category template            |
      | Grade to pass         | 40                               |
    And I am on "Course 1" course homepage with editing mode on
    And I am on the "Test verbal feedback" "verbalfeedback activity" page logged in as teacher1
    And I follow "Edit verbal feedback items"
    # Make sure that there are percent rates > 50%
    And I wait until "//*[text()='Content']/../..//option[@value='0.45']" "xpath_element" exists
    And I wait until "//*[text()='Content']/../..//option[@value='0.55']" "xpath_element" exists
    And I set the field with xpath "//*[text()='Content']/../..//select" to "1.0"
