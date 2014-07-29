<?php

namespace spec\OpenStack\DocGenerator;

use GuzzleHttp\Command\Guzzle\Operation;
use GuzzleHttp\Stream\StreamInterface;
use OpenStack\DocGenerator\ServiceFinder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GeneratorSpec extends ObjectBehavior
{
    private $stream;
    private $finder;

    function let(Operation $operation, StreamInterface $stream, ServiceFinder $finder)
    {
        $this->stream = $stream;
        $this->finder = $finder;

        $this->beConstructedWith($operation, $stream, $finder);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('OpenStack\DocGenerator\Generator');
    }

    function it_should_map_services_to_doc_directories()
    {
        $this->getServiceMapping()->shouldReturn([
            'OpenStack\\ObjectStore\\Service' => 'object-store'
        ]);
    }

    function it_should_allow_mappings_to_be_overriden()
    {
        $this->setServiceMapping(['Foo' => 'foo']);
        $this->getServiceMapping()->shouldReturn(['Foo' => 'foo']);
    }
}