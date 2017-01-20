# unit6/passport

A client library for the [@Eurolink](https://github.com/eurolink) single sign-on (SSO) service, Passport.

This library is restricted for usage by **approved** applications which need access to the secure Passport identity network.

## Requirements

 - Passport API Keys
 - PHP 5.6
 - cURL 7.43
 - JSON

## Installation

Install dependencies:

    composer install

Run unit tests:

    ./vendor/bin/phpunit --verbose

## Usage

Create a client application using credentials:

```php
use Unit6\Passport;

$client = new Passport\Client([
	'id' => '1',
	'key' => 'secret',
	'algorithm' => 'sha256',
	'user' => 'example',
	'host' => 'api.example.org'
]);

[...]
```

### Register a user:

A user can be registered by approved applications with a `Pending` state. This is helpful if you intend to verify their account beforehand:

```php
[...]

$user = (new Passport\User())
        ->withName('John Smith')
        ->withEmail('j.smith@example.org')
        ->withPassword('secret')
        ->withStatus('Pending');

$registered = $client->registerUser($user);

# ID: $user->getId()
# Message: $client->getMessage()

[...]
```

### Update user status:

Once the user has clicked an 'activation' link sent by email, their account status can be changed to `Active`. You can then allow them to login.

```php
[...]

$update = $user->updateStatus(Passport\User::STATUS_ACTIVE);

[...]
```

### Login a user:

Use the credentials to authenticate a user during a login process:

```php
[...]

$user = (new Passport\User())
        ->withClient($client)
        ->withEmail('j.smith@example.org')
        ->withPassword('secret');

$user->authenticate();

[...]
```

### Get user information:

You can obtain user information specific to your application by requesting their `Persona` by passing your `$applicationId`:

```php
[...]

$persona = $user->getPersona($applicationId);

# Save user persona to local database.
# $persona->getId();
```

## License

MIT, see LICENSE.