<?php

namespace spec\OpenStack\DocGenerator\DocBlock;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ReturnTagSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('@return string');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('OpenStack\DocGenerator\DocBlock\ReturnTag');
    }

    function it_should_throw_exception_if_input_string_is_malformed()
    {
        $this->shouldThrow('InvalidArgumentException')->during__construct('');
    }

    function it_handles_normal_return_types()
    {
        $this->beConstructedWith('@return string Description');
        $this->getType()->shouldReturn('string');
        $this->getDescription()->shouldReturn('Description');
        $this->getOperation()->shouldReturn(null);
    }

    function it_handles_operation_names()
    {
        $this->beConstructedWith('@return {FooOperation}');
        $this->getType()->shouldReturn(null);
        $this->getOperation()->shouldReturn('FooOperation');
    }
}