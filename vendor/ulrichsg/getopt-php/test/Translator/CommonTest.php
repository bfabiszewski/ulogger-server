<?php

namespace GetOpt\Test\Translator;

use GetOpt\Translator;
use PHPUnit\Framework\TestCase;

class CommonTest extends TestCase
{
    /** @test */
    public function throwsWhenLanguageNotAvailable()
    {
        self::setExpectedException('InvalidArgumentException');

        new Translator('unknown');
    }

    /** @test */
    public function usesTranslationFile()
    {
        $translator = new Translator(__DIR__ . '/incomplete-translation.php');

        $result = $translator->translate('usage-title');

        self::assertSame('Verwendung: ', $result);
    }

    /** @test */
    public function usesFallBackTranslation()
    {
        $translator = new Translator(__DIR__ . '/incomplete-translation.php');

        $result = $translator->translate('commands-title');

        self::assertSame("Commands:\n", $result);
    }
}
