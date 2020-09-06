# Installation

This package is available on [Packagist] and you can install it using [Composer].

By running the following command you'll add `lcobucci/di-builder` as a dependency to your project:

```sh
composer require lcobucci/di-builder
```

## Autoloading

!!! Note
    We'll be omitting the autoloader from the code samples to simplify the documentation.

In order to be able to use the classes provided by this library you're also required to include [Composer]'s autoloader in your application:

```php
require 'vendor/bin/autoload.php';
```

!!! Tip
    If you're not familiar with how [composer] works, we highly recommend you to take some time to read it's documentation - especially the [autoloading section].

## PHP configuration

In order to make sure that we're dealing with the correct data, we're using the function `assert()`.

The nice thing about `assert()` is that we can (and should) disable it on production.
That would avoid creating and executing _opcodes_ which are relevant only for development.

Check the documentation for more information: <https://secure.php.net/manual/en/function.assert.php>

### Production mode

We recommend you to set `zend.assertions` to `-1` in your `php.ini`.

### Development

You should leave `zend.assertions` as `1` and set `assert.exception` to `1`, which will make PHP throw an `AssertionError` when things go wrong.

[Packagist]: https://packagist.org/packages/lcobucci/di-builder
[Composer]: https://getcomposer.org
[autoloading section]: https://getcomposer.org/doc/01-basic-usage.md#autoloading
