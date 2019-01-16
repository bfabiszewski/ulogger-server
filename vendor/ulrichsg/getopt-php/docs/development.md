---
layout: default
title: Development
permalink: /development.html
---
# {{ page.title }}

GetOpt has received contributions from [several people](https://github.com/getopt-php/getopt-php/graphs/contributors)
in the past, for which we are deeply thankful. If you would like to contribute, feel free to
[create an issue](https://github.com/getopt-php/getopt-php/issues/new) or clone the source and submit a pull
request. If you are planning to hack on GetOpt yourself, please read the rest of this page for some general
information.

## Guidelines

 - GetOpt supports PHP versions back up to 5.4, so unfortunately, all those shiny language features from newer
versions are off limits. Luckily, [Travis](https://travis-ci.org/getopt-php/getopt-php) ensures this
automatically and will notify you if your pull request does not respect it (or if it breaks anything else).
 - With v2.0 we have adopted [PSR-2](http://www.php-fig.org/psr/psr-2/) as coding conventions, so we would
ask all contributors to adhere to it. You probably don't use it in your personal work, but it's the closest thing
to a community consensus that the PHP world has, and thus we consider it appropriate for
an open source project to use.
 - Please supply unit tests and doc comments wherever necessary.

## Running the Tests

If PHPUnit is already installed, calling `phpunit` in the project directory will run the test suite.
However, if it is not, you can pull it in as a local dependency by running:

```console
$ make install-dependencies
```

This will download Composer and then run it to install GetOpt's development dependencies (i.e. PHPUnit). After that,
the test suite can be executed like this:

```console
$ make test
```
