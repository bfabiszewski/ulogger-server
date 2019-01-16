<?php

namespace GetOpt\Test;

use GetOpt\Argument;
use GetOpt\Arguments;
use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Option;
use PHPUnit\Framework\TestCase;

class ArgumentsTest extends TestCase
{
    /** @var GetOpt */
    protected $getopt;

    protected function setUp()
    {
        $this->getopt = new GetOpt();
    }

    /** @test */
    public function parseNoOptions()
    {
        $this->getopt->process(Arguments::fromString('something'));

        self::assertCount(0, $this->getopt->getOptions());
        $operands = $this->getopt->getOperands();
        self::assertCount(1, $operands);
        self::assertEquals('something', $operands[0]);
    }

    /** @test */
    public function parseUnknownOption()
    {
        $this->setExpectedException('GetOpt\ArgumentException\Unexpected');
        $this->getopt->addOption(new Option('a', null));

        $this->getopt->process('-b');
    }

    /** @test */
    public function unknownLongOption()
    {
        $this->setExpectedException('GetOpt\ArgumentException\Unexpected');
        $this->getopt->addOption(new Option('a', 'alpha'));

        $this->getopt->process('--beta');
    }

    /** @test */
    public function parseRequiredArgumentMissing()
    {
        $this->setExpectedException('GetOpt\ArgumentException\Missing');
        $this->getopt->addOption(new Option('a', null, GetOpt::REQUIRED_ARGUMENT));

        $this->getopt->process('-a');
    }

    /** @test */
    public function parseMultipleOptionsWithOneHyphen()
    {
        $this->getopt->addOptions([
            new Option('a'),
            new Option('b'),
        ]);

        $this->getopt->process('-ab');

        $options = $this->getopt->getOptions();
        self::assertEquals(1, $options['a']);
        self::assertEquals(1, $options['b']);
    }

    /** @test */
    public function parseCumulativeOption()
    {
        $this->getopt->addOptions([
            new Option('a'),
            new Option('b'),
        ]);

        $this->getopt->process('-a -b -a -a');

        $options = $this->getopt->getOptions();
        self::assertEquals(3, $options['a']);
        self::assertEquals(1, $options['b']);
    }

    /** @test */
    public function parseCumulativeOptionShort()
    {
        $this->getopt->addOptions([
            new Option('a'),
            new Option('b'),
        ]);

        $this->getopt->process('-abaa');

        $options = $this->getopt->getOptions();
        self::assertEquals(3, $options['a']);
        self::assertEquals(1, $options['b']);
    }

    /** @test */
    public function parseShortOptionWithArgument()
    {
        $this->getopt->addOptions([
            new Option('a', null, GetOpt::REQUIRED_ARGUMENT)
        ]);

        $this->getopt->process('-a value');

        $options = $this->getopt->getOptions();
        self::assertEquals('value', $options['a']);
    }

    /** @test */
    public function parseZeroArgument()
    {
        $this->getopt->addOptions([
            new Option('a', null, GetOpt::REQUIRED_ARGUMENT)
        ]);

        $this->getopt->process('-a 0');

        $options = $this->getopt->getOptions();
        self::assertEquals('0', $options['a']);
    }

    /** @test */
    public function parseNumericOption()
    {
        $this->getopt->addOptions([
            new Option('a', null, GetOpt::REQUIRED_ARGUMENT),
            new Option('2', null)
        ]);

        $this->getopt->process('-a 2 -2');

        $options = $this->getopt->getOptions();
        self::assertEquals('2', $options['a']);
        self::assertEquals(1, $options['2']);
    }

    /** @test */
    public function parseCollapsedShortOptionsRequiredArgumentMissing()
    {
        $this->setExpectedException('GetOpt\ArgumentException\Missing');
        $this->getopt->addOptions([
            new Option('a', null),
            new Option('b', null, GetOpt::REQUIRED_ARGUMENT)
        ]);
        $this->getopt->process('-ab');
    }

    /** @test */
    public function parseCollapsedShortOptionsWithArgument()
    {
        $this->getopt->addOptions([
            new Option('a', null),
            new Option('b', null, GetOpt::REQUIRED_ARGUMENT)
        ]);
        $this->getopt->process('-ab value');

        $options = $this->getopt->getOptions();
        self::assertEquals(1, $options['a']);
        self::assertEquals('value', $options['b']);
    }

    /** @test */
    public function parseNoArgumentOptionAndOperand()
    {
        $this->getopt->addOptions([
            new Option('a', null),
        ]);
        $this->getopt->process('-a b');

        $options = $this->getopt->getOptions();
        self::assertEquals(1, $options['a']);
        $operands = $this->getopt->getOperands();
        self::assertCount(1, $operands);
        self::assertEquals('b', $operands[0]);
    }

    /** @test */
    public function parsedRequiredArgumentWithNoSpace()
    {
        $this->getopt->addOptions([
            new Option('p', null, GetOpt::REQUIRED_ARGUMENT)
        ]);
        $this->getopt->process('-ppassword');
        $options = $this->getopt->getOptions();
        self::assertEquals('password', $options['p']);
    }
    /** @test */
    public function parseCollapsedRequiredArgumentWithNoSpace()
    {
        $this->getopt->addOptions([
            new Option('v', null),
            new Option('p', null, GetOpt::REQUIRED_ARGUMENT)
        ]);
        $this->getopt->process('-vvvppassword');
        $options = $this->getopt->getOptions();
        self::assertEquals('password', $options['p']);
        self::assertEquals(3, $options['v']);
    }

    /** @test */
    public function parseOperandsOnly()
    {
        $this->getopt->addOptions([
            new Option('a', null, GetOpt::REQUIRED_ARGUMENT),
            new Option('b', null)
        ]);
        $this->getopt->process('-- -a -b');

        self::assertCount(0, $this->getopt->getOptions());
        $operands = $this->getopt->getOperands();
        self::assertCount(2, $operands);
        self::assertEquals('-a', $operands[0]);
        self::assertEquals('-b', $operands[1]);
    }

    /** @test */
    public function parseLongOptionWithoutArgument()
    {
        $this->getopt->addOptions([
            new Option('o', 'option', GetOpt::OPTIONAL_ARGUMENT)
        ]);
        $this->getopt->process('--option');

        $options = $this->getopt->getOptions();
        self::assertEquals(1, $options['option']);
    }

    /** @test */
    public function parseLongOptionWithoutArgumentAndOperand()
    {
        $this->getopt->addOptions([
            new Option('o', 'option', GetOpt::NO_ARGUMENT)
        ]);
        $this->getopt->process('--option something');

        $options = $this->getopt->getOptions();
        self::assertEquals(1, $options['option']);
        $operands = $this->getopt->getOperands();
        self::assertCount(1, $operands);
        self::assertEquals('something', $operands[0]);
    }

    /** @test */
    public function parseLongOptionWithArgument()
    {
        $this->getopt->addOptions([
            new Option('o', 'option', GetOpt::OPTIONAL_ARGUMENT)
        ]);
        $this->getopt->process('--option value');

        $options = $this->getopt->getOptions();
        self::assertEquals('value', $options['option']);
        self::assertEquals('value', $options['o']);
    }

    /** @test */
    public function parseLongOptionWithEqualsSignAndArgument()
    {
        $this->getopt->addOptions([
            new Option('o', 'option', GetOpt::OPTIONAL_ARGUMENT)
        ]);
        $this->getopt->process('--option=value something');

        $options = $this->getopt->getOptions();
        self::assertEquals('value', $options['option']);
        $operands = $this->getopt->getOperands();
        self::assertCount(1, $operands);
        self::assertEquals('something', $operands[0]);
    }

    /** @test */
    public function parseLongOptionWithValueStartingWithHyphen()
    {
        $this->getopt->addOptions([
            new Option('o', 'option', GetOpt::REQUIRED_ARGUMENT)
        ]);
        $this->getopt->process('--option=-value');

        $options = $this->getopt->getOptions();
        self::assertEquals('-value', $options['option']);
    }

    /** @test */
    public function parseNoValueStartingWithHyphenRequired()
    {
        $this->setExpectedException('GetOpt\ArgumentException\Missing');
        $this->getopt->addOptions([
            new Option('a', null, GetOpt::REQUIRED_ARGUMENT),
            new Option('b', null)
        ]);
        $this->getopt->process('-a -b');
    }

    /** @test */
    public function parseNoValueStartingWithHyphenOptional()
    {
        $this->getopt->addOptions([
            new Option('a', null, GetOpt::OPTIONAL_ARGUMENT),
            new Option('b', null)
        ]);
        $this->getopt->process('-a -b');

        $options = $this->getopt->getOptions();
        self::assertEquals(1, $options['a']);
        self::assertEquals(1, $options['b']);
    }

    /** @test */
    public function parseOptionWithDefaultValue()
    {
        $optionA = new Option('a', null, GetOpt::REQUIRED_ARGUMENT);
        $optionA->setArgument(new Argument(10));
        $optionB = new Option('b', 'beta', GetOpt::REQUIRED_ARGUMENT);
        $optionB->setArgument(new Argument(20));
        $this->getopt->addOptions([$optionA, $optionB]);
        $this->getopt->process('-a 12');

        $options = $this->getopt->getOptions();
        self::assertEquals(12, $options['a']);
        self::assertEquals(20, $options['b']);
        self::assertEquals(20, $options['beta']);
    }

    /** @test */
    public function multipleArgumentOptions()
    {
        $this->getopt->addOption(new Option('a', null, GetOpt::MULTIPLE_ARGUMENT));

        $this->getopt->process('-a value1 -a value2');

        self::assertEquals(['value1', 'value2'], $this->getopt->getOption('a'));
    }

    /** @test */
    public function doubleHyphenNotInOperands()
    {
        $this->getopt->addOptions([
            new Option('a', null, GetOpt::REQUIRED_ARGUMENT)
        ]);
        $this->getopt->process('-a 0 foo -- bar baz');

        $options = $this->getopt->getOptions();
        self::assertEquals('0', $options['a']);
        $operands = $this->getopt->getOperands();
        self::assertCount(3, $operands);
        self::assertEquals('foo', $operands[0]);
        self::assertEquals('bar', $operands[1]);
        self::assertEquals('baz', $operands[2]);
    }

    /** @test */
    public function singleHyphenValue()
    {
        $this->getopt->addOptions([
            new Option('a', 'alpha', GetOpt::REQUIRED_ARGUMENT)
        ]);

        $this->getopt->process('-a -');

        $options = $this->getopt->getOptions();
        self::assertEquals('-', $options['a']);
        $operands = $this->getopt->getOperands();
        self::assertCount(0, $operands);

        $this->getopt->process('--alpha -');

        $options = $this->getopt->getOptions();
        self::assertEquals('-', $options['a']);
        $operands = $this->getopt->getOperands();
        self::assertCount(0, $operands);
    }

    /** @test */
    public function singleHyphenOperand()
    {
        $this->getopt->addOptions([
            new Option('a', null, GetOpt::REQUIRED_ARGUMENT)
        ]);
        $this->getopt->process('-a 0 -');

        $options = $this->getopt->getOptions();
        self::assertEquals('0', $options['a']);
        $operands = $this->getopt->getOperands();
        self::assertCount(1, $operands);
        self::assertEquals('-', $operands[0]);
    }

    /** @test */
    public function optionsAfterOperands()
    {
        $this->getopt->addOptions([
            new Option('a', null, GetOpt::REQUIRED_ARGUMENT),
            new Option('b', null, GetOpt::REQUIRED_ARGUMENT)
        ]);

        $this->getopt->process('-a 42 operand -b "don\'t panic"');

        self::assertEquals([
            'a' => 42,
            'b' => 'don\'t panic'
        ], $this->getopt->getOptions());
        self::assertEquals(['operand'], $this->getopt->getOperands());
    }

    /** @test */
    public function emptyOperandsAndOptionsWithString()
    {
        $this->getopt->addOptions([
            new Option('a', null, GetOpt::REQUIRED_ARGUMENT)
        ]);

        $this->getopt->process('-a "" ""');

        self::assertSame(['a' => ''], $this->getopt->getOptions());
        self::assertSame([''], $this->getopt->getOperands());
    }

    /** @test */
    public function emptyOperandsAndOptionsWithArray()
    {
        $this->getopt->addOptions([
            new Option('a', null, GetOpt::REQUIRED_ARGUMENT)
        ]);

        // this is how we get it in $_SERVER['argv']
        $this->getopt->process([
            '-a',
            '',
            ''
        ]);

        self::assertSame(['a' => ''], $this->getopt->getOptions());
        self::assertSame([''], $this->getopt->getOperands());
    }

    /** @test */
    public function spaceOperand()
    {
        $this->getopt->addOptions([]);

        $this->getopt->process('" "');

        self::assertSame([' '], $this->getopt->getOperands());
    }

    /** @test */
    public function parseWithArgumentValidation()
    {
        $validation = 'is_numeric';
        $optionA = new Option('a', null, GetOpt::OPTIONAL_ARGUMENT);
        $optionA->setArgument(new Argument(null, $validation));
        $optionB = new Option('b', null, GetOpt::REQUIRED_ARGUMENT);
        $optionB->setArgument(new Argument(null, $validation));
        $optionC = new Option('c', null, GetOpt::OPTIONAL_ARGUMENT);
        $optionC->setArgument(new Argument(null, $validation));
        $this->getopt->addOptions([$optionA, $optionB, $optionC]);
        $this->getopt->process('-a 1 -b 2 -c');

        $options = $this->getopt->getOptions();
        self::assertSame('1', $options['a']);
        self::assertSame('2', $options['b']);
        self::assertSame(1, $options['c']);
    }

    /** @test */
    public function parseInvalidArgument()
    {
        $this->setExpectedException('GetOpt\ArgumentException\Invalid');
        $validation = 'is_numeric';
        $option = new Option('a', null, GetOpt::OPTIONAL_ARGUMENT);
        $option->setArgument(new Argument(null, $validation));
        $this->getopt->addOptions([$option]);
        $this->getopt->process('-a nonnumeric');
    }

    /** @test */
    public function stringWithSingleQuotes()
    {
        $this->getopt->addOptions([
            new Option('a', 'optA', GetOpt::REQUIRED_ARGUMENT),
        ]);

        $this->getopt->process('-a \'the value\'');
        $options = $this->getopt->getOptions();

        self::assertSame('the value', $options['a']);
    }

    /** @test */
    public function stringWithDoubleQuotes()
    {
        $this->getopt->addOptions([
            new Option('a', 'optA', GetOpt::REQUIRED_ARGUMENT),
        ]);

        $this->getopt->process('-a "the value"');
        $options = $this->getopt->getOptions();

        self::assertSame('the value', $options['a']);
    }

    /** @test */
    public function singleQuotesInString()
    {
        $this->getopt->addOptions([
            new Option('a', 'optA', GetOpt::REQUIRED_ARGUMENT),
        ]);

        $this->getopt->process('-a "the \'"');
        $options = $this->getopt->getOptions();

        self::assertSame('the \'', $options['a']);
    }

    /** @test */
    public function doubleQuotesInString()
    {
        $this->getopt->addOptions([
            new Option('a', 'optA', GetOpt::REQUIRED_ARGUMENT),
        ]);

        $this->getopt->process('-a \'the "\'');
        $options = $this->getopt->getOptions();

        self::assertSame('the "', $options['a']);
    }

    /** @test */
    public function quoteConcatenation()
    {
        $this->getopt->addOptions([
            new Option('a', 'optA', GetOpt::REQUIRED_ARGUMENT),
            new Option('b', 'optB', GetOpt::REQUIRED_ARGUMENT),
        ]);

        $this->getopt->process('-a \'\'"\'"\' inside single quote\' -b ""\'"\'" inside double quote"');
        $options = $this->getopt->getOptions();

        self::assertSame('\' inside single quote', $options['a']);
        self::assertSame('" inside double quote', $options['b']);
    }

    /** @test */
    public function quoteEscapingDoubleQuote()
    {
        $this->getopt->process('-- "this \\" is a double quote"');

        self::assertSame('this " is a double quote', $this->getopt->getOperand(0));
    }

    /** @test */
    public function quoteEscapingSingleQuote()
    {
        $this->getopt->process("-- 'this \\' is a single quote'");

        self::assertSame("this ' is a single quote", $this->getopt->getOperand(0));
    }

    /** @test */
    public function linefeedAsSeparator()
    {
        $this->getopt->addOptions([
            new Option('a', 'optA', GetOpt::REQUIRED_ARGUMENT),
        ]);

        $this->getopt->process("-a\nvalue");
        $options = $this->getopt->getOptions();

        self::assertSame('value', $options['a']);
    }

    /** @test */
    public function tabAsSeparator()
    {
        $this->getopt->addOptions([
            new Option('a', 'optA', GetOpt::REQUIRED_ARGUMENT),
        ]);

        $this->getopt->process("-a\tvalue");
        $options = $this->getopt->getOptions();

        self::assertSame('value', $options['a']);
    }

    /** @test */
    public function explictArguments()
    {
        $getopt = $this->getopt;
        $this->getopt->addOptions([
            Option::create('a'),
            Option::create('b')->setValidation(function () use ($getopt) {
                return is_null($getopt->getOption('a'));
            })
        ]);

        $this->setExpectedException('GetOpt\ArgumentException\Invalid');
        $this->getopt->process('-a -b');
    }

    /** @test */
    public function usingCommand()
    {
        $cmd = new Command('test', 'var_dump', [
            new Option('a', 'alpha')
        ]);
        $this->getopt->addCommand($cmd);

        $this->getopt->process('test -a --alpha');

        self::assertSame(2, $this->getopt->getOption('a'));
        self::assertSame($cmd, $this->getopt->getCommand());
    }
}
