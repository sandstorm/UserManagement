@profile
Feature: Edit profile

  Background:
    Given an activated user "profile-user@example.com" with password "Sup3rSecret!1" exists

  Scenario: A logged-in user changes their password via the profile page and can log in with it
    When I open the login page
    And I log in with email "profile-user@example.com" and password "Sup3rSecret!1"
    And I open the profile page
    And I set a new profile password "Ch4ngedSecret!2"
    Then I should be back on the profile page
    When I log out
    And I open the login page
    And I log in with email "profile-user@example.com" and password "Ch4ngedSecret!2"
    Then I should be logged in
