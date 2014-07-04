Feature: As an admin user I can create some hosts
  @javascript
  Scenario: I log in and visit one project
    Given I am logged as an admin user "admin" "admin"
    Given I am on "/"
    Then I should see "Wapistrano Status"
    Given I am on "/hosts/"
    Then I follow "Add new"
    Then I wait for text "Add new host" to appear
    Given I fill in "wapistrano_corebundle_hosts_name" with "127.0.0.1"
    Given I fill in "wapistrano_corebundle_hosts_alias" with "testHost"
    Given I fill in "wapistrano_corebundle_hosts_description" with "A description for testHost"
    Then I press "Save"
    Then I wait for text "Hosts list" to appear