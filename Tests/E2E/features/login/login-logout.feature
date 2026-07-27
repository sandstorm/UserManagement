@login
Feature: Login and logout

  Background:
    Given an activated user "login-user@example.com" with password "Sup3rSecret!1" exists

  Scenario: A registered user can log in and log out
    When I open the login page
    And I log in with email "login-user@example.com" and password "Sup3rSecret!1"
    Then I should be logged in
    When I log out
    Then I should be logged out

  Scenario: Logging in with a wrong password does not log the user in
    When I open the login page
    And I log in with email "login-user@example.com" and password "wrong-password"
    Then I should still see the login form
