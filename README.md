# PHP extensions finder

This library helps to find PHP extension required by your code, it can be used in CI tools.

## Usage
`php-extensions-finder check [--composer COMPOSER] [--] <dirs>...`

For more information, run `php-extensions-finder check --help`

The result looks like:

```
Please, add these lines to your composer.json:
==============================================

{
    "require": {
        "ext-json": "*"
    }
}
```

Return code is count of missing extensions, so you can use it in CI tools.

## Installation
PHP extension finder requires PHP 7.1.0 or newer. You can install it via Composer. This project is not meant to be run as a dependency, so install it as standalone:

`composer global require efabrica/php-extensions-finder`
