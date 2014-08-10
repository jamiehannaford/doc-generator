<?php

namespace spec\OpenStack\DocGenerator;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class BatchGeneratorSpec extends ObjectBehavior
{
    function let()
    {
        $srcDir = __DIR__;
        $desDir = __DIR__;

        $this->beConstructedWith($srcDir, $desDir);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('OpenStack\DocGenerator\BatchGenerator');
    }
}