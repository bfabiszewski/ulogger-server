---
layout: default
title: Validation
permalink: /validation.html
---
# {{ page.title }}

This library does not come with a bunch of validators that you can use and extend. Instead you provide a callable or
closure that has to return a truthy value if the value is valid (further called the validator).

The validator gets the value as first and only parameter. For a lot of php standard functions this is enough (eg. 
`is_numeric`). The value will always be a string or null. Here comes an example that shows how to check that it has
a valid json value:

```php
<?php
$getopt = new \GetOpt\GetOpt([
    \GetOpt\Option::create(null, 'data', \GetOpt\GetOpt::REQUIRED_ARGUMENT)
        ->setValidation(function ($value) {
            return $value === 'null' || json_decode($value) !== null;
        })
]);
```

```console
$ php program.php --data null
$ php program.php --data []
$ php program.php --data '{"a":"alpha"}'
$ php program.php --data invalid
```

## Validation Message

Since version 3.2 GetOpt supports custom validation messages. `Option::setValidation()` and `Operand::setValidation` now
allow to pass a second parameter the validation message. This message gets passed to `sprintf($message, $desc, $value)`
where `$desc` is the description of the validated object (e. g. `Option 'data'`) and `$value` the original value that
was not valid.

The validation message can also be a callable that is then called with `$object` and `$value`. To get the description
you can then use `$object->describe()`.

```php
<?php
\GetOpt\Option::create('n', 'count', \GetOpt\GetOpt::REQUIRED_ARGUMENT)
    ->setValidation(function ($value) {
        return is_numeric($value) && (int)$value == round((double)$value);
    }, 'Count has to be an integer');
\GetOpt\Operand::create('pretend')
    ->setValidation('is_resource', function(\GetOpt\Operand $operand, $value) {
        return $value . ' is invalid for ' . $operand->getName();
    });
```

## Reusable Validator

You can also create functions or classes that can be used as validator and reuse them.

```php
<?php
class InArrayValidator {
    protected $array;
    public function __construct(array $array) {
        $this->array = $array;
    }
    public function __invoke($value) {
        return in_array($value, $this->array);
    }
}

\GetOpt\Operand::create('type')->setValidation(
    new InArrayValidator(['file', 'f', 'directory', 'd', 'link', 'l'])
);
```

## Advanced Validation

The validator is also executed if the option mode is `NO_ARGUMENT`. This way we can also check other circumstances
inside our application as well as the current status of options.

A use case for this could be to define exclusive options (which is also the reason because it was asked in a feature
request). Let's say our program has the options `alpha` and `omega` but when you define `alpha` the `omega` option is
forbidden and vise versa:

```php
<?php
$getopt = new \GetOpt\GetOpt();

$getopt->addOptions([
    \GetOpt\Option::create(null, 'alpha', \GetOpt\GetOpt::REQUIRED_ARGUMENT)
        ->setValidation(function () use ($getopt) {
            return !$getopt->getOption('omega');
        }),
    \GetOpt\Option::create(null, 'omega', \GetOpt\GetOpt::REQUIRED_ARGUMENT)
        ->setValidation(function () use ($getopt) {
            return !$getopt->getOption('alpha');
        }),
]);
```
