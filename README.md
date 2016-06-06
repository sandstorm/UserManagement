# Sandstorm.UserManagement Neos / Flow Package

## Features
This package works in Neos CMS and Flow and provides the following functionalities:

* Registration of (frontend) users via a registration form
* Sending out an e-mail for account confirmation
* Login of registered (frontend) users via a login form
* "Forgotten password" with password reset e-mail

# Configuration

## Setup
The package automatically exposes its routes via auto-inclusion in the package settings.
Attention: Any routes defined in the global Routes.yaml are loaded before this package's routes, so they may be overriden.
This is especially true for the default Flow subroutes, so make sure you have removed those from your global Routes.yaml.
If you can't remove them, just include the subroutes for this package manually before the Flow subroutes.

## Base configuration options
These are the basic configuration options for e-mails, timeouts etc. You will usually want to adapt these to your application.
```
Sandstorm:
  UserManagement:
    # Validity timespan for the activation token for newly registered users.
    activationTokenTimeout: '2 days'
    # Validity timespan for the token used to reset passwords.
    resetPasswordTokenTimeout: '4 hours'
    # Email settings
    email:
      # Sender Address
      senderAddress: 'test@example.com'
      # Sender name - will be merged with senderAddress to something
      # like "Sandstorm Usermanagement Package <test@example.com>"
      senderName: 'Sandstorm Usermanagement Package'
      # Subject line for the account confirmation email
      subjectActivation: 'Please confirm your account'
      # Subject line for the password reset email
      subjectResetPassword: 'Password reset'
      # Template package to read the email templates from - this can be used to override
      # e-mail templates. They are expected in the package given here, in the folder
      # <Package>/Resources/Private/EmailTemplates.
      templatePackage: 'Sandstorm.UserManagement'
    # An array of roles which are assigned to users after they activate their account.
    rolesForNewUsers: []
```

## Configuring SwiftMailer
The UserManagement package requires SwiftMailer to send out e-mails. Please check the swiftmailer package's
configuration options (https://github.com/neos/swiftmailer) in order to configure SMTP credentials.

## Additional Settings for usage in Neos
Add the following to your package's (or the global) `Settings.yaml`. This creates a separate authentication provider so Neos can
distinguish between frontend and backend logins.

```

TYPO3:
  Flow:
    security:
      authentication:
        providers:
          'Typo3BackendProvider':
            requestPatterns:
              'Sandstorm\UserManagement\Security\NeosRequestPattern': 'backend'
          'Sandstorm.UserManagement:Login':
            provider: 'PersistedUsernamePasswordProvider'
            requestPatterns:
              'Sandstorm\UserManagement\Security\NeosRequestPattern': 'frontend'

```

# Usage

## Creating users via the CLI
The package exposes a command to create users. You can run

`./flow sandstormuser:create test@example.com password firstName lastName`

to create a test user. This will create a Neos user if you're using the package in Neos. You can assign
roles to the new user in the Neos backend afterwards. It doesn't work yet for standalone usage in Flow (see TODOS).

## Redirect after login/logout
TODO: Document this (redirectAfterLogin / redirectAfterLogout property of the loginform node)

## Checking for a logged-in user in your templates
There is a ViewHelper available that allows you to check if somebody is logged into the frontend. Here's an example:

```
{namespace usermanagement=Sandstorm\UserManagement\ViewHelpers}

<usermanagement:ifAuthenticated authenticationProviderName="Sandstorm.UserManagement:Login">
  <f:then>
    You are currently logged in.
  </f:then>
  <f:else>
    You are not logged in!
  </f:else>
</usermanagement:ifAuthenticated>

```

# Extending the package

## Changing / overriding templates
TODO: Document this

## Overriding e-mail templates
As documented in the configuration options above, overriding e-mail templates is easy:
* Copy the `EmailTemplates` folder from the UserManagement's `Resources/Private` folder into your
  own package and modify the templates to your heart's desire.
* Then, set the `email.templatePackage` configuration option to that package's name. Done!

## Changing or adding properties to the registration flow
TODO: document this (will work via RegistrationFlow.attributes and custom implementation of UserCreationServiceInterface).

# Known issues

Feel free to submit issues/PRs :)

* When you logout while on a restricted page in Neos, you will not be redirected to
  another page, but will be shown a "page not found" error after logout.

# TODOs

* The standalone version does not provide a mechanism to create users via the CLI yet.
  Furthermore, a Forwarding Service for the standalone case is missing.
* We haven't described all features in detail yet.
* I18N.
* Tests.

# FAQ

* *What happens if the user did not receive the registration email?*
  Just tell the user to register again. In this case, previous unfinished registrations are discarded.

# License
MIT.
https://opensource.org/licenses/MIT
