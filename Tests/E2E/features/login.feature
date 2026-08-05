@default-context
Feature: Login flow with default settings

  Background:
    Given A user with username "admin", password "password" and role "Neos.Neos:Administrator" exists
    And A user with username "editor", password "password" and role "Neos.Neos:Editor" exists

  Scenario: Admin user can log in
    When I log in with username "admin" and password "password"
    Then I should see the Neos content page

  Scenario: Editor user can log in
    When I log in with username "editor" and password "password"
    Then I should see the Neos content page
