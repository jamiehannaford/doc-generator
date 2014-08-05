<?php

namespace spec\OpenStack\DocGenerator;

use OpenStack\DocGenerator\ServiceFinder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GeneratorSpec extends ObjectBehavior
{
    private $finder;
    private $destinationDir;

    function let(ServiceFinder $finder)
    {
        $this->finder = $finder;
        $this->destinationDir = '/tmp/doc/_build/';

        $this->beConstructedWith(null, $this->destinationDir, $finder);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('OpenStack\DocGenerator\Generator');
    }

    function it_should_invoke_service_finder()
    {
        $this->finder->retrieveServiceDescriptions()->shouldBeCalled();
        $this->finder->retrieveServiceDescriptions()->willReturn([]);

        $this->writeFiles();
    }
}