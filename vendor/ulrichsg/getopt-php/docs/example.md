---
layout: default
title: Example
permalink: /example.html
---
# {{ page.title }}

Here we want to show how a complete setup may look like.

## Executable file:

```php
#!/usr/bin/env php
<?php

use GetOpt\GetOpt;
use GetOpt\Option;
use GetOpt\Command;
use GetOpt\ArgumentException;
use GetOpt\ArgumentException\Missing;

require_once __DIR__ . '/vendor/autoload.php';

define('NAME', 'AwesomeApp');
define('VERSION', '1.0-alpha');

$getOpt = new GetOpt();

// define common options
$getOpt->addOptions([
   
    Option::create(null, 'version', GetOpt::NO_ARGUMENT)
        ->setDescription('Show version information and quit'),
        
    Option::create('?', 'help', GetOpt::NO_ARGUMENT)
        ->setDescription('Show this help and quit'),
    
]);

// add simple commands
$getOpt->addCommand(Command::create('test-setup', function () { 
    echo 'When you see this message the setup works.' . PHP_EOL;
})->setDescription('Check if setup works'));

// add commands
$getOpt->addCommand(new CopyCommand());
$getOpt->addCommand(new MoveCommand());
$getOpt->addCommand(new DeleteCommand());


// process arguments and catch user errors
try {
    try {
        $getOpt->process();
    } catch (Missing $exception) {
        // catch missing exceptions if help is requested
        if (!$getOpt->getOption('help')) {
            throw $exception;
        }
    }
} catch (ArgumentException $exception) {
    file_put_contents('php://stderr', $exception->getMessage() . PHP_EOL);
    echo PHP_EOL . $getOpt->getHelpText();
    exit;
}

// show version and quit
if ($getOpt->getOption('version')) {
    echo sprintf('%s: %s' . PHP_EOL, NAME, VERSION);
    exit;
}

// show help and quit
$command = $getOpt->getCommand();
if (!$command || $getOpt->getOption('help')) {
    echo $getOpt->getHelpText();
    exit;
}

// call the requested command
call_user_func($command->getHandler(), $getOpt);
```

## Copy Command

```php
<?php

use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Operand;

class CopyCommand extends Command
{
    public function __construct()
    {
        parent::__construct('copy', [$this, 'handle']);
        
        $this->addOperands([
            Operand::create('file', Operand::REQUIRED)
                ->setValidation('is_readable'),
            Operand::create('destination', Operand::REQUIRED)
                ->setValidation('is_writable')
        ]);
        
    }
    
    public function handle(GetOpt $getOpt)
    {
        copy($getOpt->getOperand('file'), $getOpt->getOperand('destination'));
    } 
}
```
