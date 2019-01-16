<?php

namespace GetOpt\Test\Options;

use GetOpt\GetOpt;
use GetOpt\Help;
use GetOpt\Option;
use PHPUnit\Framework\TestCase;

class HelpTest extends TestCase
{
    /** @test */
    public function helpText()
    {
        $getopt = new GetOpt([
            [ 'a', 'alpha', GetOpt::NO_ARGUMENT, 'Short and long options with no argument' ],
            [ null, 'beta', GetOpt::OPTIONAL_ARGUMENT, 'Long option only with an optional argument' ],
            [ 'c', null, GetOpt::REQUIRED_ARGUMENT, 'Short option only with a mandatory argument' ]
        ]);
        $getopt->process('');

        $script = $_SERVER['PHP_SELF'];

        self::assertSame(
            'Usage: ' . $script . ' [options] [operands]' . PHP_EOL . PHP_EOL .
            'Options:' . PHP_EOL .
            '  -a, --alpha     Short and long options with no argument' . PHP_EOL .
            '  --beta [<arg>]  Long option only with an optional argument' . PHP_EOL .
            '  -c <arg>        Short option only with a mandatory argument' . PHP_EOL . PHP_EOL,
            $getopt->getHelpText()
        );
    }

    /** @test */
    public function helpTextWithoutDescriptions()
    {
        $getopt = new GetOpt([
            [ 'a', 'alpha', GetOpt::NO_ARGUMENT ],
            [ null, 'beta', GetOpt::OPTIONAL_ARGUMENT ],
            [ 'c', null, GetOpt::REQUIRED_ARGUMENT ]
        ]);

        $script = $_SERVER['PHP_SELF'];

        self::assertSame(
            'Usage: ' . $script . ' [options] [operands]' . PHP_EOL . PHP_EOL .
            'Options:' . PHP_EOL .
            '  -a, --alpha     ' . PHP_EOL .
            '  --beta [<arg>]  ' . PHP_EOL .
            '  -c <arg>        ' . PHP_EOL . PHP_EOL,
            $getopt->getHelpText()
        );
    }

    /** @test */
    public function helpTextWithLongDescriptions()
    {
        defined('COLUMNS') || define('COLUMNS', 90);

        $getopt = new GetOpt([
            [
                'a', 'alpha', GetOpt::NO_ARGUMENT, 'Short and long options with no argument and a very long text ' .
                                                   'that exceeds the length of the row'
            ],
            [ null, 'beta', GetOpt::OPTIONAL_ARGUMENT, 'Long option only with an optional argument' ],
            [ 'c', null, GetOpt::REQUIRED_ARGUMENT, 'Short option only with a mandatory argument' ]
        ]);

        $script = $_SERVER['PHP_SELF'];
        self::assertSame(
            'Usage: ' . $script . ' [options] [operands]' . PHP_EOL . PHP_EOL .
            'Options:' . PHP_EOL .
            '  -a, --alpha     Short and long options with no argument and a very long text that' . PHP_EOL .
            '                  exceeds the length of the row' . PHP_EOL .
            '  --beta [<arg>]  Long option only with an optional argument' . PHP_EOL .
            '  -c <arg>        Short option only with a mandatory argument' . PHP_EOL . PHP_EOL,
            $getopt->getHelpText()
        );
    }

    /** @test */
    public function longWordsInDescription()
    {
        defined('COLUMNS') || define('COLUMNS', 90);

        $getopt = new GetOpt([
            ['a', 'alpha', GetOpt::OPTIONAL_ARGUMENT, 'This-is-a-long-word-with-dashes-what-should-not-cause-an-issue'],
            ['b', 'beta', GetOpt::OPTIONAL_ARGUMENT, 'ThisWillCauseAnIssueBecauseWeDontKnowWhereToBreakInThisLongWord'],
        ]);

        $script = $_SERVER['PHP_SELF'];
        self::assertSame(
            'Usage: ' . $script . ' [options] [operands]' . PHP_EOL . PHP_EOL .
            'Options:' . PHP_EOL .
            '  -a, --alpha [<arg>]  This-is-a-long-' . PHP_EOL .
            '                       word-with-dashes-' . PHP_EOL .
            '                       what-should-not-' . PHP_EOL .
            '                       cause-an-issue' . PHP_EOL .
            '  -b, --beta [<arg>]   ThisWillCauseAnIs' . PHP_EOL .
            '                       sueBecauseWeDontK' . PHP_EOL .
            '                       nowWhereToBreakIn' . PHP_EOL .
            '                       ThisLongWord' . PHP_EOL . PHP_EOL,
            $getopt->getHelpText([Help::MAX_WIDTH => 40])
        );
    }

    /** @test */
    public function helpTextWithArgumentName()
    {
        $getopt = new GetOpt([
            Option::create('a', 'alpha', GetOpt::REQUIRED_ARGUMENT)
                ->setArgumentName('alpha')
        ]);

        $script = $_SERVER['PHP_SELF'];
        self::assertSame(
            'Usage: ' . $script . ' [options] [operands]' . PHP_EOL . PHP_EOL .
            'Options:' . PHP_EOL .
            '  -a, --alpha <alpha>  ' . PHP_EOL . PHP_EOL,
            $getopt->getHelpText()
        );
    }

    /** @test */
    public function textsGetUsed()
    {
        $getopt = new GetOpt([Option::create('a', 'alpha')]);

        $getopt->getHelp()->setTexts([
            'usage-title' => '',
            'options-title' => 'Parameters:' . PHP_EOL
        ]);

        $script = $_SERVER['PHP_SELF'];
        self::assertSame(
            $script . ' [options] [operands]' . PHP_EOL . PHP_EOL .
            'Parameters:' . PHP_EOL .
            '  -a, --alpha  ' . PHP_EOL . PHP_EOL,
            $getopt->getHelpText()
        );
    }
}
