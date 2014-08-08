<?php

namespace spec\OpenStack\DocGenerator;

use OpenStack\DocGenerator\ServiceRetriever;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;

class GeneratorSpec extends ObjectBehavior
{
    function let()
    {
        $srcDir = __DIR__;
        $desDir = __DIR__;

        $this->beConstructedWith($srcDir, $desDir);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('OpenStack\DocGenerator\Generator');
    }

    function it_should_use_retriever_to_retrieve_service_classes(
        ServiceRetriever $retriever
    ) {
        $retriever->retrieve()->shouldBeCalled();
        $this->setRetriever($retriever);

        $this->buildDocs();
    }

    function it_should_generate_three_files_for_each_service_method(
        Filesystem $filesystem, ServiceRetriever $retriever
    ) {
        $dir = __DIR__ . '/foo-service-v2/_generated/';
        $filesystem->remove($dir)->shouldBeCalled();
        $filesystem->mkdir($dir)->shouldBeCalled();

        $filesystem->touch($dir . 'fooOperation.params.rst')->shouldBeCalled();
        $filesystem->touch($dir . 'fooOperation.sample.rst')->shouldBeCalled();
        $filesystem->touch($dir . 'fooOperation.signature.rst')->shouldBeCalled();

        $this->setRetriever($retriever);
        $this->setFilesystem($filesystem);

        $filesystem = new Filesystem();
        $filesystem->mkdir(__DIR__ . '/foo-service-v2/_generated', 0777);

        $retriever->retrieve()->willReturn([
            [
                'docPath'   => 'foo-service-v2',
                'namespace' => __NAMESPACE__ . '\\FixturesClass',
                'descPath'  => ''
            ]
        ]);

        $this->buildDocs();

        $filesystem->remove(__DIR__ . '/foo-service-v2');
    }
}

class FixturesClass
{
    public function fooOperation() {}
}