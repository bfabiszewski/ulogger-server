---
layout: default
title: Options
permalink: /options.html
---
# {{ page.title }}

This page describes how to specify options and their arguments. It covers everything you need to know to make use of
options.

## Specifying Options

Options are defined by an object of the class `GetOpt\Option`. There are two helpers to create these options but we
recommend to use the usual way to create objects.

> We are using argument definition in these examples have a look at [specifying arguments](#arguments) to
> learn more about it.

### Creating Options

```php
<?php
$optionAlpha = new \GetOpt\Option('a', 'alpha', \GetOpt\GetOpt::REQUIRED_ARGUMENT);
$optionAlpha->setDescription(
    'This description could be very long ' .
    'and you may want to separate to multiple lines.'
);
$optionAlpha->setValidation('is_numeric');
```

And add them to the `GetOpt\GetOpt` object:

```php
<?php
// in constructor
$getopt = new GetOpt([$optionAlpha, $optionBeta]);

// via addOptions
$getopt = new GetOpt();
$getopt->addOptions([$optionAlpha, $optionBeta]);

// via addOption
$getopt = new GetOpt();
$getopt->addOption($optionAlpha)->addOption($optionBeta);
```

The setters can be chained and for convenience there is also a public static method create which allows to write the 
above command this way:

```php
<?php
$getopt = new \GetOpt\GetOpt([
    
    \GetOpt\Option::create('a', 'alpha', \GetOpt\GetOpt::REQUIRED_ARGUMENT)
        ->setDescription('This is the description for the alpha option')
        ->setArgument(new \GetOpt\Argument(null, 'is_numeric', 'alpha')),
    
    \GetOpt\Option::create('b', 'beta', \GetOpt\GetOpt::NO_ARGUMENT)
        ->setDescription('This is the description for the beta option'),
        
]);
```

> This looks very clean in my opinion

### Options From String (Short Options Only)

Options can be defined by a string with the exact same syntax as 
[PHP's `getopt()` function](http://php.net/manual/en/function.getopt.php) and the original GNU getopt. It is the
shortest way to set up GetOpt, but it does not support long options or any advanced features:

```php
<?php
$getopt = new GetOpt('ab:c::');
```

Each letter or digit in the string declares one option. Letters may be followed by either one or two colons to
determine if the option can or must have an argument:

 - No colon - no argument
 - One colon - argument required
 - Two colons - argument optional

### Options From Array

There is also a helper that creates an `GetOpt\Option` from array. These method allows the most important features and
can look very clean too:

```php
<?php
$getopt = new \GetOpt\GetOpt([
   
    // creates an option a without a long alias and with the default argument mode
    ['a'],
    
    // creates an option without a short alias and with the default argument mode
    ['beta'],
    
    // you can define the argument mode
    ['c', \GetOpt\GetOpt::REQUIRED_ARGUMENT],
    
    // you can define long, short, argument mode, description and default value
    ['d', 'delta', \GetOpt\GetOpt::MULTIPLE_ARGUMENT, 'Description for delta', 'default value'],
    
    // note that you have to provide null values if you want to add a desciprtion or default value
    ['e', null, \GetOpt\GetOpt::NO_ARGUMENT, 'Enable something'],
    
]);
```

This method does not allow to specify the validation or the argument name but you can get the option and define it
afterwards:

```php
<?php
$getopt->getOption('beta', true)
    ->setDescription('Provide a beta version')
    ->setMode(\GetOpt\GetOpt::OPTIONAL_ARGUMENT)
    ->setArgumentName('beta version');
```

The default mode is `NO_ARGUMENT` you can overwrite this with the setting `SETTING_DEFAULT_MODE` from GetOpt:

```php
<?php
$getopt = new \GetOpt\GetOpt([
    ['a']
], [
    \GetOpt\GetOpt::SETTING_DEFAULT_MODE => \GetOpt\GetOpt::OPTIONAL_ARGUMENT
]);
```

## Working With Options

After the options have been defined you can process the command line arguments. The method `GetOpt::process()` takes
an array or string function of the arguments that should be processed. If the parameter is omitted the method uses
`$_SERVER['argv']` for processing.

```php
<?php
$getopt = new \GetOpt\GetOpt();
// add your options

// process $_SERVER['argv']
$getopt->process();

// process an arguments string
$getopt->process('-b --beta -a"this is the value of a"');

// process an array
$getopt->process(['-b', '--beta', '-a', 'this is the value of a']);
```

After processing you can access the value of a specific option with `GetOpt::getOption(string)` or getting all values
with `GetOpt::getOptions()`.

```php
<?php
// access by long name (suggested)
$beta = $getopt->getOption('beta');

// access by short name
$beta = $getopt->getOption('b');

// access all values
$options = $getopt->getOptions();
var_dump($options);
// [
//   'b' => 2,
//   'beta' => 2,
//   'a' => 'this is the value of a',
//   'alpha' => 'this is the value of a'
// ]
```

### Accessing Options By ArrayAccess

You can also access options by array and therefore you can iterate over your `GetOpt` instance. *But keep in mind* that
you will **only** get the long name options if defined (**changed since version 3**). Take a look at this example:

```php
<?php
$getopt = new \GetOpt\GetOpt([
    \GetOpt\Option::create('a', null, \GetOpt\GetOpt::OPTIONAL_ARGUMENT),
    \GetOpt\Option::create('b', 'beta', \GetOpt\GetOpt::REQUIRED_ARGUMENT),
    \GetOpt\Option::create('v', 'verbose'),
]);
$getopt->process('-vvv -a "value of alpha" -b value');

foreach ($getopt as $key => $value) {
    echo sprintf('%s: %s', $key, $value) . PHP_EOL;
}
// a: value of alpha
// beta: value
// verbose: 3 
```

Even if foreach does not iterate over the key value pair `['b' => 'value']` you can access it directly:

```php
<?php
var_dump(array_key_exists('b', $getopt)); // true
var_dump($getopt['b']); // 'value'
```

## Arguments

The mode of an option specifies the existence of an argument. It can be one of the following constants:

```php
<?php
\GetOpt\GetOpt::NO_ARGUMENT;       // ':noArg'
\GetOpt\GetOpt::REQUIRED_ARGUMENT; // ':requiredArg'
\GetOpt\GetOpt::OPTIONAL_ARGUMENT; // ':optionalArg'
\GetOpt\GetOpt::MULTIPLE_ARGUMENT; // ':multipleArg'
```

> **Attention:** the type and value of these constants changed in version 3.

This mode is defined during construction of the option. The default value is `NO_ARGUMENT`. It changes the
visualization for the help text and if the mode is **not** `NO_ARGUMENT` then a following argument will be assigned as
the value for the option:

```console
$ php program.php -c "this is the value of option c"
```

`REQUIRED_ARGUMENT` and `MULTIPLE_ARGUMENT` will fail if no value is defined.

### Define the name

For better understanding you can define the name of the argument that gets shown in the help:

```php
<?php
$getopt = new \GetOpt\GetOpt([
    \GetOpt\Option::create('c', 'config', \GetOpt\GetOpt::REQUIRED_ARGUMENT)
        ->setArgumentName('ini-file')
]);
echo $getopt->getHelpText();
// Usage: program.php [options] [operands]
// Options:
//   -c --config <ini-file>
```

### Set up a default value

For options with arguments you might want to define a default value. An option that is not defined in the command line
returns the default value for `Option::getValue()` and `GetOpt::getOption()`:

```php
<?php
$getopt = new \GetOpt\GetOpt([
    \GetOpt\Option::create('c', 'config', \GetOpt\GetOpt::REQUIRED_ARGUMENT)
        ->setDefaultValue('/etc/program.ini')
]);

echo $getopt->getOption('config'); // /etc/program.ini
```

### Multiple Argument

An option with a multiple argument always returns an array. An empty array if the option is not set and no default is
given, an array with only the default value if not set and default value is given or an array of all values given.

```php
<?php
$getopt = new \GetOpt\GetOpt([
    \GetOpt\Option::create('d', 'domain', \GetOpt\GetOpt::MULTIPLE_ARGUMENT)
]);
$getopt->process('-d example.com --domain example.org');

var_dump($getopt->getOption('domain')); // ['example.com', 'example.org']
``` 

### Validation

You can validate the argument of an option using the `->setValidation($callable)`. To learn more about validation
please refer to the section [Validation](validation.md) of this handbook.

## Allow Custom Options

By default only options are allowed that are defined before you run `GetOpt::process()`. This we called
`STRICT_OPTIONS`. For a quick and dirty application you may want to allow everything. When you setup your `GetOpt` with
`GetOpt::SETTING_STRICT_OPTIONS = false` every option is allowed with an optional argument. 

```php
<?php
$getopt = new \GetOpt\GetOpt(null, [\GetOpt\GetOpt::SETTING_STRICT_OPTIONS => false]);

$getopt->process('-a "hello world" --alpha -vvv');
var_dump($getopt->getOptions());
// [
//     'a' => 'hello world',
//     'alpha' => 1,
//     'v' => 3
// ];
```
