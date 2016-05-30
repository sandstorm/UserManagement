# Sandstorm.UserManagement Neos / Flow Package

## Features
This package provides models for users and basic mechanisms for Login/Logout, User activation and password reset.
(TODO: Write some more on features)

# How to use

## Setup
The package automatically exposes its routes via auto-inclusion in the package settings.
Attention: Any routes defined in the global Routes.yaml are loaded before this package's routes, so they may be overriden.
This is especially true for the default Flow subroutes, so make sure you have removed those from your global Routes.yaml.
If you can't remove them, just include the subroutes for this package manually before the Flow subroutes.

## Configuring the package
The following configuration options exist:
- swiftmailer
- overriding templates
- ... TODO

## Creating users via the CLI
The package exposes a command to create users. You can run

`./flow sandstormuser:create test@example.com password`

to create a test user. This will create a Neos user if you're using the package in Neos. You can assign
roles to the new user in the Neos backend afterwards. It doesn't work yet for standalone usage in Flow (see TODOS).

# Additional Settings for usage in Neos

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

# Extending the package

## Changing / overriding templates
TODO: Document this

## Overriding e-mail templates
TODO: Document this (together with config optns)

## Changing properties in the registration flow
TODO: document this (will work via RegistrationFlow.attributes and custom implementation of UserCreationServiceInterface).

# Known issues / TODOS

Feel free to submit issues/PRs :)

* The configuration options aren't documented yet.
* The standalone version does not provide a mechanism to create users via the CLI yet.
  It's also not possible to configure which roles newly registered users get yet.
  Furthermore, a Forwarding Service for the standalone case is missing.
* We haven't described all features in detail yet.
* The extension options are not documented yet.
* I18N.

# FAQ

* *What happens if the user did not receive the registration email?*

  Just tell the user to register again. In this case, previous unfinished registrations are discarded.
