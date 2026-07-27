@reset-password
Feature: Forgot / reset password

  Background:
    Given an activated user "reset-user@example.com" with password "OldSecret!1" exists

  Scenario: A user can reset their password via the emailed link
    When I request a password reset for "reset-user@example.com"
    Then I should see the password reset confirmation
    When I open the password reset link that was emailed to "reset-user@example.com"
    And I set a new password "NewSecret!2"
    Then I should see the password was updated
    When I open the login page
    And I log in with email "reset-user@example.com" and password "NewSecret!2"
    Then I should be logged in

  Scenario: Requesting a reset for an unknown email does not reveal whether the account exists
    When I request a password reset for "unknown@example.com"
    Then I should see the password reset confirmation

  Scenario: An expired reset link no longer works
    When I request a password reset for "reset-user@example.com"
    And I wait for the reset token to expire
    And I open the password reset link that was emailed to "reset-user@example.com"
    Then I should see that the reset link is not valid
