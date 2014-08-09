<?php

namespace spec\OpenStack\DocGenerator\DocBlock;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ParamTagSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('@param $foo');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('OpenStack\DocGenerator\DocBlock\ParamTag');
    }

    function it_will_throw_exception_if_input_string_is_malformed()
    {
        $this->shouldThrow('InvalidArgumentException')->during__construct('foo');
    }

    function it_gives_name_to_normal_params()
    {
        $this->beConstructedWith('@param $foo');
        $this->getName()->shouldReturn('foo');
    }

    function it_gives_name_to_operations()
    {
        $this->beConstructedWith('@param $foo {Operation::Foo}');
        $this->getName()->shouldReturn('foo');
    }

    function it_gives_type_to_normal_params()
    {
        $this->beConstructedWith('@param string $foo');
        $this->getType()->shouldReturn('string');
    }

    function it_gives_no_type_to_operations()
    {
        $this->beConstructedWith('@param $foo {Operation::ParamName}');
        $this->getType()->shouldReturn('');
    }

    function it_gives_description_to_normal_params()
    {
        $this->beConstructedWith('@param string $foo This is a great description');
        $this->getDescription()->shouldReturn('This is a great description');
    }

    function it_gives_no_description_to_operations()
    {
        $this->beConstructedWith('@param $foo {Operation::ParamName}');
        $this->getDescription()->shouldBeNull();
    }

    function it_recognizes_operation_with_param_names()
    {
        $this->beConstructedWith('@param $foo {Operation::ParamName}');
        $this->shouldBeOperationParam();
    }

    function it_recognizes_operations_without_param_names()
    {
        $this->beConstructedWith('@param $foo {Operation}');
        $this->shouldBeOperationParam();
    }

    function it_does_not_consider_params_with_normal_strings_operations()
    {
        $this->beConstructedWith('@param string $foo This is a great description');
        $this->shouldNotBeOperationParam();
    }

    function it_parses_operation_name_and_param_name_for_operations_that_define_both()
    {
        $this->beConstructedWith('@param $foo {Operation::ParamName}');
        $this->getOperationName()->shouldReturn('Operation');
        $this->getOperationParamName()->shouldReturn('ParamName');
    }

    function it_parses_operation_name_for_operations_without_param_name()
    {
        $this->beConstructedWith('@param $foo {Operation}');
        $this->getOperationName()->shouldReturn('Operation');
        $this->getOperationParamName()->shouldReturn(null);
    }
}