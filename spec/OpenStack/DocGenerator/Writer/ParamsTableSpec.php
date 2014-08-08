<?php

namespace spec\OpenStack\DocGenerator\Writer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ParamsTableSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('OpenStack\DocGenerator\Writer\ParamsTable');
    }
}