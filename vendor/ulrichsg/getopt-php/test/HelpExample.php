<?php

namespace GetOpt\Test;

use GetOpt\GetOpt;
use GetOpt\HelpInterface;

class HelpExample implements HelpInterface
{
    /**
     * Render the help text for $getopt
     *
     * @param GetOpt $getopt
     * @param array  $data
     * @return string
     */
    public function render(GetOpt $getopt, array $data = [])
    {
        return 'no help for you!';
    }
}
