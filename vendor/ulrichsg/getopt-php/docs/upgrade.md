---
layout: default
title: Upgrading GetOpt.PHP
permalink: /upgrade.html
---
# {{ page.title }}

GetOpt.PHP version 3 introduced several backwards-incompatible changes that
that will require a few minor code adjustments after upgrading the library.

This page describes these breaking changes, and how to fix the code.

## Namespace And Main Class Name

The **namespace** changed from `Ulrichsg\Getopt` to just `GetOpt`, and
the **main class name** changed from `Getopt` to `GetOpt` (with a capital `O`).

This will require changing the namespace imports, and rename the class throughout the code.
If it is used often, it may be easier to alias the class name in the use statement
as shown below.

```php?start_inline=true
// Legacy code:
use Ulrichsg\Getopt\Getopt;
use Ulrichsg\Getopt\Option;
$opt = new Getopt();

// New GetOpt 3.x code:
use GetOpt\GetOpt;
use GetOpt\Option;
$opt = new GetOpt();

// New, with aliasing:
use GetOpt\GetOpt as Getopt;
use GetOpt\Option;
$opt = new Getopt();
```

## Constructor's Signature

While the first parameter still has the same meaning and is compatible with version 2,
the **second parameter is now an array of settings**.
To set the default option mode, you have to change it as follows:

```php?start_inline=true
// Legacy code:
$getOpt = new Getopt([], Getopt::OPTIONAL_ARGUMENT);

// New GetOpt 3.x code:
$getOpt = new GetOpt([], [
    GetOpt::SETTING_DEFAULT_MODE => GetOpt::OPTIONAL_ARGUMENT
]);
```

## SetBanner And Padding Parameter Removed

The `Getopt::setBanner()` method got removed completely.

To customize the usage message, please refer to the [Help Text]({{ site.baseurl }}/help.html) section.

## Help Text Generation

The _padding_ parameter for `GetOpt::getHelpText()` method was removed.

The generated help message is now automatically wrapped based on the console's width.
Long option decriptions break on space at the end of the line, and
remaining text is moved to the next line and indented as appropriate.

You can therefore remove any manually added line breaks and padding from
option description texts.

Consider the following output:
```console
$ php example.php --help
Usage: example.php [options] [operands]

Options:
  --help     Show this help text.
  -o <arg>   This is a very long description text that wraps at column 80
             because the shell in which this command is executed has only
             80 columns.
```

Legacy code:
```php?start_inline=true
$opt = new Getopt([
    Option::create(null, 'help', GetOpt::NO_ARGUMENT)
        ->setDescription('Show this help text'),
    Option::create('o', null, GetOpt::REQUIRED_ARGUMENT )
        ->setDescription('This is a very long description text that wraps at column 80
            because the shell in which this command is executed has only
            80 columns.'),
]);
$opt->parse(); // To populate the script's name
echo $opt->getHelpText(11);
```

New GetOpt 3.x code:
```php?start_inline=true
$opt = new GetOpt([
    Option::create(null, 'help', GetOpt::NO_ARGUMENT)
        ->setDescription('Show this help text'),
    Option::create('o', null, GetOpt::REQUIRED_ARGUMENT )
        // Remove wrapping and padding
        ->setDescription('This is a very long description text that wraps at column 80 because the shell in which this command is executed has only 80 columns.'),
]);
// NOTE: calling parse() is not required in this example, as in v3
// the script's name is populated in the Constructor
echo $opt->getHelpText(); // Removed padding parameter
```
