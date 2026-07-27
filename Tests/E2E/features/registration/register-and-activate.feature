@registration
Feature: Registration and account activation

  Scenario: A new user can register and activate their account via the emailed link
    When I open the registration form
    And I register with email "newuser@example.com", password "Sup3rSecret!1", first name "Ada" and last name "Lovelace"
    Then I should see the registration confirmation
    When I open the activation link that was emailed to "newuser@example.com"
    Then I should see the account activated
    When I open the login page
    And I log in with email "newuser@example.com" and password "Sup3rSecret!1"
    Then I should be logged in

  Scenario: Registering with mismatched password confirmation shows a validation error
    When I open the registration form
    And I register with email "mismatch@example.com", password "Sup3rSecret!1" and password confirmation "Different!2", first name "Ada" and last name "Lovelace"
    Then I should still see the registration form

  Scenario: An already-used activation link no longer works
    When I open the registration form
    And I register with email "reused@example.com", password "Sup3rSecret!1", first name "Ada" and last name "Lovelace"
    And I open the activation link that was emailed to "reused@example.com"
    And I open the activation link that was emailed to "reused@example.com"
    Then I should see that the activation link is not valid

  Scenario: An expired activation link no longer works
    When I open the registration form
    And I register with email "expired@example.com", password "Sup3rSecret!1", first name "Ada" and last name "Lovelace"
    And I wait for the activation token to expire
    And I open the activation link that was emailed to "expired@example.com"
    Then I should see that the activation link is not valid
