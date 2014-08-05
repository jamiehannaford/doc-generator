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
        $this->parameter->getEnum()->shouldBeCalled();
        $this->parameter->getName()->willReturn('a');
        $this->parameter->getType()->willReturn('b');
        $this->parameter->getRequired()->willReturn(false);
        $this->parameter->getDescription()->willReturn('d');

        $block = <<<'EOT'
Parameters
~~~~~~~~~~

.. csv-table::
  :header: "Name", "Type", "Required", "Description"
  :widths: 20, 20, 10, 50

  "a", "b", "No", "d"

EOT;

        $this->stream->write($block)->shouldBeCalled();

        $this->writeAll();
    }
}