Feature: As an admin user I can fill project's stage
  @javascript
  Scenario: I log in and visit one project
    Given I am logged as an admin user "admin" "admin"
    Given I am on "/"
    Then I should see "Wapistrano Status"
    Given I am on "/projects/"
    Then I click on "xpath" "//a[text()='Project One']"
    Then I follow "testStageUpdated"
    Then I should see "Alert on deploy"
    Given I click on "css" ".popConfigurationAjax"
    Then I wait for text "Add a configuration" to appear
    Given I fill in "wapistrano_corebundle_projects_name" with "testStageConfig"
    Given I fill in "wapistrano_corebundle_projects_value" with "testValue"
    Then I press "Save"
    Then I wait for text "testStageConfig" to appear

  @javascript
  Scenario: I log in and visit one project
    Given I am logged as an admin user "admin" "admin"
    Given I am on "/"
    Then I should see "Wapistrano Status"
    Given I am on "/projects/"
    Then I click on "xpath" "//a[text()='Project One']"
    Then I follow "testStageUpdated"
    Then I should see "Alert on deploy"
    Given I click on "xpath" "//a[contains(@class,'popConfigurationAjax')]/i[contains(@class,'glyphicon-edit')]"
    Then I wait for text "Edit a configuration" to appear
    Given I fill in "wapistrano_corebundle_projects_name" with "testStageConfigUpdated"
    Given I fill in "wapistrano_corebundle_projects_value" with "testValue"
    Then I press "Save"
    Then I wait for text "testStageConfig" to appear