<?php

namespace spec\OpenStack\DocGenerator\Writer;

use GuzzleHttp\Stream\StreamInterface;
use OpenStack\Common\Rest\ServiceDescription;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CodeSampleSpec extends ObjectBehavior
{
    private $stream;
    private $description;

    function let(StreamInterface $stream, ServiceDescription $description)
    {
        $this->stream = $stream;

        $class  = new \ReflectionClass(__NAMESPACE__ . '\\CodeSampleClass');
        $method = $class->getMethod('barOperation');

        $this->description = $description;

        $this->beConstructedWith($stream, $method, $description);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('OpenStack\DocGenerator\Writer\CodeSample');
    }
}

class CodeSampleClass
{
    public function barOperation() {}
}