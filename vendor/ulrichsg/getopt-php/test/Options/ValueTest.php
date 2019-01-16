<?php

namespace GetOpt\Test\Options;

use GetOpt\GetOpt;
use GetOpt\Option;
use PHPUnit\Framework\TestCase;

class ValueTest extends TestCase
{
    /** @dataProvider dataOptionsWithoutDefault
     * @param Option $option
     * @param mixed  $expected
     * @test */
    public function valueWithoutDefault(Option $option, $expected)
    {
        $result = $option->getValue();

        self::assertSame($expected, $result);
    }

    /** @dataProvider dataOptionsWithoutDefault
     * @param Option $option
     * @param mixed  $dummy
     * @param mixed  $value
     * @param mixed  $expected
     * @test */
    public function valueWithoutDefaultButSetValue(Option $option, $dummy, $value, $expected)
    {
        $option->setValue($value);

        $result = $option->getValue();

        self::assertSame($expected, $result);
    }

    public function dataOptionsWithoutDefault()
    {
        return [
            [ Option::create('a', null, GetOpt::NO_ARGUMENT), null, null, 1],
            [ Option::create('a', null, GetOpt::OPTIONAL_ARGUMENT), null, null, 1],
            [ Option::create('a', null, GetOpt::OPTIONAL_ARGUMENT), null, 'val', 'val'],
            [ Option::create('a', null, GetOpt::REQUIRED_ARGUMENT), null, 'val', 'val'],
            [ Option::create('a', null, GetOpt::MULTIPLE_ARGUMENT), [], 'val', ['val']],
        ];
    }

    /** @test */
    public function toStringWithoutArgument()
    {
        $option = new Option('a', null);
        $option->setValue(null);
        $option->setValue(null);

        $this->assertSame('2', (string)$option);
    }

    /** @test */
    public function toStringWithArgument()
    {
        $option = new Option('a', null, GetOpt::REQUIRED_ARGUMENT);
        $option->setValue('valueA');

        $this->assertSame('valueA', (string)$option);
    }

    /** @test */
    public function toStringWithMultipleArguments()
    {
        $option = new Option('a', null, GetOpt::MULTIPLE_ARGUMENT);
        $option->setValue('valueA');
        $option->setValue('valueB');

        $this->assertSame('valueA,valueB', (string)$option);
    }

    /** @test */
    public function defaultValueNotUsedForCounting()
    {
        $option = new Option('a', null, GetOpt::OPTIONAL_ARGUMENT);
        $option->setDefaultValue(42);

        $option->setValue(null);

        $this->assertSame(1, $option->getValue());
    }
}
