---
layout: default
title: Help Text
permalink: /help.html
---
# {{ page.title }}

This library can generate console output that helps your users understand how they can use your application.
The output varies depending on what options, operands and commands you provide, if additional operands and
custom options are allowed, and so on.

## Customizing Help

By default, `GetOpt::getHelpText()` uses the `GetOpt\Help` class that implements `GetOpt\HelpInterface`.

You can provide your own, custom help text generator with `GetOpt::setHelp(HelpInterface)`.
The method `HelpInterface::render(GetOpt, array)` receives the `GetOpt` object from which
`getHelpText()` was called, with additional custom data in the second parameter.

### Localization

By default, `GetOpt` displays standard help text in English. Have a look at [Localization](localization.md) for more
information about localization.

#### Override the localization

> **Deprecated** note that this method is deprecated for messages. Please use localization files instead.

The `GetOpt\Help` class can be used to define the standard help text, with the `setTexts(array $texts)` method. 
The provided array overwrites the existing localization:

```php
<?php
$getopt = new \GetOpt\GetOpt();
$getopt->getHelp()->setTexts([
    'placeholder' => '<>',
    'optional' => '[]',
    'multiple' => '...',
    'usage-title' => 'Usage: ',
    'usage-command' => 'command',
    'usage-options' => 'options',
    'usage-operands' => 'operands',
    'options-title' => "Options:\n",
    'options-listing' => ', ',
    'commands-title' => "Commands:\n"
]);
```

### Extending The Help Class

You can also extend and reuse the methods in `GetOpt\Help`. The possibilities are endless... here is a small example:

```php
<?php
class MyHelp extends \GetOpt\Help {
    protected function renderColumns($columnWidth, $data)
    {
        return implode("\n--------------\n", array_map(function ($row) {
            return $row[0] . "\n    " . $row[1] . "\n";
        }, $data));
    }
}

$getopt = new \GetOpt\GetOpt();
$getopt->setHelp(new MyHelp());
```

### Custom Templates

> **The use of templates is deprecated**. 
> Please consider extending the help class and overwrite the `render*()` methods instead.

Instead of developing your own custom Help class, you may also create templates
([examples](https://github.com/getopt-php/getopt-php/tree/3.1.0-alpha.1/test/Help)). The output from these templates
is then used to generate the help text.

```php
<?php
$getopt = new \GetOpt\GetOpt();
$getopt->getHelp()
    ->setUsageTemplate('path/to/my/usageTemplate.php')
    ->setOptionsTemplate('path/to/my/optionsTemplate.php')
    ->setCommandsTemplate('path/to/my/commandsTemplate.php');
```

### Tables

Options, operands and commands are shown in a table with two columns. The columns are divided with two spaces and the
description (second column) will automatically break after the last space that fits into the terminal. The number of
columns that fit into the terminal is determined in the following sequence:

1. a constant `COLUMNS`,
2. an environment variable `COLUMNS`,
3. the result from `tput cols` command
4. value `90`

This is limited by the setting `GetOpt\Help::MAX_WIDTH` (default: `120`).

### The Parts of Help

In the following sections, you will find a complete description of the three parts the Help is split into, and what
they typically show.

#### Usage

The _usage_ briefly describes how to run your application (i.e. the command's syntax). It shows the script name, if a
command has to be given, where the options should be entered and the name and order of operands.

 - Default with commands and options defined:  
   `Usage: path/to/app <command> [options] [operands]`
 - Command `make:config` is given, options are defined, strict operands with operand `file` defined:  
   `Usage: path/to/app make:config [options] <file>`
 - No commands, options and operands defined and strict operands:  
   `Usage: path/to/app`
   
#### Operands

When one of the operands has a description the table of operands will be shown. The table can be suppressed by passing
the option `HIDE_OPERANDS` with a falsy value (e. g. `false`).

The table shows the operands name in the left column and the description on the right column what might look like this:

```
Operands:
  <source>    The source that should be copied.
  [<target>]  The target where source should be copied to. If target is ommitted the file 
              will be copied to the current working directory.
```

#### Options

Options are shown in a table with the options (including argument) in the left column, and
the description of each option in the right column.

In the end it might look something like this:

```
Options:
  -h --help           Shows this help
  -c --config <file>  Use this configuration file. By default the configuration from user
                      is used (e. g. $HOME/.myapp.php.inc)
  --version           Show version information and quit
```

#### Commands

Commands are shown in a table similar to options. Because they only have a name the list might look something
like this:

```
Commands:
  user:create    Create a new user
  user:delete    Delete an existing user
  user:edit      Edit the information of an user
  user:password  Set a new password for the user. Alternative you can also send a link to
                 change the password to his current eMail address.
```

The list of commands is only shown when at least one command is defined, and no command is set. When a command is set, the
options, operands and the long description from the command is shown:

```console
$ ./app user:create --help
Usage: ./app user:create [options] [<username>]

Create a new user.

When the username is omitted you will be prompted for a username. The same is for password and email options.

Options:
  -h --help           Shows this help
  -c --config <file>  Use this configuration file. By default the configuration from user
                      is used (e. g. $HOME/.myapp.php.inc)
  --version           Show version information and quit
  --password <arg>    The password for the user
  --email <arg>       The email address for the user
  --no-interaction    Throw an error when data is missing
```

> **NOTE:** the long description of a command does not automatically wrap at the column width of your console. 
