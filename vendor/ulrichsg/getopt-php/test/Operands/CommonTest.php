<?php

namespace GetOpt\Test\Operands;

use GetOpt\ArgumentException\Missing;
use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Operand;
use PHPUnit\Framework\TestCase;

class CommonTest extends TestCase
{
    /** @test */
    public function operandsAreResetted()
    {
        $getopt = new GetOpt();
        $getopt->process('"any operand"');

        $getopt->process('');

        self::assertSame([], $getopt->getOperands());
    }

    /** @test */
    public function addOperands()
    {
        $operand1 = new Operand('op1');
        $operand2 = new Operand('op2');
        $operand3 = new Operand('op3');

        $getopt = new GetOpt();
        $getopt->addOperand($operand1);
        $getopt->addOperands([$operand2, $operand3]);

        self::assertSame([$operand1, $operand2, $operand3], $getopt->getOperandObjects());
    }

    /** @test */
    public function operandValidation()
    {
        $operand = Operand::create('op1')
            ->setValidation(function ($value) {
                return $value === null; // this validator is always false
            });

        $getopt = new GetOpt();
        $getopt->addOperand($operand);

        $this->setExpectedException('GetOpt\ArgumentException\Invalid');
        $getopt->process('"any value"');
    }

    /** @test */
    public function optionalOperand()
    {
        $operand = new Operand('op1', Operand::OPTIONAL); // false is default

        $getopt = new GetOpt();
        $getopt->addOperand($operand);
        $getopt->process('');

        self::assertSame([], $getopt->getOperands());
    }

    /** @test */
    public function requiredOperand()
    {
        $operand = new Operand('op1', Operand::REQUIRED);

        $getopt = new GetOpt();
        $getopt->addOperand($operand);

        $this->setExpectedException('GetOpt\ArgumentException\Missing');
        $getopt->process('');
    }

    /** @test */
    public function getOperandByName()
    {
        $operand = new Operand('op1');

        $getopt = new GetOpt();
        $getopt->addOperand($operand);
        $getopt->process('42');

        self::assertSame('42', $getopt->getOperand('op1'));
    }

    /** @test */
    public function defaultValue()
    {
        $operand = Operand::create('op1')
            ->setDefaultValue(42);

        $getopt = new GetOpt();
        $getopt->addOperand($operand);
        $getopt->process('');

        self::assertSame(42, $getopt->getOperand('op1'));
    }

    /** @test */
    public function allPreviousOperandsGetRequiredToo()
    {
        $operand1 = new Operand('op1', Operand::OPTIONAL);
        $operand2 = new Operand('op2', Operand::REQUIRED);

        $getopt = new GetOpt();
        $getopt->addOperands([$operand1, $operand2]);

        self::assertTrue($getopt->getOperandObjects()[0]->isRequired());
    }

    /** @test */
    public function commandsCanHaveOperands()
    {
        $operand = new Operand('op1');
        $command = new Command('command1', 'var_dump');
        $command->addOperands([$operand]);

        self::assertSame([$operand], $command->getOperands());
    }

    /** @test */
    public function commandWithOperand()
    {
        $getopt = new GetOpt();
        $command = new Command('command', 'var_dump');
        $operand = new Operand('file');
        $command->addOperand($operand);
        $getopt->addCommand($command);

        $getopt->process('command path/to/file');

        self::assertSame('path/to/file', $getopt->getOperand('file'));
    }

    /** @test */
    public function returnsNullForUnknownOperands()
    {
        $getopt = new GetOpt();

        $result = $getopt->getOperand('file');

        self::assertNull($result);
    }

    /** @test */
    public function requireMakesRequired()
    {
        $operand = new Operand('op1');

        $operand->required();

        self::assertTrue($operand->isRequired());
    }

    /** @test */
    public function requireFalse()
    {
        $operand = new Operand('op1', Operand::REQUIRED);

        $operand->required(false);

        self::assertFalse($operand->isRequired());
    }

    /** @test */
    public function requireDoesNotMakeAnOperandMultiple()
    {
        $operand = new Operand('op1');

        $operand->required();
        $operand->required();

        self::assertTrue($operand->isRequired());
        self::assertFalse($operand->isMultiple());
    }

    /** @test */
    public function multipleMakesMultiple()
    {
        $operand = new Operand('op1');

        $operand->multiple();

        self::assertTrue($operand->isMultiple());
    }

    /** @test */
    public function multipleFalse()
    {
        $operand = new Operand('op1', Operand::MULTIPLE);

        $operand->multiple(false);

        self::assertFalse($operand->isMultiple());
    }

    /** @test */
    public function requiredMultipleThrowsMissing()
    {
        $operand = new Operand('port');
        $operand->multiple(true);
        $operand->required(true);

        $getOpt = new GetOpt();
        $getOpt->addOperand($operand);

        $this->setExpectedException('GetOpt\ArgumentException\Missing', 'Operand port is required');

        $getOpt->process('');
    }
}
