<?php

namespace spec\OpenStack\DocGenerator;

use GuzzleHttp\Command\Guzzle\Operation;
use GuzzleHttp\Command\Guzzle\Parameter;
use GuzzleHttp\Stream\StreamInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CodeSampleGeneratorSpec extends ObjectBehavior
{
    private $operation;
    private $stream;
    private $parameter;

    function let(Operation $operation, Parameter $parameter, StreamInterface $stream)
    {
        $this->operation = $operation;
        $operation->getName()->willReturn('FooOperation');
        $operation->getParams()->willReturn([$parameter]);

        $this->stream = $stream;
        $this->parameter = $parameter;

        $this->beConstructedWith($operation, $stream);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('OpenStack\DocGenerator\CodeSampleGenerator');
    }

    function it_should_check_to_see_whether_operation_is_an_iterator()
    {
        return;
        $this->operation->getData('iterator')->shouldBeCalled();

        $this->writeAll();
    }

    function it_should_write_variable_block_for_normal_command(
        Parameter $param1, Parameter $param2, Parameter $param3
    ) {
        $param1->getName()->willReturn('BarBarBar');
        $param1->getType()->willReturn('string');
        $param1->getEnum()->willReturn(null);
        $param1->getRequired()->willReturn(true);

        $param2->getName()->willReturn('Baz');
        $param2->getType()->willReturn('array');
        $param2->getEnum()->willReturn(null);
        $param2->getRequired()->willReturn(false);

        $param3->getName()->willReturn('Foo');
        $param3->getType()->willReturn('string');
        $param3->getEnum()->willReturn(['json', 'xml']);
        $param3->getRequired()->willReturn(false);

        $this->operation->getData('iterator')->willReturn(null);
        $this->operation->getParams()->willReturn([$param1, $param2, $param3]);

        $this->beConstructedWith($this->operation, $this->stream);

        $block = <<<'EOT'
Sample code
~~~~~~~~~~~

.. code-block:: php

  $response = $service->fooOperation([
      'BarBarBar' => '{string}', // required
      'Baz'       => '{array}',
      'Foo'       => 'json|xml',
  ]);

EOT;

        $this->stream->write($block)->shouldBeCalled();

        $this->writeAll();
    }
}