<?php

namespace GetOpt\Test;

use GetOpt\Argument;
use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Help;
use GetOpt\Operand;
use GetOpt\Option;
use PHPUnit\Framework\TestCase;

class MagicGettersTest extends TestCase
{
    /** @dataProvider provideGetOptAttributes
     * @test */
    public function getOptUsesMagicGetters($getOpt, $attribute, $expected)
    {
        $result = $getOpt->{$attribute};

        self::assertSame($expected, $result);
    }

    public function provideGetOptAttributes()
    {
        $getOpt = new GetOpt();
        $getOpt->addOption(Option::create('a', 'alpha'));
        $getOpt->addCommand($command = Command::create('test', 'var_dump'));
        $getOpt->setHelp($help = new Help());
        $getOpt->process('test --alpha omega');

        return [
            [ $getOpt, 'options', ['a' => 1, 'alpha' => 1] ],
            [ $getOpt, 'operands', ['omega'] ],
            [ $getOpt, 'command', $command ],
            [ $getOpt, 'commands', [ 'test' => $command ] ],
            [ $getOpt, 'help', $help ],
            [
                $getOpt,
                'helpText',
                'Usage: ' . $getOpt->get(GetOpt::SETTING_SCRIPT_NAME) . ' test [options] [operands]' .
                '' . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL .
                'Options:' . PHP_EOL .
                '  -a, --alpha  ' . PHP_EOL . PHP_EOL
            ],
            [ $getOpt, 'anything', null ],
        ];
    }

    /** @dataProvider provideCommandAttributes
     * @test */
    public function commandUsesMagicGetters($command, $attribute, $expected)
    {
        $result = $command->{$attribute};

        self::assertSame($expected, $result);
    }

    public function provideCommandAttributes()
    {
        $command = new Command('test', 'Foo@Bar');
        $command->setDescription('This is the long description');
        $command->setShortDescription('This is the short description');
        $command->addOption($option = Option::create('a', 'alpha'));

        return [
            [ $command, 'name', 'test' ],
            [ $command, 'handler', 'Foo@Bar' ],
            [ $command, 'description', 'This is the long description' ],
            [ $command, 'shortDescription', 'This is the short description' ],
            [ $command, 'options', [ $option ] ],
        ];
    }

    /** @dataProvider provideArgumentAttributes
     * @test */
    public function argumentUsesMagicGetters($argument, $attribute, $expected)
    {
        $result = $argument->{$attribute};

        self::assertSame($expected, $result);
    }

    public function provideArgumentAttributes()
    {
        $argument = new Argument('default', 'strlen', 'gamma');

        return [
            [ $argument, 'name', 'gamma' ],
            [ $argument, 'defaultValue', 'default' ],
        ];
    }

    /** @dataProvider provideOperandAttributes
     * @test */
    public function operandUsesMagicGetters($operand, $attribute, $expected)
    {
        $result = $operand->{$attribute};

        self::assertSame($expected, $result);
    }

    public function provideOperandAttributes()
    {
        $operand = new Operand('gamma');
        $operand->setValue('42');

        return [
            [ $operand, 'value', '42' ],
            [ $operand, 'name', 'gamma' ],
        ];
    }

    /** @dataProvider provideOptionAttributes
     * @test */
    public function optionUsesMagicGetters($option, $attribute, $expected)
    {
        $result = $option->{$attribute};

        self::assertSame($expected, $result);
    }

    public function provideOptionAttributes()
    {
        $option = new Option('a', 'alpha', GetOpt::OPTIONAL_ARGUMENT);
        $option->setDescription('This is the description');
        $argument = $option->getArgument();
        $option->setValue('42');

        return [
            [ $option, 'description', 'This is the description' ],
            [ $option, 'short', 'a' ],
            [ $option, 'long', 'alpha' ],
            [ $option, 'mode', GetOpt::OPTIONAL_ARGUMENT ],
            [ $option, 'argument', $argument ],
            [ $option, 'value', '42' ],
        ];
    }
}
