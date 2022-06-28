@mod @mod_verbalfeedback
Feature: create a verbal feedback activity template
  In order to use my verbal feedback in a course
  As a teacher
  I need to create a verbal feedback template

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
  Scenario: Create a verbal feedback activity template
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Verbal feedback" to section "1" and I fill the form with:
      | Name                  | Test verbal feedback             |
      | Description           | Test verbal feedback description |
      | Template              | Default template                 |
      | Grade to pass         | 40                               |
    And I am on "Course 1" course homepage with editing mode on
    And I am on the "Test verbal feedback" "verbalfeedback activity" page
    And I navigate to "Verbal feedback templates" in current page administration
    And I follow "New template"
    And I set the field "id_name" to "My new template"
    And I set the field "id_description" to "My new verbal feedback template"
    And I press "id_submitbutton"
    Then I should see "My new template"
    And I follow "Template categories"
    And I follow "New category"
    And I set the field "id_unique_name" to "New template category 1"
    And I press "id_submitbutton"
    Then I should see "New template category 1"
    And I follow "New category"
    And I set the field "id_unique_name" to "New template category 2"
    And I press "id_submitbutton"
    Then I should see "New template category 2"
    And I follow "Templates"
    And I click on "Edit" "link" in the "My new template" "table_row"
    And I click on ".form-check-input" "css_element" in the "New template category 1" "fieldset"
    And I click on ".form-check-input" "css_element" in the "New template category 2" "fieldset"
    And I press "id_submitbutton"
    And I follow "Template criteria"
    And I follow "New criterion"
    And I set the field "id_localized_strings_1_string" to "New template criterion 1"
    And I set the field "id_subrating_title_en_string_0" to "New template criterion 1 title"
    And I set the field "id_subrating_verynegative_en_string_0" to "New template criterion 1 very negative"
    And I set the field "id_subrating_negative_en_string_0" to "New template criterion 1 negative"
    And I set the field "id_subrating_positive_en_string_0" to "New template criterion 1 positive"
    And I set the field "id_subrating_verypositive_en_string_0" to "New template criterion 1 very positive"
    And I press "id_submitbutton"
    And I follow "New criterion"
    And I set the field "id_localized_strings_1_string" to "New template criterion 2"
    And I set the field "id_subrating_title_en_string_0" to "New template criterion 2 title"
    And I set the field "id_subrating_verynegative_en_string_0" to "New template criterion 2 very negative"
    And I set the field "id_subrating_negative_en_string_0" to "New template criterion 2 negative"
    And I set the field "id_subrating_positive_en_string_0" to "New template criterion 2 positive"
    And I set the field "id_subrating_verypositive_en_string_0" to "New template criterion 2 very positive"
    And I press "id_submitbutton"
