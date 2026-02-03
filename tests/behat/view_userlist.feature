@mod @mod_verbalfeedback
Feature: User management in the verbal feedback activity
  In order to collect and grade verbal feedback to the users in a course
  As a teacher
  I need to filter users in the verbal feedback activity

  @javascript
  Scenario: Add a verbal feedback to a course and then grade
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | One      | student1@example.com |
      | student2 | Student   | Two      | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
    And the following "groups" exist:
      | name    | course | idnumber |
      | Class A | C1     | G1       |
      | Class B | C1     | G2       |
    And the following "group members" exist:
      | user     | group |
      | student1 | G1    |
      | student2 | G2    |
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a verbalfeedback activity to course "Course 1" section "1" and I fill the form with:
      | Name        | Test verbal feedback             |
      | Description | Test verbal feedback description |
      | Template    | Default template                 |
    And I am on "Course 1" course homepage
    And I am on the "Test verbal feedback" "verbalfeedback activity" page logged in as teacher1
    And I follow "Edit verbal feedback items"
    And I should see "Edit verbal feedback items"
    And the field "Maximum grade" matches value "100.00"
    And I set the field "Maximum grade" to "50"
    And I click on "Save" "button"
    And I am on the "Test verbal feedback" "verbalfeedback activity" page logged in as teacher1
    And I follow "Make available"
    And I should see "All participants" in the "Groups" "select"
    And I should see "Student One"
    And I should see "Student Two"
    When I set the field "Groups" to "Class A"
    Then I should see "Student One"
    And I should not see "Student Two"
    When I set the field "Groups" to "Class B"
    Then I should not see "Student One"
    And I should see "Student Two"
