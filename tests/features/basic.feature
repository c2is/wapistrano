Feature: login

  Scenario: Attempting to access projects list page I am redirected where the login form
    Given I am on "/projects/"
    Then the response status code should be 200
    Then I should see "Login"