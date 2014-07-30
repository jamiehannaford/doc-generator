<?php

namespace spec\OpenStack\DocGenerator;

use GuzzleHttp\Command\Guzzle\Operation;
use GuzzleHttp\Command\Guzzle\Parameter;
use OpenStack\DocGenerator\ServiceFinder;
use PhpSpec\Exception\Example\FailureException;
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

        $this->beConstructedWith($finder, null, $this->destinationDir);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('OpenStack\DocGenerator\Generator');
    }

    function it_should_create_param_rst_files(Operation $operation, Parameter $param)
    {
        $operation->getName()->willReturn('FooOperation');

        $operation->getParams()->shouldBeCalled();
        $operation->getParams()->willReturn([$param]);

        $this->finder->retrieveServiceParameters()->shouldBeCalled();
        $this->finder->retrieveServiceParameters()->willReturn([
            'object-store-v2' => [$operation],
            'compute-v2'      => [$operation],
            'compute-v3'      => [$operation]
        ]);

        $this->writeFiles();

        $paths = [
            $this->destinationDir . 'object-store-v2/_generated/FooOperation.params.rst',
            $this->destinationDir . 'compute-v2/_generated/FooOperation.params.rst',
            $this->destinationDir . 'compute-v3/_generated/FooOperation.params.rst'
        ];

        foreach ($paths as $path) {
            if (!file_exists($path)) {
                throw new FailureException(sprintf("%s should exist, but does not", $path));
            }
        }
    }
}