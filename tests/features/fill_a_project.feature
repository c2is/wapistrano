Feature: login TEST
  @javascript
  Scenario: I log in and visit one project
    Given I am on "/login"
    Given I fill in "username" with "admin"
    Given I fill in "password" with "admin"
    Given I press "login"
    Then I should see "Wapistrano Status"
    Given I am on "/projects/"
    Then I click on "xpath" "//a[text()='Project One']"
    Then I should see "Project One Description"
