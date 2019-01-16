---
layout: default
title: Syntax
permalink: /syntax.html
---
# {{ page.title }}

## Short Options

A short option is a letter (A-Z only, no umlauts etc.) or digit prefixed with a single hyphen.
Letters are case sensitive, i.e., `-v` and `-V` are different options.
If the option has an argument, it must be separated from the option by a space. The following
command invokes `program.php` with two short options `a` and `1`, where
`a` has the argument `foo` and `1` has no argument:

```console
$ php program.php -a foo -1
```

Multiple short options can be clustered together if at most one of them has an argument.
In this case that option has to come last; the argument is added as usual. The following command
is equivalent to the one above:

```console
$ php program.php -1a foo
```

A short option with no argument may occur repeatedly. The number of repetitions becomes the
option's value. The following two commands are thus equivalent except for the
[option modes]({{ site.baseurl }}/options.html) they are valid in:

```console
$ php program.php -vvv  # v must have an optional or no argument
$ php program.php -v 3  # v must have a mandatory or optional argument
```

## Long Options

A long option is a sequence of two or more characters (letters, digits, underscore, hyphen)
prefixed with two hyphens. The first character must be a letter or digit. Arguments can be
separated from the option by either a space or an equals sign:

```console
$ php program.php --foo --bar baz --quux=1337
```

## Operands

Anything that is not an option or option's argument is called an operand. The following rules
determine what is parsed as an operand:

 - As soon as a command line argument is encountered that cannot be parsed as an option or
    as an option's argument, it is parsed as an operand.
 - If the command line contains an argument `--`, all arguments after it are parsed as operands.

In the following commands, `-a` is parsed as an operand even though it looks like an option.

```console
$ php program.php -- -a
```

Depending on the definition of `-f` the following `value` can either be an operand or the argument of `-f`:

```console
$ php program.php -f value -o --option
```

## Commands

When using commands for routing the command has to be provided as first operand. Also no command specific option can
be provided in front of the command. The following examples shows the basic syntax and a concrete usage:

```console
$ php program.php [common options] <command> [common and specific options] [operands]
$ php program.php -c /path/to/config.inc drop-table --cascade users
```

## Quoting

Parameters, arguments and operands are separated by spaces. You can have spaces in operands and arguments
by quoting them in single or double quotes. A double quoted argument can also contain single quotes and
vise versa. They can be concatenated like usually in bash scripts.

```console
$ php program.php "this is one operand"
['this is one operand']

$ php program.php "you can concatenate"' by connecting them'
['you can concatenate by connecting them']

$ php program.php 'this can contain "'
['this can contain "']

$ php program.php "this can contain '"
['this can contain \'']

$ php program.php "you may want both '"'"'
['you man want both \'"']
```
