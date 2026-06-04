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
      | student1 | Albrecht  | Dürer    | albrecht@example.com |
      | student2 | Edvard    | Munch    | edvard@example.com   |
      | student3 | Paul      | Gaugin   | paulg@example.com    |
      | student4 | Paul      | Klee     | paulk@example.com    |
      | student5 | Franz     | Marc     | franz@example.com    |
      | student6 | Edgar     | Degas    | franz@example.com    |
    And the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1        | 0        | 1         |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
      | student3 | C1     | student        |
      | student4 | C1     | student        |
      | student5 | C1     | student        |
      | student6 | C1     | student        |
    And the following "groups" exist:
      | name    | course | idnumber |
      | Class A | C1     | G1       |
      | Class B | C1     | G2       |
    And the following "group members" exist:
      | user     | group |
      | student1 | G1    |
      | student2 | G2    |
      | student3 | G1    |
      | student4 | G2    |
      | student5 | G1    |
      | student6 | G2    |
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a verbalfeedback activity to course "Course 1" section "1" and I fill the form with:
      | Name        | Test verbal feedback             |
      | Description | Test verbal feedback description |
      | Template    | Default template                 |
      | Group mode  | Separate groups                  |
    And I am on "Course 1" course homepage
    And I am on the "Test verbal feedback" "verbalfeedback activity" page logged in as teacher1
    And I follow "Edit verbal feedback items"
    And I should see "Edit verbal feedback items"
    And the field "Maximum grade" matches value "100.00"
    And I set the field "Maximum grade" to "50"
    And I click on "Save" "button"
    And I am on the "Test verbal feedback" "verbalfeedback activity" page logged in as teacher1
    And I follow "Make available"
    # See all participants with no filter set.
    And I confirm "All participants" exists in the "Search groups" search combo box
    And I should see "Albrecht Dürer"
    And I should see "Edvard Munch"
    And I should see "Paul Gaugin"
    And I should see "Paul Klee"
    And I should see "Franz Marc"
    And I should see "Edgar Degas"
    # Select group: class A.
    When I click on "Class A" in the "Search groups" search combo box
    Then I should see "Albrecht Dürer"
    And I should see "Paul Gaugin"
    And I should see "Franz Marc"
    And I should not see "Edvard Munch"
    And I should not see "Paul Klee"
    And I should not see "Edgar Degas"
    # Select group: class B.
    And I click on "Class B" in the "Search groups" search combo box
    Then I should not see "Albrecht Dürer"
    And I should not see "Paul Gaugin"
    And I should not see "Franz Marc"
    And I should see "Edvard Munch"
    And I should see "Paul Klee"
    And I should see "Edgar Degas"
    # For two students provide feedback, complete it for Paul Klee and just save it for Edgar Degas.
    Then I click on "Provide feedback" "link" in the "Paul Klee" "table_row"
    And I press "Finalize evaluation"
    And I click on "Provide feedback" "link" in the "Edgar Degas" "table_row"
    And I click on "label[aria-label=\"Somewhat agree\"]" "css_element" in the "The use of media is professional." "table_row"
    And I click on "label[aria-label=\"Disagree\"]" "css_element" in the "The visual aids are attractive and of good quality." "table_row"
    And I press "Save and return"
    # Again show all partipants (both groups)
    And I click on "All participants" in the "Search groups" search combo box
    # And filter now by status, pending assessment should exclude the two students that have been graded (patially).
    And I set the field "Status" to "Pending"
    Then I should not see "Edgar Degas"
    And I should not see "Paul Klee"
    And I should see "Edvard Munch"
    And I should see "Paul Gaugin"
    And I should see "Franz Marc"
    And I should see "Albrecht Dürer"
    # Do an user search.
    Then I set the field "Search users" to "pa"
    And I confirm "Paul Gaugin" exists in the "Search users" search combo box
    And I confirm "Paul Klee" does not exist in the "Search users" search combo box
