<?php

namespace spec\OpenStack\DocGenerator;

use PhpSpec\Exception\Example\FailureException;
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

    public function getMatchers()
    {
        return [
            'haveKey' => function($subject, $key) {
                    return array_key_exists($key, $subject);
                }
        ];
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

    function it_should_return_array_where_keys_are_service_versions()
    {
        $this->retrieveServiceParameters()->shouldBeArray();
        $this->retrieveServiceParameters()->shouldHaveKey('object-store-v2');
    }
}