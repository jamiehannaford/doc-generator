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

    public function getMatchers()
    {
        return [
            'containString' => function(StreamInterface $stream, $string) {
                return $string === \GuzzleHttp\Stream\copy_to_string($stream);
            }
        ];
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('OpenStack\DocGenerator\ParameterTableGenerator');
    }

    function it_should_have_writable_stream()
    {
        $this->getStream()->shouldReturnAnInstanceOf('GuzzleHttp\Stream\StreamInterface');
    }

    function it_should_write_section_header_to_stream()
    {
        $string = <<<EOT
Parameters
~~~~~~~~~~
EOT;

        $this->stream->write($string)->shouldBeCalled();

        $this->writeSectionHeader();
    }

    function it_should_write_directive_to_stream()
    {
        $string = ".. csv-table::\n";

        $this->stream->write($string)->shouldBeCalled();

        $this->writeDirective();
    }

    function it_should_write_titles_to_stream()
    {
        $string = ':header: "Name", "Type", "Required", "Description"' . "\n";

        $this->stream->write($string)->shouldBeCalled();

        $this->writeTitles();
    }

    function it_should_write_widths_to_stream()
    {
        $string = ":widths: 20, 20, 10, 50\n";

        $this->stream->write($string)->shouldBeCalled();

        $this->writeWidths();
    }

    function it_should_call_parameter_for_required_values()
    {
        $this->parameter->getName()->shouldBeCalled();
        $this->parameter->getType()->shouldBeCalled();
        $this->parameter->getRequired()->shouldBeCalled();
        $this->parameter->getDescription()->shouldBeCalled();

        $this->writeParameters();
    }

    function it_should_write_param_values_into_csv_format()
    {
        $this->parameter->getName()->willReturn('a');
        $this->parameter->getType()->willReturn('b');
        $this->parameter->getRequired()->willReturn(false);
        $this->parameter->getDescription()->willReturn('d');

        $string = '"a","b","No","d"' . "\n";
        $this->stream->write($string)->shouldBeCalled();

        $this->writeParameters();
    }
}