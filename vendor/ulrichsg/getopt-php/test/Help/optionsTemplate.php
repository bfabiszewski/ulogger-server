<?php

// An example of a options template

use GetOpt\GetOpt;

echo 'Available options:' . PHP_EOL;

/** @var \GetOpt\Option[] $options */

$data            = [];
$definitionWidth = 0;
foreach ($options as $option) {
    $definition = implode(', ', array_filter([
        $option->getShort() ? '-' . $option->getShort() : null,
        $option->getLong() ? '--' . $option->getLong() : null,
    ]));

    if ($option->getMode() !== GetOpt::NO_ARGUMENT) {
        $name = $option->getArgument()->getName();
        $argument = '<' . $name . '>';
        if ($option->getMode() === GetOpt::OPTIONAL_ARGUMENT) {
            $argument = '[' . $argument . ']';
        }

        $definition .= ' ' . $argument;
    }

    if (strlen($definition) > $definitionWidth) {
        $definitionWidth = strlen($definition);
    }

    $data[] = [
        $definition,
        $option->getDescription()
    ];
}

echo $this->renderColumns($definitionWidth, $data);
