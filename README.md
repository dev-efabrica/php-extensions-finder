# PHP extensions finder

This library helps to find PHP extension required by your code, it can be used in CI tools.

## Installation
PHP extension finder requires PHP 7.1.0 or newer. You can install it via Composer. This project is not meant to be run as a dependency, so install it as project:

```shell script
mkdir -p ~/tests/php-extensions-finder
composer require efabrica/php-extensions-finder
```

or globally:
```shell script
composer global require efabrica/php-extensions-finder
```

## Usage
```shell script
~/tests/php-extensions-finder/vendor/bin/php-extensions-finder check [--composer COMPOSER] [--] <dirs>...
```
or
```shell script
php-extensions-finder check [--composer COMPOSER] [--] <dirs>...
```
if it is installed globally

For more information, run with option `--help`

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

With option `-vvv` output will be extended with missing extensions usage:

```
Missing extensions usage:
=========================

ext-json
--------
src/Command/MyCommand.php:35 json_decode
src/Command/MyCommand.php:70 json_encode
src/Command/MyCommand.php:70 JSON_PRETTY_PRINT

Please, add these lines to your composer.json:
==============================================

{
    "require": {
        "ext-json": "*"
    }
}
```

Return code is count of missing extensions, so you can use it in CI tools.
