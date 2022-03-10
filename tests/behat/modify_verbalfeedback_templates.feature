@mod @mod_verbalfeedback
Feature: Modify a verbal feedback activity template
  In order to configure my verbal feedback in a course
  As a teacher
  I need to modify my verbal feedback template

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |

  @javascript
  Scenario: Edit a verbal feedback activity template
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Verbal feedback" to section "1" and I fill the form with:
      | Name                  | Test verbal feedback             |
      | Description           | Test verbal feedback description |
      | Template              | Default template                 |
      | Grade to pass         | 40                               |
    And I am on "Course 1" course homepage with editing mode on
    And I follow "Test verbal feedback"
    And I click on "#region-main-settings-menu [role=button]" "css_element"
    And I choose "Manage templates" in the open action menu
    And I click on "Edit" "link" in the "Default template" "table_row"
    And I set the field "id_name" to "Custom template"
    And I press "id_submitbutton"
    Then I should see "Custom template"

  @javascript
  Scenario: Delete a verbal feedback activity template
   When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Verbal feedback" to section "1" and I fill the form with:
      | Name                  | Test verbal feedback             |
      | Description           | Test verbal feedback description |
      | Template              | Default template                 |
      | Grade to pass         | 40                               |
    And I am on "Course 1" course homepage with editing mode on
    And I follow "Test verbal feedback"
    And I click on "#region-main-settings-menu [role=button]" "css_element"
    And I choose "Manage templates" in the open action menu
    And I click on "Delete" "link" in the "Default template" "table_row"
    And I press "id_submitbutton"
   Then I should not see "Default template"

  @javascript
  Scenario: Delete a verbal feedback activity category template
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Verbal feedback" to section "1" and I fill the form with:
      | Name                  | Test verbal feedback             |
      | Description           | Test verbal feedback description |
      | Template              | Default template                 |
      | Grade to pass         | 40                               |
    And I am on "Course 1" course homepage with editing mode on
    And I follow "Test verbal feedback"
    And I click on "#region-main-settings-menu [role=button]" "css_element"
    And I choose "Manage templates" in the open action menu
    And I follow "Template categories"
    And I click on "Delete" "link" in the "structure" "table_row"
    And I press "id_submitbutton"
   Then I should not see "structure"

  @javascript
  Scenario: Delete a verbal feedback activity criteria template
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Verbal feedback" to section "1" and I fill the form with:
      | Name                  | Test verbal feedback             |
      | Description           | Test verbal feedback description |
      | Template              | Default template                 |
      | Grade to pass         | 40                               |
    And I am on "Course 1" course homepage with editing mode on
    And I follow "Test verbal feedback"
    And I click on "#region-main-settings-menu [role=button]" "css_element"
    And I choose "Manage templates" in the open action menu
    And I follow "Template criteria"
    And I click on "Delete" "link" in the "The content is tailored to the target audience and the occasion." "table_row"
    And I press "id_submitbutton"
   Then I should not see "The content is tailored to the target audience and the occasion."

  @javascript
  Scenario: Delete a verbal feedback activity language
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Verbal feedback" to section "1" and I fill the form with:
      | Name                  | Test verbal feedback             |
      | Description           | Test verbal feedback description |
      | Template              | Default template                 |
      | Grade to pass         | 40                               |
    And I am on "Course 1" course homepage with editing mode on
    And I follow "Test verbal feedback"
    And I click on "#region-main-settings-menu [role=button]" "css_element"
    And I choose "Manage templates" in the open action menu
    And I follow "Language"
    And I click on "Delete" "link" in the "fr" "table_row"
    And I press "id_submitbutton"
   Then I should not see "fr"
