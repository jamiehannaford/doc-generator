<?php

namespace spec\OpenStack\DocGenerator;

use OpenStack\DocGenerator\ServiceRetriever;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;

class GeneratorSpec extends ObjectBehavior
{
    private $filesystem;

    function let()
    {
        $srcDir = __DIR__;
        $desDir = __DIR__;

        $this->beConstructedWith($srcDir, $desDir);

        // Create fixtures
        $this->filesystem = new Filesystem();
        $this->filesystem->mkdir(__DIR__ . '/foo-service-v2/_generated', 0777);
    }

    function letgo()
    {
        $this->filesystem->remove(__DIR__ . '/foo-service-v2');
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

        $retriever->retrieve()->willReturn([
            [
                'docPath'   => 'foo-service-v2',
                'namespace' => __NAMESPACE__ . '\\FixturesClass',
                'descPath'  => ''
            ]
        ]);

        $this->buildDocs();
    }

    function it_should_not_create_params_table_file_for_methods_that_have_no_options_param(
        Filesystem $filesystem, ServiceRetriever $retriever
    ) {
        $dir = __DIR__ . '/foo-service-v2/_generated/';
        $filesystem->remove($dir)->shouldBeCalled();
        $filesystem->mkdir($dir)->shouldBeCalled();

        $filesystem->touch($dir . 'barOperation.sample.rst')->shouldBeCalled();
        $filesystem->touch($dir . 'barOperation.params.rst')->shouldNotBeCalled();
        $filesystem->touch($dir . 'barOperation.signature.rst')->shouldBeCalled();

        $this->setRetriever($retriever);
        $this->setFilesystem($filesystem);

        $retriever->retrieve()->willReturn([
            [
                'docPath'   => 'foo-service-v2',
                'namespace' => __NAMESPACE__ . '\\OtherFixturesClass',
                'descPath'  => ''
            ]
        ]);

        $this->buildDocs();
    }
}

class FixturesClass
{
    /**
     * @param array $options
     *
     * @return mixed
     */
    public function fooOperation(array $options = []) {}
}

class OtherFixturesClass
{
    /**
     * @param string $name
     */
    public function barOperation($name) {}
}