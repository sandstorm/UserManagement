# Sandstorm.UserManagement Neos / Flow Package

# 0. Features
This package works in Neos CMS and Flow and provides the following functionality:

* Registration of (frontend) users via a registration form
* Sending out an e-mail for account confirmation
* Login of registered (frontend) users via a login form
* "Forgotten password" with password reset e-mail

# 1. Compatibility and Maintenance
Sandstorm.UserManagement is currently being maintained for Neos 2.3 LTS and Neos 3.x.

| Neos / Flow Version        | Sandstorm.UserManagement Version | Branch | Maintained |
|----------------------------|----------------------------------|--------|------------|
| Neos 3.x, Flow 4.x         | 5.x                              | master | Yes        |
| Neos 2.3 LTS, Flow 3.3 LTS | 3.x                              | 3.0    | Bugfixes   |
| Neos 2.2, Flow 3.2         | 1.x                              | No     | No         |

## Breaking changes in Version 5.x
### Configuration Changes
Since I've removed the direct dependency to swiftmailer in favor of the Sandstorm/TemplateMailer package
(which provides css inlining), the EmailService in this package was removed. This means that you will need
to change some of your config options, because they are now set in the Sandstorm.TemplateMailer config path
instead of inside the Sandstom.UserManagement path. Please refer to the [Sandstorm/TemplateMailer Documentation](https://github.com/sandstorm/TemplateMailer)
for instructions on how to set the following configurations:

* senderAddress
* senderName
* templatePackage

Hint: to override the sender address for this package, you will need the following setting:
```YAML
Sandstorm:
  TemplateMailer:
    senderAddresses:
      sandstorm_usermanagement_sender_email: # You need to use this exact key to override the UserManagement defaults
        name: Your-App
        address: yoursenderemail@yourapp.de
```

### Changes to Email Templates
In the registration email templates, two variables are no longer available by default:
* "applicationName" (filled with configured email senderAddress)
* "email" (filled with the email address the mail is sent to)
However, in the registration email, "registrationFlow" is now available, which gives access to the email as well to all
other information the user has entered during the registration process (as long as it is stored in the RegistrationFlow object).

# 2. Configuration

## Setup
There are the basic config steps:
1. Run `./flow doctrine:migrate` after you add this package to install its model. The package automatically exposes its routes
via auto-inclusion in the package settings. Attention: Any routes defined in the global `Routes.yaml` are loaded before this package's 
routes, so they may be overriden. This is especially true for the default Flow subroutes, so make sure you have removed those from your global `Routes.yaml`.
If you can't remove them, just include the subroutes for this package manually before the Flow subroutes.

2. Require this package in your own package's `composer.json`. This will inform Flow that it needs to load UserManagement before
your packages, which allows you to override config and will make sure authorizations work correctly. Keep in mind that you have to add this into all
packages that use features from user management - very important if your site is split into multiple packages or plugins.
Here's an example:
```
{
    "description": "Your Site Package",
    "type": "neos-site", (or "neos-package" if you're using Flow only or building a Plugin)
    "require": {
        "neos/neos": "*",
        "sandstorm/usermanagement": "*"
    }
    ...more settings here...
}
```

3. Run `./flow neos.flow:package:rescan` to regenerate to order in which all your packages are loaded.

4. Add and adapt the configuration settings below to your config (make sure to not miss the special Neos settings).

## Basic configuration options
These are the basic configuration options for e-mails, timeouts etc. You will usually want to adapt these to your application.
```
Sandstorm:
  UserManagement:
    # Validity timespan for the activation token for newly registered users.
    activationTokenTimeout: '2 days'
    # Validity timespan for the token used to reset passwords.
    resetPasswordTokenTimeout: '4 hours'
    # The message that appears if a user could not be logged in.
    authFailedMessage:
      title: 'Login nicht möglich'
      body: 'Sie haben ungültige Zugangsdaten eingegeben. Bitte versuchen Sie es noch einmal.'
    # Email settings
    email:
      # Subject line for the account confirmation email
      subjectActivation: 'Please confirm your account'
      # Subject line for the password reset email
      subjectResetPassword: 'Password reset'
    # An array of roles which are assigned to users after they activate their account.
    rolesForNewUsers: []
```

## Additional Settings for usage in Neos
You should switch the implementation of the Redirect and User Creation Services to the Neos services. Add this to your `Objects.yaml`:
```
# Use the Neos services
Sandstorm\UserManagement\Domain\Service\RedirectTargetServiceInterface:
  className: 'Sandstorm\UserManagement\Domain\Service\Neos\NeosRedirectTargetService'
Sandstorm\UserManagement\Domain\Service\UserCreationServiceInterface:
  className: 'Sandstorm\UserManagement\Domain\Service\Neos\NeosUserCreationService'
```

Be aware that the `NeosUserCreationService` requires a non-empty firstName and lastName to be present in the `RegistrationFlow` attributes
as it's in the templates of this package.

### Neos 3.0 and higher

Add the following to your package's (or the global) `Settings.yaml`. This creates a separate authentication provider so Neos can
distinguish between frontend and backend logins.

```
Neos:
  Flow:
    security:
      authentication:
        providers:
          'Neos.Neos:Backend':
            requestPatterns:
              Sandstorm.UserManagement:NeosBackend:
                pattern: Sandstorm\UserManagement\Security\NeosRequestPattern
                patternOptions:
                  'area': 'backend'
          'Sandstorm.UserManagement:Login':
            provider: PersistedUsernamePasswordProvider
            requestPatterns:
              Sandstorm.UserManagement:NeosFrontend:
                pattern: Sandstorm\UserManagement\Security\NeosRequestPattern
                patternOptions:
                  'area': 'frontend'

```

### Neos 2.3 (Flow 3.3)

Before Neos 3.0, the `Neos.Neos:Backend` authentication provider was called `Typo3BackendProvider`. Replace `Neos.Neos:Backend`
with `Typo3BackendProvider` in the config above.

# 3. Usage

## CLI Commands
### Creating users
The package exposes a command to create users. You can run

`./flow sandstormuser:create test@example.com password --additionalAttributes="firstName:Max;lastName:Mustermann"`

to create a user. This will create a Neos user if you're using the package in Neos. You can assign
roles to the new user in the Neos backend afterwards.

### Confirming user registration
It is possible to confirm a registrationflow and trigger user creation by running

`./flow sandstormuser:activateregistration test@example.com`

### Resetting passwords
Since 1.1.2, it is possible to reset passwords for users created with this package.

`./flow sandstormuser:setpassword test@example.com password`

If the package detects that the NeosUserCreationService is used, it forwards the command to the
Neos `UserCommandController->setPasswordCommand()`. Otherwise, our oackage's own logic is used.

The Authentication Provider can be passed in as an optional argument to reset passwords for users created
 with a different provider that the default UserManagement one (`Sandstorm.UserManagement:Login`):

`./flow sandstormuser:setpassword test@example.com password --authenticationProvider=Typo3BackendProvider`

## Redirect after login/logout
### Via configuration
To define where users should be redirected after they log in or out, you can set some config options:
```
Sandstorm:
  UserManagement:
    redirect:
    # To activate redirection, make these settings:
      afterLogin:
        action: 'action'
        controller: 'Controller'
        package: 'Your.Package'
      afterLogout:
        action: 'action'
        controller: 'Controller'
        package: 'Your.Package'
```

### Via node properties
When using the package within Neos, you have another possibility: you can set properties on the LoginForm node type.
The pages you link here will be shown after users log in or out. Please note that when a login/logout form is displayed
on a restricted page: in that case you MUST set a redirect target, otherwise you will receive an error message on logout.
If the redirection is configured via Settings.yaml, they will take precedence over the configuration at the node.
You can, of course, set these properties from TypoScript also if you have a login/logout form directly in you template:
```
loginform = Sandstorm.UserManagement:LoginForm {
  // This should be set, or there will be problems when you have multiple plugins on a page
  argumentNamespace = 'login'
  // Redirect to the parent page automatically after logout
  redirectAfterLogout = ${q(documentNode).parent().get(0)}
}
```

### Via custom RedirectTargetService
If redirecting to a specific controller method is still not enough for you, you can simply roll your own implementation of the
`RedirectTargetServiceInterface`. Just add the implementation within your own package and add the following lines to your `Objects.yaml`.
Mind the package loading order, you package should require sandstorm/usermanagement in its composer.json.
```
Sandstorm\UserManagement\Domain\Service\RedirectTargetServiceInterface:
  className: 'Your\Package\Domain\Service\YourCustomRedirectTargetService'
```

## Checking for a logged-in user in your templates
There is a ViewHelper available that allows you to check if somebody is logged into the frontend. Here's an example:

```
{namespace um=Sandstorm\UserManagement\ViewHelpers}

<um:ifAuthenticated>
  <f:then>
    You are currently logged in.
  </f:then>
  <f:else>
    You are not logged in!
  </f:else>
</um:ifAuthenticated>

```

If you have configured a different Authentication Provider than the default one, the viewhelper has an `authenticationProviderName`
argument to which you can pass the name of the Auth Provider you are using.

# Extending the package

## Changing / overriding templates
You can change any template via the default method using `Views.yaml`. Please see
http://flowframework.readthedocs.io/en/stable/TheDefinitiveGuide/PartIII/ModelViewController.html#configuring-views-through-views-yaml.
Here's an example how to plug your own login template:

```

-
  requestFilter: 'isPackage("Sandstorm.UserManagement") && isController("Login") && isAction("login")'
  options:
    templatePathAndFilename: 'resource://Your.Package/Private/Templates/Login/Login.html'
    partialRootPaths: ['resource://Your.Package/Private/Partials']
    layoutRootPaths: ['resource://Your.Package/Private/Layouts']
```

## Overriding e-mail templates
As documented in the configuration options above, overriding e-mail templates is easy:
* Copy the `EmailTemplates` folder from the UserManagement's `Resources/Private` folder into your
  own package and modify the templates to your heart's desire.
* Add your own package to the templatePackages config, as described in [Sandstorm/TemplateMailer Documentation](https://github.com/sandstorm/TemplateMailer).

## Changing the User model
You might want to add additional information to the user model. This can be done by extending
the User model delivered with this package and adding properties as you like. You will then
need to switch out the implementation of `UserCreationServiceInterface` to get control over
the creation process. This can be done via `Objects.yaml`:
```
Sandstorm\UserManagement\Domain\Service\UserCreationServiceInterface:
  className: 'Your\Package\Domain\Service\YourCustomUserCreationService'
```

## Changing the Registration Flow and validation logic
The `RegistrationFlow` class is the representation of a user signing up for your application.
It has a few default properties and can be extended with arbitrary additional data via its `attributes` property.

### Adding custom fields to the Registration Flow
Exchange the registration template as described above and add a field:
```
<f:form.checkbox id="terms" property="attributes.terms" value=""/>
```
This will add the field, but of course you might also want to validate it.

### Extending the Registration Flow validation logic
The UserManagement package has a hook for you to implement your custom registration flow validation logic. It is
called directly from the domain model validator of the package. All you need to to is create an implementation of
`Sandstorm\UserManagement\Domain\Service\RegistrationFlowValidationServiceInterface` in your own package. It could
look like this:
```
class RegistrationFlowValidationService implements RegistrationFlowValidationServiceInterface {
    /**
     * @param RegistrationFlow $registrationFlow
     * @param RegistrationFlowValidator $validator
     * @return void
     */
    public function validateRegistrationFlow(RegistrationFlow $registrationFlow, RegistrationFlowValidator $validator) {
        // This is an example of your own custom validation logic.
        if ($registrationFlow->getAttributes()['agb'] !== '1') {
            $validator->getResult()->forProperty('attributes.terms')->addError(new \Neos\Flow\Validation\Error('You need to accept the terms and conditions.'));
        }
    }
}
```

# 4. Known issues

Feel free to submit issues/PRs :)

# 5. TODOs

* An important missing feature: configuring password restrictions (8 chars min, a smiley and a celtic rune, ...)
* I18N for Templates.
* Tests.

# 6. FAQ

* *What happens if the user did not receive the registration email?*
  Just tell the user to register again. In this case, previous unfinished registrations are discarded.

# 7. License
MIT.
https://opensource.org/licenses/MIT
