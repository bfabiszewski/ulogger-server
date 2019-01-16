<?php

// An example of a commands template

echo 'Available commands:' . PHP_EOL;

/** @var \GetOpt\Command[] $commands */

$data      = [];
$nameWidth = 0;
foreach ($commands as $command) {
    if (strlen($command->getName()) > $nameWidth) {
        $nameWidth = strlen($command->getName());
    }

    $data[] = [
        $command->getName(),
        $command->getShortDescription()
    ];
}

echo $this->renderColumns($nameWidth, $data);
