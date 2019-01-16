<?php

namespace GetOpt\Test\Argument;

use GetOpt\Argument;
use GetOpt\Describable;
use GetOpt\GetOpt;
use GetOpt\Operand;
use GetOpt\Option;
use PHPUnit\Framework\TestCase;

class ValidationTest extends TestCase
{
    protected function tearDown()
    {
        GetOpt::setLang('en'); // reset the language
        parent::tearDown();
    }

    /** @test */
    public function defaultMessageForOption()
    {
        $option = Option::create('a', 'alpha', GetOpt::REQUIRED_ARGUMENT)
            ->setValidation('is_numeric');

        $this->setExpectedException(
            'GetOpt\ArgumentException\Invalid',
            sprintf('Option \'%s\' has an invalid value', 'alpha')
        );

        $option->setValue('foo');
    }

    /** @test */
    public function defaultMessageForOperand()
    {
        $operand = Operand::create('alpha')
            ->setValidation('is_numeric');

        $this->setExpectedException(
            'GetOpt\ArgumentException\Invalid',
            sprintf('Operand \'%s\' has an invalid value', 'alpha')
        );

        $operand->setValue('foo');
    }

    /** @test */
    public function defaultMessageForArgument()
    {
        $argument = new Argument(null, 'is_numeric', 'alpha');

        $this->setExpectedException(
            'GetOpt\ArgumentException\Invalid',
            sprintf('Argument \'%s\' has an invalid value', 'alpha')
        );

        $argument->setValue('foo');
    }

    /** @test */
    public function usesCustomMessage()
    {
        $option = Option::create('a', 'alpha', GetOpt::REQUIRED_ARGUMENT)
            ->setValidation('is_numeric', 'alpha has to be numeric');

        $this->setExpectedException(
            'GetOpt\ArgumentException\Invalid',
            'Alpha has to be numeric'
        );

        $option->setValue('foo');
    }

    /** @test */
    public function usesTranslatedDescriptions()
    {
        GetOpt::setLang('de');
        $operand = Operand::create('alpha')
            ->setValidation('is_numeric', 'Die value von %s muss numerisch sein');

        $this->setExpectedException(
            'GetOpt\ArgumentException\Invalid',
            sprintf('Die value von Operand \'%s\' muss numerisch sein', 'alpha')
        );

        $operand->setValue('foo');
    }

    /** @test */
    public function providesValueAsSecondReplacement()
    {
        $option = Option::create('a', 'alpha', GetOpt::REQUIRED_ARGUMENT)
            ->setValidation('is_numeric', '%s %s');

        $this->setExpectedException(
            'GetOpt\ArgumentException\Invalid',
            'Option \'alpha\' foo'
        );

        $option->setValue('foo');
    }

    /** @test */
    public function usesCallbackToGetMessage()
    {
        $option = Option::create('a', 'alpha', GetOpt::REQUIRED_ARGUMENT)
            ->setValidation('is_numeric', function () {
                return 'alpha has to be numeric';
            });

        $this->setExpectedException(
            'GetOpt\ArgumentException\Invalid',
            'alpha has to be numeric'
        );

        $option->setValue('foo');
    }

    /** @test */
    public function providesOptionAndValue()
    {
        $option = Option::create('a', 'alpha', GetOpt::REQUIRED_ARGUMENT);
        $option->setValidation('is_numeric', function (Describable $object, $value) use ($option) {

            $this->assertSame('foo', $value);
            $this->assertSame($option, $object);

            return 'anything';
        });

        $this->setExpectedException('GetOpt\ArgumentException\Invalid');
        $option->setValue('foo');
    }

    /** @test */
    public function providesOperandAndValue()
    {
        $operand = Operand::create('alpha');
        $operand->setValidation('is_numeric', function (Describable $object, $value) use ($operand) {

            $this->assertSame('foo', $value);
            $this->assertSame($operand, $object);

            return 'anything';
        });

        $this->setExpectedException('GetOpt\ArgumentException\Invalid');
        $operand->setValue('foo');
    }
}
