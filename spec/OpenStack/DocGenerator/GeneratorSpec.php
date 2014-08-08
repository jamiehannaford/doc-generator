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

    function it_should_generate_directory_for_each_service(
        Filesystem $filesystem, ServiceRetriever $retriever
    ) {
        $dir = __DIR__ . '/foo-service-v2/_generated/';
        $filesystem->remove($dir)->shouldBeCalled();
        $filesystem->mkdir($dir)->shouldBeCalled();
        $filesystem->touch(Argument::type('string'))->shouldBeCalled();

        $retriever->retrieve()->willReturn([
            'foo-service-v2' => '\ArrayAccess'
        ]);

        $this->setRetriever($retriever);
        $this->setFilesystem($filesystem);

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

        $retriever->retrieve()->willReturn([
            'foo-service-v2' => __NAMESPACE__ . '\\FixturesClass'
        ]);

        $this->setRetriever($retriever);
        $this->setFilesystem($filesystem);

        $this->buildDocs();
    }
}

class FixturesClass
{
    public function fooOperation() {}
}