<?php

namespace GetOpt\Test\Help;

use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Help;
use GetOpt\Option;
use PHPUnit\Framework\TestCase;

class TemplateTest extends TestCase
{
    /** @test */
    public function rendersUsageTemplate()
    {
        $getOpt = new GetOpt();
        $scriptName = $getOpt->get(GetOpt::SETTING_SCRIPT_NAME);

        $help = new Help([
            Help::TEMPLATE_USAGE => __DIR__ . '/usageTemplate.php'
        ]);
        $getOpt->setHelp($help);

        self::assertSame($scriptName . ' [operands]' . PHP_EOL, $getOpt->getHelpText());
    }

    /** @test */
    public function rendersOptionsTemplate()
    {
        $getOpt = new GetOpt([
            Option::create('a', 'alpha', GetOpt::OPTIONAL_ARGUMENT),
        ]);
        $scriptName = $getOpt->get(GetOpt::SETTING_SCRIPT_NAME);

        $help = new Help([
            Help::TEMPLATE_OPTIONS => __DIR__ . '/optionsTemplate.php'
        ]);
        $getOpt->setHelp($help);

        self::assertSame(
            'Usage: ' . $scriptName . ' [options] [operands]' . PHP_EOL .
            PHP_EOL .
            'Available options:' . PHP_EOL .
            '  -a, --alpha [<arg>]  ' . PHP_EOL,
            $getOpt->getHelpText()
        );
    }

    /** @test */
    public function rendersCommandsTemplate()
    {
        $getOpt = new GetOpt();
        $getOpt->addCommand(Command::create('test', 'var_dump')->setDescription('Run this tests'));
        $scriptName = $getOpt->get(GetOpt::SETTING_SCRIPT_NAME);

        $help = new Help([
            Help::TEMPLATE_COMMANDS => __DIR__ . '/commandsTemplate.php'
        ]);
        $getOpt->setHelp($help);

        self::assertSame(
            'Usage: ' . $scriptName . ' <command> [operands]' . PHP_EOL .
            PHP_EOL .
            'Available commands:' . PHP_EOL .
            '  test  Run this tests' . PHP_EOL,
            $getOpt->getHelpText()
        );
    }
}
