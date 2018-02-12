# Batched push provider for laravel

[![Build Status](https://travis-ci.org/morloderex/batchedPusherChannels.svg?branch=master)](https://travis-ci.org/morloderex/batchedPusherChannels)

* [Installation](#installation)

This package allows batch the authentication requests from pusher in one request using [Dirk Bonhomme](https://github.com/dirkbonhomme/pusher-js-auth) authorizor plugin.

Once installed you will see authorization requests towards your channels in batches.

## Installation

This package can be used in Laravel 5.5 or higher.

You can install the package via composer:

``` bash
composer require morloderex/batched-pusher-channels
```

In Laravel 5.5 the service provider will automatically get registered. In older versions of the framework just add the service provider in `config/app.php` file:

```php
'providers' => [
    // ...
    Morloderex\PusherBatch\Providers\BatchedPusherProvider::class,
];
```

## Change your broadcast provider

Once installed through composer by the require command. You then change the BROADCAST_PROVIDER environment variable to be ```BatchedPusher```

And you should be all set.

### Testing

``` bash
phpunit
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security-related issues, please email [michael.lundboel@gmail.com](michael.lundboel@gmail.com) instead of using the issue tracker.

## Credits

- Michael Lundbøl (website is coming)
- [All Contributors](../../contributors)

This package is heavily based on [Dirk Bonhomme](https://github.com/dirkbonhomme/pusher-js-auth)'s awesome [Package](https://github.com/dirkbonhomme/pusher-js-auth)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
