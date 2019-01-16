<?php

namespace GetOpt\Test\Options;

use GetOpt\Argument;
use GetOpt\GetOpt;
use GetOpt\Option;
use PHPUnit\Framework\TestCase;

class CommonTest extends TestCase
{
    /** @test */
    public function construct()
    {
        $option = new Option('a', 'az-AZ09_', GetOpt::OPTIONAL_ARGUMENT);
        $this->assertEquals('a', $option->getShort());
        $this->assertEquals('az-AZ09_', $option->getLong());
        $this->assertEquals(GetOpt::OPTIONAL_ARGUMENT, $option->getMode());
    }

    /** @test */
    public function create()
    {
        $option = Option::create('a', 'az-AZ09_', GetOpt::OPTIONAL_ARGUMENT);
        $this->assertEquals('a', $option->getShort());
        $this->assertEquals('az-AZ09_', $option->getLong());
        $this->assertEquals(GetOpt::OPTIONAL_ARGUMENT, $option->getMode());
    }

    /** @dataProvider dataConstructFails
     * @param string $short
     * @param string $long
     * @param int    $mode
     * @test */
    public function constructFails($short, $long, $mode)
    {
        $this->setExpectedException('InvalidArgumentException');
        new Option($short, $long, $mode);
    }

    public function dataConstructFails()
    {
        return [
            [ null, null, GetOpt::NO_ARGUMENT ],      // long and short are both empty
            [ '&', null, GetOpt::NO_ARGUMENT ],       // short name must be one of [a-zA-Z0-9?!§$%#]
            [ null, 'öption', GetOpt::NO_ARGUMENT ],  // long name may contain only alphanumeric chars, _ and -
            [ 'a', null, 'no_argument' ],             // invalid mode
            [ null, 'a', GetOpt::NO_ARGUMENT ]        // long name must be at least 2 characters long
        ];
    }

    /** @test */
    public function setArgument()
    {
        $option = new Option('a', null, GetOpt::OPTIONAL_ARGUMENT);
        $this->assertEquals($option, $option->setArgument(new Argument()));
        $this->assertInstanceof(Argument::CLASSNAME, $option->getArgument());
    }

    /** @test */
    public function setArgumentWrongMode()
    {
        $this->setExpectedException('InvalidArgumentException');
        $option = new Option('a', null, GetOpt::NO_ARGUMENT);
        $option->setArgument(new Argument());
    }

    /** @test */
    public function setDefaultValue()
    {
        $option = new Option('a', null, GetOpt::OPTIONAL_ARGUMENT);
        $this->assertEquals($option, $option->setDefaultValue(10));
        $this->assertEquals(10, $option->getArgument()->getDefaultValue());
    }

    /** @test */
    public function setValidation()
    {
        $option = new Option('a', null, GetOpt::OPTIONAL_ARGUMENT);
        $this->assertEquals($option, $option->setValidation('is_numeric'));
        $this->assertTrue($option->getArgument()->hasValidation());
    }
}
