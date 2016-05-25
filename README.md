# Sandstorm.UserManagement Flow Package

This package provides models for users and basic mechanisms for Login/Logout, User activation and password reset.

# How to use (STANDALONE)


## Setup
The package automatically exposes its routes via auto-inclusion in the package settings.
Attention: Any routes defined in the global Routes.yaml are loaded before this package's routes, so they may be overriden.
This is especially true for the default Flow subroutes, so make sure you have removed those from your global Routes.yaml.
If you can't remove them, just include the subroutes for this package manually before the Flow subroutes.

## Creating users via the CLI
The package exposes a command to create users with arbitrary roles. You can run
`./flow user:create test@sandstorm.de cccccccc Bastian Heist Sandstorm.UserManagement:User`
to create a test user.

# How to use (NEOS)

Settings.yaml of your package:

```

TYPO3:
  Flow:
    security:
      authentication:
        providers:
          'Typo3BackendProvider':
            requestPatterns:
              'Flowpack\Neos\FrontendLogin\Security\NeosRequestPattern': 'backend'
          'Sandstorm.UserManagement:Login':
            provider: 'PersistedUsernamePasswordProvider'
            requestPatterns:
              'Flowpack\Neos\FrontendLogin\Security\NeosRequestPattern': 'frontend'

```

# Extending the package

## Changing templates
The default templates are written with Foundation for Sites 6 in mind and will look OK if you are using that.
Still, changing templates is easily possible:
...Views.yaml


# TODO List

* Re-Implement Forgot Password
* Duplicate User ID validator
* check that it works outside Neos
* Move Flowpack\Neos\FrontendLogin\Security\NeosRequestPattern to this package


# FAQ

* *What happens if the user did not receive the registration email?*

  Just tell the user to register again. In this case, previous unfinished registrations are discarded.
