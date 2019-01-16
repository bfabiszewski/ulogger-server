---
layout: default
title: Installation
permalink: /installation.html
---
# {{ page.title }}

The recommended way of installing GetOpt.php is to use Composer.

## Use composer

```console
$ /path/to/composer require ulrichsg/getopt-php:"^3.0"
```

Replace `^3.0` by the release number you want to use (a list of releases is available on
[Packagist](https://packagist.org/packages/ulrichsg/getopt-php)).

### Manually edit composer.json

Add it to your composer.json file like this:

```json
{
    "require": {
        "ulrichsg/getopt-php": "^3.0"
    }
}
```

Afterwards update the package or all packages:

```console
# update only getopt-php
$ /path/to/composer update ulrichsg/getopt-php

# update all packages
$ /path/to/composer update
```

## Download and install manually

If not using Composer, you can download the desired release from GitHub and integrate it into your application
manually. GetOpt.php does not have any external dependencies.

In this case you have to configure an autoload mechanism or load all files manually too.
