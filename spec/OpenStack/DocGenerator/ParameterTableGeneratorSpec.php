<?php

namespace spec\OpenStack\DocGenerator;

use GuzzleHttp\Command\Guzzle\Operation;
use GuzzleHttp\Command\Guzzle\Parameter;
use GuzzleHttp\Stream\StreamInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ParameterTableGeneratorSpec extends ObjectBehavior
{
    private $stream;
    private $parameter;

    function let(Operation $operation, Parameter $parameter, StreamInterface $stream)
    {
        $operation->getParams()->willReturn([$parameter]);

        $this->stream = $stream;
        $this->parameter = $parameter;

        $this->beConstructedWith($operation, $stream);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('OpenStack\DocGenerator\ParameterTableGenerator');
    }

    function it_should_have_writable_stream()
    {
        $this->getStream()->shouldReturnAnInstanceOf('GuzzleHttp\Stream\StreamInterface');
    }

    public function it_should_write_table()
    {
        $desc = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed '
            . 'Lorem ipsum dolor sit amet consectetur adipisicing elit, sed '
            . 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed '
            . 'do eiusmod tempor incididunt ut labore et dolore \\' . PHP_EOL
            . 'magna aliqua. Ut enim ad minim';

        $this->parameter->getStatic()->shouldBeCalled();
        $this->parameter->getEnum()->shouldBeCalled();
        $this->parameter->getName()->willReturn('Foo');
        $this->parameter->getType()->willReturn('string');
        $this->parameter->getRequired()->willReturn(false);
        $this->parameter->getDescription()->willReturn($desc);

        $block = <<<'EOT'
Parameters
~~~~~~~~~~

+--------+----------+------------+-----------------------------------------------------+
| Name   | Type     | Required   | Description                                         |
+========+==========+============+=====================================================+
| Foo    | string   | No         | Lorem ipsum dolor sit amet, consectetur             |
|        |          |            | adipisicing elit, sed Lorem ipsum dolor sit amet    |
|        |          |            | consectetur adipisicing elit, sed Lorem ipsum       |
|        |          |            | dolor sit amet, consectetur adipisicing elit, sed   |
|        |          |            | do eiusmod tempor incididunt ut labore et dolore    |
|        |          |            | magna aliqua. Ut enim ad minim                      |
+--------+----------+------------+-----------------------------------------------------+

EOT;
        $this->stream->write($block)->shouldBeCalled();

        $this->writeAll();
    }
}