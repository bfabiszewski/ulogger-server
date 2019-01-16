<?php

namespace GetOpt\Test\Operands;

use GetOpt\GetOpt;
use GetOpt\Operand;
use PHPUnit\Framework\TestCase;

class StrictTest extends TestCase
{
    /** @test */
    public function noOperandsAllowed()
    {
        $getopt = new GetOpt();
        $getopt->set(GetOpt::SETTING_STRICT_OPERANDS, true);

        $this->setExpectedException('GetOpt\ArgumentException\Unexpected');
        $getopt->process('"some operand"');
    }

    /** @test */
    public function specifiedOperandsAllowed()
    {
        $getopt = new GetOpt();
        $getopt->set(GetOpt::SETTING_STRICT_OPERANDS, true);

        $getopt->addOperand(new Operand('op1'));
        $getopt->process('"some operand"');

        self::assertSame('some operand', $getopt->getOperand('op1'));
    }

    /** @test */
    public function helpDoesNotShowAdditionalOperands()
    {
        $getopt = new GetOpt();
        $getopt->set(GetOpt::SETTING_STRICT_OPERANDS, true);
        $getopt->addOperand(new Operand('file', true));
        $script = $_SERVER['PHP_SELF'];

        self::assertSame(
            'Usage: ' . $script . ' <file> ' . PHP_EOL . PHP_EOL,
            $getopt->getHelpText()
        );
    }
}
