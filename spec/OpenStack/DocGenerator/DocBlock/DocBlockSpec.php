<?php

namespace spec\OpenStack\DocGenerator\DocBlock;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DocBlockSpec extends ObjectBehavior
{
    function let()
    {
        $string = <<<'EOT'
/**
 * @param string $fooParam This is a description
 */
EOT;
        $this->beConstructedWith($string);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('OpenStack\DocGenerator\DocBlock\DocBlock');
    }

    function it_handles_type_differences()
    {
        $string = <<<'EOT'
/**
 * @param string $fooParam This is a description
 * @param $barParam {BarOperation::ParamName}
 */
EOT;

        $this->beConstructedWith($string);

        $this->getParamTags()->shouldBeArray();
        $this->getParamTag('fooParam')->shouldReturnAnInstanceOf('OpenStack\DocGenerator\DocBlock\ParamTag');
        $this->getParamTag('bazParam')->shouldReturn(null);
    }

    function it_handles_return_tags()
    {
        $string = <<<'EOT'
/**
 * @param string $fooParam This is a description
 *
 * @return {FooOperation}
 */
EOT;

        $this->beConstructedWith($string);

        $this->getReturnTag()->shouldReturnAnInstanceOf('OpenStack\DocGenerator\DocBlock\ReturnTag');
    }
}