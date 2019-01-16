---
layout: default
title: Commands
permalink: /commands.html
---
# {{ page.title }}

The concept behind commands is a single program offering different, related tasks, for example an administration  
backend with the option to create, read, update and delete users. 

Instead of defining a `GetOpt` with many non-mandatory options that, depending on context, may later become required  
or need a different validation (username has to exist for update and delete, but must be unique for create), 
we create commands and define specific options and arguments for each of them.

## Defining Commands

A command must have at least a _name_ and a _handler_. The _handler_ parameter does not necessarily have to be a
[callable](http://php.net/manual/en/language.types.callable.php), it could also be a controller class name or any other
kind of data you wish to associate with the command.

```php
<?php
$getopt = new \GetOpt\GetOpt();
$getopt->addCommand(new \GetOpt\Command('create', 'User::create'));
```

### Setup the command's Description

There are two descriptions:

- the *short version* is shown in the list of commands, on the program's main help page
    ```console
    $ php program.php --help
    Usage: program.php <command> [options] [operands]
    
    Options:
      -h --help  Shows this help
      
    Commands:
      setup  Short description of setup
    ```

- the *full description* is shown when displaying a specific command's help page 
    ```console
    $ php program.php setup --help
    Usage: program.php setup [options] [operands]
    
    This is a longer description of the command.
    
    It may describe in more details what happens when you call it.
    
    Options:
      -h --help    Shows this help
      -o --option  An option from the setup command
    ```

You can either define a single help text which will be used for both the short and full description, 
or provide both descriptions, as shown in the example below:

```php
<?php
$getopt = new \GetOpt\GetOpt();
$getopt->addCommands([
    \GetOpt\Command::create('setup', 'Setup::setup')
        ->setDescription('Setup the application'),
        
    \GetOpt\Command::create('user:create', 'User::create')
        ->setDescription(
            'Creates a new user with the given data.' . PHP_EOL .
            PHP_EOL .
            'You can omit username and password when you use interactive mode.'
        )->setShortDescription('Create a new user'),
]);
```

### Command-Specific Options

A command can have specific options. Like for `GetOpt` you can pass the options through the Constructor, 
or using the `addOption(Option)` and `addOptions(Option[])` methods.

```php
<?php
$getopt = new \GetOpt\GetOpt();
$getopt->addCommands([
    \GetOpt\Command::create('user:delete', 'User::delete', [
        \GetOpt\Option::create('u', 'userId', \GetOpt\GetOpt::REQUIRED_ARGUMENT),
    ]),
    
    \GetOpt\Command::create('user:create', 'User::create')
        ->addOptions([
            \GetOpt\Option::create('u', 'username', \GetOpt\GetOpt::REQUIRED_ARGUMENT),
            \GetOpt\Option::create('p', 'password', \GetOpt\GetOpt::REQUIRED_ARGUMENT),
            \GetOpt\Option::create('i', 'interactive'),
        ]),
]);
```

You can also reuse the options and share options for different commands:

```php
<?php
/** @var \GetOpt\Option[] $options */
$options = [];
$options['userId'] = \GetOpt\Option::create('u', 'userId', \GetOpt\GetOpt::REQUIRED_ARGUMENT);
$options['interactive'] = \GetOpt\Option::create('i', 'interactive');

$getopt = new \GetOpt\GetOpt();
$getopt->addCommands([
    \GetOpt\Command::create('user:delete', 'User::delete')
        ->addOption($options['userId']),
        
    \GetOpt\Command::create('user:edit', 'User::edit')
        ->addOptions([
            $options['interactive'],
            $options['userId']
        ]),
]);
```

### Command-Specific Operands

You can specify operands that are only valid for a specific command, the same way as for `GetOpt`. You can also reuse
these Operands for different commands.

```php
<?php
$operandUserId = \GetOpt\Operand::create('userId', \GetOpt\Operand::MULTIPLE);

$getopt = new \GetOpt\GetOpt();
$getopt->addCommands([
    \GetOpt\Command::create('user:delete', 'User::delete')->addOperand($operandUserId),
    
    \GetOpt\Command::create('user:export', 'User::export')->addOperand($operandUserId),
]);
```

### Limitations

#### A command cannot specify an option that is already defined "globally"
 
`GetOpt` will throw an exception if you try to add a command with an option that conflicts with another option. 
If the command is added first and the option after, the exception will be thrown when the command is executed. 

We recommend to add common, global options first, and commands later.

#### Commands must be set before operands

This is an artificial limitation. The command has to be the first operand. When you add common operands these will be
the first operands after the command, followed by the command-specific operands. 

We suggest not to add common operands.

## Working With Commands

After processing the command-line arguments, we can retrieve the current command by calling `GetOpt::getCommand()` 
without a parameter. It returns the Command object and we can use the `Command::getName()`, `Command::getHandler()`,
`Command::getDescription()` and `Command:getShortDescription()` getters to identify the command.  
If no command is specified it will return `null`.

```php
<?php
$getopt = new \GetOpt\GetOpt();
// define options and commands

try {
    $getopt->process();
} catch (\GetOpt\ArgumentException $exception) {
    // do something with this exception
}

$command = $getopt->getCommand();
if (!$command) {
    // no command given - show help?
} else {
    // do something with the command - example:
    list ($class, $method) = explode('::', $command->getHandler());
    $controller = controllerFactory($class);
    call_user_func([$controller, $method], $getopt->getOptions(), $getopt->getOperands());
}
```
