@mod @mod_verbalfeedback
Feature: Grade a verbal feedback activity
  In order to collect and grade verbal feedback to the users in a course
  As a teacher
  I need to grade a verbalfeedback activity

  @javascript
  Scenario: Add a verbal feedback to a course and then grade
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | teacher2 | Teacher   | 2        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | teacher2 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the "multilang" filter is "on"
    And the "multilang" filter applies to "content and headings"
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Verbal feedback" to section "1" and I fill the form with:
      | Name        | Test verbal feedback             |
      | Description | Test verbal feedback description |
      | Template    | Default template                 |
    And I am on "Course 1" course homepage
    And I follow "Test verbal feedback"
    And I follow "Edit verbal feedback items"
    And I should see "Edit verbal feedback items"
    And the field "Maximum grade" matches value "100.00"
    And I set the field "Maximum grade" to "50"
    And I click on "Save" "button"
    And I follow "Test verbal feedback"
    And I follow "Make available"
    And I click on "Provide feedback" "link" in the "Student 1" "table_row"
    And I should see "Pending" in the "[data-region='status']" "css_element"
    And I change window size to "large"
    And "label[aria-label=\"Strongly disagree\"]" "css_element" should exist
    And I click on "label[aria-label=\"Somewhat agree\"]" "css_element" in the "The content is relevant, technically correct and supported by arguments." "table_row"
    And I click on "label[aria-label=\"Disagree\"]" "css_element" in the "The content is tailored to the target audience and the occasion." "table_row"
    And I click on "label[aria-label=\"Somewhat disagree\"]" "css_element" in the "The content presented is evidence of a thorough, independent examination of the topic." "table_row"
    And I click on "label[aria-label=\"Strongly disagree\"]" "css_element" in the "The introduction establishes contact, stimulates interest and provides orientation." "table_row"
    And I click on "label[aria-label=\"Strongly agree\"]" "css_element" in the "The main points build on each other and are in line with your purpose." "table_row"
    And I click on "label[aria-label=\"Agree\"]" "css_element" in the "The final is convincing and clearly signals the end of the presentation." "table_row"
    And I click on "label[aria-label=\"Somewhat agree\"]" "css_element" in the "The use of media is professional." "table_row"
    And I click on "label[aria-label=\"Somewhat disagree\"]" "css_element" in the "The visual aids are attractive and of good quality." "table_row"
    And I click on "label[aria-label=\"Somewhat agree\"]" "css_element" in the "The language style is typical for spoken texts and suitable for the target audience." "table_row"
    And I click on "label[aria-label=\"Disagree\"]" "css_element" in the "The style of speaking is convincing and facilitates comprehension." "table_row"
    And I click on "label[aria-label=\"Strongly disagree\"]" "css_element" in the "The performance is confident and the space is used effectively." "table_row"
    And I click on "label[aria-label=\"Strongly agree\"]" "css_element" in the "Eye contact is made with the entire audience and is maintained as much as possible." "table_row"
    And I click on "label[aria-label=\"Agree\"]" "css_element" in the "Facial expressions are friendly and authentic. Gestures emphasize what is being said." "table_row"
    And I press "Finalize evaluation"
    And I wait until the page is ready
    Then I should see "Student 1"
    And I log out
    And I log in as "teacher2"
    And I am on "Course 1" course homepage
    And I follow "Test verbal feedback"
    And I click on "Provide feedback" "link" in the "Student 1" "table_row"
    And I change window size to "large"
    And I click on "label[aria-label=\"Strongly disagree\"]" "css_element" in the "The content is relevant, technically correct and supported by arguments." "table_row"
    And I click on "label[aria-label=\"Agree\"]" "css_element" in the "The content is tailored to the target audience and the occasion." "table_row"
    And I click on "label[aria-label=\"Strongly agree\"]" "css_element" in the "The content presented is evidence of a thorough, independent examination of the topic." "table_row"
    And I click on "label[aria-label=\"Somewhat agree\"]" "css_element" in the "The introduction establishes contact, stimulates interest and provides orientation." "table_row"
    And I click on "label[aria-label=\"Somewhat disagree\"]" "css_element" in the "The main points build on each other and are in line with your purpose." "table_row"
    And I click on "label[aria-label=\"Somewhat disagree\"]" "css_element" in the "The final is convincing and clearly signals the end of the presentation." "table_row"
    And I click on "label[aria-label=\"Strongly disagree\"]" "css_element" in the "The use of media is professional." "table_row"
    And I click on "label[aria-label=\"Agree\"]" "css_element" in the "The visual aids are attractive and of good quality." "table_row"
    And I click on "label[aria-label=\"Disagree\"]" "css_element" in the "The language style is typical for spoken texts and suitable for the target audience." "table_row"
    And I click on "label[aria-label=\"Strongly agree\"]" "css_element" in the "The style of speaking is convincing and facilitates comprehension." "table_row"
    And I click on "label[aria-label=\"Somewhat agree\"]" "css_element" in the "The performance is confident and the space is used effectively." "table_row"
    And I click on "label[aria-label=\"Somewhat disagree\"]" "css_element" in the "Eye contact is made with the entire audience and is maintained as much as possible." "table_row"
    And I click on "label[aria-label=\"Strongly disagree\"]" "css_element" in the "Facial expressions are friendly and authentic. Gestures emphasize what is being said." "table_row"
    And I press "Save and return"
    Then I should see "Student 1"
    And I click on "Provide feedback" "link" in the "Student 1" "table_row"
    And I should see "In progress" in the "[data-region='status']" "css_element"
    And I press "Finalize evaluation"
    Then I should see "Student 1"
    And I should see "Completed" in the "Student 1" "table_row"
    And I click on "View feedback for user" "link" in the "Student 1" "table_row"
    Then I should see "24.50/50" in the "Weighted Ø" "table_row"
    And I should see "Weighted Ø: 2.50" in the "Content" "table_row"
    And I should see "Category weight: 20%" in the "Content" "table_row"
    And I should see "Weighted Ø: 2.67" in the "Structure" "table_row"
    And I should see "Category weight: 20%" in the "Structure" "table_row"
    And I should see "Weighted Ø: 2.25" in the "Media" "table_row"
    And I should see "Category weight: 20%" in the "Media" "table_row"
    And I should see "Weighted Ø: 2.50" in the "Speech" "table_row"
    And I should see "Category weight: 20%" in the "Speech" "table_row"
    And I should see "Weighted Ø: 2.33" in the "Body language" "table_row"
    And I should see "Category weight: 20%" in the "Body language" "table_row"
