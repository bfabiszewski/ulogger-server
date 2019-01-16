---
layout: default
title: Operands
permalink: /operands.html
---
# {{ page.title }}

Since version 3 it is possible to specify operands. Other than options operands have to be defined and provided in the
correct order. This limitation is because they don't have names. In other libraries they are often called positional
arguments.

## Specifying Operands

Operands can be added by `GetOpt::addOperand()` and `GetOpt::addOperands()`. These methods allow only `Operand` and
`Operand[]` respectively. There is no helper to define operands by string or array.

The constructor of `Operand` requires only a name. Optionally you can define the mode for the operand:

| Mode                | Int | Description                                                   |
|---------------------|-----|---------------------------------------------------------------|
| `OPTIONAL`          | 0   | Operand that may or may not appear                            |
| `REQUIRED`          | 1   | Operand that has to appear                                    |
| `MULTIPLE`          | 2   | Operand that can appear multiple times                        |
| `MULTIPLE+REQUIRED` | 3   | Operand that has to appear once but can appear multiple times |

By logic there are some restrictions because of the strict order:

  * a required operand can not follow after optional operands
  * no operand can follow after a multiple operand
  
When you add a required operand after optional operands all previous operands will become required. But when you try
to add an operand after a multiple operand it will throw an `InvalidArgumentException`.

```php
<?php
$getopt = new \GetOpt\GetOpt();
$getopt->addOperand(new \GetOpt\Operand('file', \GetOpt\Operand::REQUIRED));
$getopt->addOperands([
    new \GetOpt\Operand('destination', \GetOpt\Operand::OPTIONAL),
    new \GetOpt\Operand('names', \GetOpt\Operand::MULTIPLE),
]);
```

### Fluent Interface

For convenience there exists a public static method create. So you don't have to wrap your instantiation before you
use other setters. 

### Set up a default value

The default value can be defined the same way as for options. A default value will appear in `GetOpt::getOperands()` as
well as in `GetOpt::getOperand()` and the following example might give an unexpected result for you:

```php
<?php
$getopt = new \GetOpt\GetOpt();
$getopt->addOperands([
    \GetOpt\Operand::create('operand1'),
    \GetOpt\Operand::create('operand2')->setDefaultValue(42),
]);
var_dump($getopt->getOperands()); // [ 42 ]
```

This can lead to a misinterpretation that operand1 is 42 and operand2 is not given. Anyway it is a correct result. If
you are planning such things you should consider using `->getOperand('operand1')` which will return `null`. 

### Validation

You can validate the argument of an operand using the `->setValidation($callable)`. To learn more about validation
please refer to the section [Validation](validation.md) of this handbook.

### Description

Since version 3.2 you can also set the description of operands with `->setDescription($description)`. When one of the 
operands has a description the table of operands will be shown in the help.

> **Note:** all operands will be shown even if they don't have a description to show the order of operands.

## Working With Operands

After processing the arguments you can retrieve all operands with `GetOpt::getOperands()` or a specific operand by it's
position (**starting with 0**) with `GetOpt::getOperand(int)` (exactly the same behaviour as in version 2). Since
operands can have names you can also retrieve the value of an operand by calling `GetOpt::getOperand(string)`.

```php
<?php
$getopt = new \GetOpt\GetOpt();
$getopt->addOperand(\GetOpt\Operand::create('alpha', \GetOpt\Operand::MULTIPLE+\GetOpt\Operand::REQUIRED));
$getopt->process('a b c');
var_dump($getopt->getOperands()); // ['a', 'b', 'c']
var_dump($getopt->getOperand(0)); // ['a', 'b', 'c']
var_dump($getopt->getOperand('alpha')); // ['a', 'b', 'c']
var_dump($getopt->getOperand(1)); // null because operand 0 is multiple
```

## Limit Operands

By default a user is allowed to enter any operands. You may want `STRICT_OPERANDS`. This is working the same way as
`STRICT_OPTIONS` for options. When you set `GetOpt::SETTING_STRICT_OPERANDS = true` `GetOpt` will throw an exception
when the user provides an extra operand.

```php
<?php
$getopt = new \GetOpt\GetOpt(null, [\GetOpt\GetOpt::SETTING_STRICT_OPERANDS => true]);
$getopt->addOperand(\GetOpt\Operand::create('file', \GetOpt\Operand::OPTIONAL));

$getopt->process('/path/to/file "any other operand"'); // throws GetOpt\ArgumentException\Unexpected
``` 
