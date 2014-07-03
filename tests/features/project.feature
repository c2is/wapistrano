Feature: As an admin user I can fill project's parameters
  @javascript
  Scenario: I log in and visit one project
    Given I am logged as an admin user "admin" "admin"
    Given I am on "/"
    Then I should see "Wapistrano Status"
    Given I am on "/projects/"
    Then I click on "xpath" "//a[text()='Project One']"
    Then I should see "Project One Description"

  @javascript
  Scenario: I fill some configurations in a project
    Given I am logged as an admin user "admin" "admin"
    Given I am on "/projects/"
    Then I click on "xpath" "//a[text()='Project One']"
    Then I should see "Project One Description"
    Given I click on "css" ".popConfigurationAjax"
    Then I wait for text "Add a configuration" to appear
    Given I fill in "wapistrano_corebundle_projects_name" with "test"
    Given I fill in "wapistrano_corebundle_projects_value" with "testValue"
    Then I press "Save"

  @javascript
  Scenario: I update some configurations in a project
    Given I am logged as an admin user "admin" "admin"
    Given I am on "/projects/"
    Then I click on "xpath" "//a[text()='Project One']"
    Then I should see "Project One Description"
    Given I click on "xpath" "//a[contains(@class,'popConfigurationAjax')]/i[contains(@class,'glyphicon-edit')]"
    Then I wait for text "Edit a configuration" to appear
    Given I fill in "wapistrano_corebundle_projects_name" with "testConfigUpdated"
    Given I fill in "wapistrano_corebundle_projects_value" with "testValue"
    Then I press "Save"
    Then I wait for text "testConfigUpdated" to appear

  @javascript
  Scenario: I fill some stages in a project
    Given I am logged as an admin user "admin" "admin"
    Given I am on "/projects/"
    Then I click on "xpath" "//a[text()='Project One']"
    Then I should see "Project One Description"
    Given I click on "css" ".popStageAjax"
    Then I wait for text "Add a stage" to appear
    Given I fill in "wapistrano_corebundle_stages_name" with "test"
    Given I fill in "wapistrano_corebundle_stages_alertEmails" with "a.cianfarani@c2is.fr"
    Then I press "Save"

  @javascript
  Scenario: I update some stages in a project
    Given I am logged as an admin user "admin" "admin"
    Given I am on "/projects/"
    Then I click on "xpath" "//a[text()='Project One']"
    Then I should see "Project One Description"
    Given I click on "xpath" "//a[contains(@class,'popStageAjax')]/i[contains(@class,'glyphicon-edit')]"
    Then I wait for text "Edit a stage" to appear
    Given I fill in "wapistrano_corebundle_stages_name" with "testStageUpdated"
    Given I fill in "wapistrano_corebundle_stages_alertEmails" with "testValue"
    Then I press "Save"
    Then I wait for text "testStageUpdated" to appear



