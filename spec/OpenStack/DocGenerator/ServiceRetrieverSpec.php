<?php

namespace spec\OpenStack\DocGenerator;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ServiceRetrieverSpec extends ObjectBehavior
{
    private $srcDir;

    function let()
    {
        $this->srcDir = __DIR__ . '/../../../vendor/php-opencloud/openstack/src/OpenStack/';

        $this->beConstructedWith($this->srcDir);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('OpenStack\DocGenerator\ServiceRetriever');
    }

    function it_will_throw_exception_if_no_services_exist()
    {
        $this->beConstructedWith(__DIR__);
        $this->shouldThrow('RuntimeException')->duringRetrieve();
    }

    function it_will_retrieve_service_paths()
    {
        $this->retrieve()->shouldReturn([
            [
                'docPath'   => 'compute-v2',
                'namespace' => 'OpenStack\Compute\v2\Service',
                'descPath'  => $this->srcDir . 'Compute/v2/Description'
            ],
            [
                'docPath'   => 'compute-v3',
                'namespace' => 'OpenStack\Compute\v3\Service',
                'descPath'  => $this->srcDir . 'Compute/v3/Description'
            ],
            [
                'docPath'   => 'object-store-v2',
                'namespace' => 'OpenStack\ObjectStore\v2\Service',
                'descPath'  => $this->srcDir . 'ObjectStore/v2/Description'
            ]
        ]);
    }
}