<?php

namespace spec\OpenStack\DocGenerator;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ServiceFinderSpec extends ObjectBehavior
{
    private $sourceDir;

    function let()
    {
        $this->sourceDir = __DIR__ . '/../../../vendor/php-opencloud/openstack/src/OpenStack/';

        $this->beConstructedWith($this->sourceDir, [
            'ObjectStore' => 'object-store'
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('OpenStack\DocGenerator\ServiceFinder');
    }

    function it_throws_exception_if_map_contains_non_existent_service_classes()
    {
        $this->shouldThrow('\RuntimeException')->during__construct('', ['Foo' => 'foo']);
    }

    function it_should_find_description_files_for_every_service()
    {
        $this->getServices([
            'object-store' => [
                $this->sourceDir . 'ObjectStore/Description/v2.0.yml'
            ]
        ]);
    }

    function it_should_read_description_files_for_params()
    {
        
    }
}