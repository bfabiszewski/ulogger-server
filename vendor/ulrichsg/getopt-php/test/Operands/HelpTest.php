<?php

namespace GetOpt\Test\Operands;

use GetOpt\ArgumentException\Missing;
use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Help;
use GetOpt\Operand;
use PHPUnit\Framework\TestCase;

class HelpTest extends TestCase
{
    /** @test */
    public function helpContainsOperandNames()
    {
        $operand1 = new Operand('op1', true);
        $operand2 = new Operand('op2', false);
        $script = $_SERVER['PHP_SELF'];

        $getopt = new GetOpt();
        $getopt->addOperands([$operand1, $operand2]);

        self::assertSame(
            'Usage: ' . $script . ' <op1> [<op2>] [operands]' . PHP_EOL . PHP_EOL,
            $getopt->getHelpText()
        );
    }

    /** @test */
    public function helpCommandDefinesOperands()
    {
        $operand1 = new Operand('op1', true);
        $operand2 = new Operand('op2', false);
        $script = $_SERVER['PHP_SELF'];

        $getopt = new GetOpt();
        $command =Command::create('command', 'var_dump')->setDescription('This is any command');
        $command->addOperands([$operand1, $operand2]);
        $getopt->addCommand($command);

        try {
            $getopt->process('command');
        } catch (Missing $exception) {
        }

        self::assertSame(
            'Usage: ' . $script . ' command <op1> [<op2>] [operands]' . PHP_EOL . PHP_EOL .
            'This is any command' . PHP_EOL . PHP_EOL,
            $getopt->getHelpText()
        );
    }

    /** @test */
    public function helpTextForMultiple()
    {
        $operand = new Operand('op1', Operand::MULTIPLE);
        $script = $_SERVER['PHP_SELF'];

        $getopt = new GetOpt();
        $getopt->addOperand($operand);

        self::assertSame(
            'Usage: ' . $script . ' [<op1>] [<op1>...]' . PHP_EOL . PHP_EOL,
            $getopt->getHelpText()
        );
    }

    /** @test */
    public function helpTextForRequiredMultiple()
    {
        $operand = new Operand('op1', Operand::MULTIPLE + Operand::REQUIRED);
        $script = $_SERVER['PHP_SELF'];

        $getopt = new GetOpt();
        $getopt->addOperand($operand);

        self::assertSame(
            'Usage: ' . $script . ' <op1> [<op1>...]' . PHP_EOL . PHP_EOL,
            $getopt->getHelpText()
        );
    }

    /** @test */
    public function showsDescriptionsBeforeOptions()
    {
        $script = $_SERVER['PHP_SELF'];
        $getOpt = new GetOpt(null, [GetOpt::SETTING_STRICT_OPERANDS => true]);
        $getOpt->addOperand(
            Operand::create('file', Operand::REQUIRED)
                ->setDescription('The file to copy')
        );
        $getOpt->addOperand(
            Operand::create('destination', Operand::OPTIONAL)
                ->setDescription('The destination folder (current folder by default)')
        );

        self::assertSame(
            'Usage: ' . $script . ' <file> [<destination>] ' . PHP_EOL . PHP_EOL .
            'Operands:' . PHP_EOL .
            '  <file>           The file to copy' . PHP_EOL .
            '  [<destination>]  The destination folder (current folder by default)' . PHP_EOL . PHP_EOL,
            $getOpt->getHelpText()
        );
    }

    /** @test */
    public function hidesDescriptionsIfRequested()
    {
        $script = $_SERVER['PHP_SELF'];
        $getOpt = new GetOpt(null, [GetOpt::SETTING_STRICT_OPERANDS => true]);
        $getOpt->addOperand(
            Operand::create('file', Operand::REQUIRED)
                ->setDescription('The file to copy')
        );
        $getOpt->addOperand(
            Operand::create('destination', Operand::OPTIONAL)
                ->setDescription('The destination folder (current folder by default)')
        );

        self::assertSame(
            'Usage: ' . $script . ' <file> [<destination>] ' . PHP_EOL . PHP_EOL,
            $getOpt->getHelpText([Help::HIDE_OPERANDS => true])
        );
    }
}
