<?php

namespace spec\OpenStack\DocGenerator;

use OpenStack\DocGenerator\Writer\AbstractWriter;
use OpenStack\DocGenerator\Writer\WriterFactory;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;

class ServiceGeneratorSpec extends ObjectBehavior
{
    private $filesystem;
    private $writerFactory;

    private $fixturesDir;
    private $docPath;
    private $namespace;
    private $descPath;

    function let(Filesystem $filesystem, WriterFactory $factory)
    {
        $this->fixturesDir = __DIR__ . '/Writer/fixtures';
        $this->namespace   = __NAMESPACE__ . '\\FixturesClass';
        $this->docPath     = $this->fixturesDir . '/doc/foo-service-v2';
        $this->descPath    = $this->fixturesDir . '/src/OpenStack/FooService/v2/Description';

        $this->setupFixtures();
        $this->beConstructedWith($this->namespace, $this->docPath, $this->descPath);

        $factory->beConstructedWith([$this->namespace, $this->docPath, $this->descPath]);
        $this->setWriterFactory($factory);
        $this->setFilesystem($filesystem);

        $this->filesystem = $filesystem;
        $this->writerFactory = $factory;
    }

    function letgo()
    {
        $fs = new Filesystem();
        $fs->remove([$this->fixturesDir . '/doc', $this->fixturesDir . '/src']);
    }

    private function setupFixtures()
    {
        $fs = new Filesystem();
        $fs->mkdir([$this->descPath, $this->docPath]);
        $fs->copy($this->fixturesDir . '/Service.yml', $this->descPath . '/Service.yml');
    }

    private function getGenDocPath($file = null)
    {
        $path = $this->docPath . DIRECTORY_SEPARATOR . '_generated';

        if ($file) {
            $path .= DIRECTORY_SEPARATOR . $file;
        }

        return $path;
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('OpenStack\DocGenerator\ServiceGenerator');
    }

    function it_throws_exception_if_class_does_not_exist()
    {
        $this->shouldThrow('InvalidArgumentException')->during__construct('FooService', null, null);
    }

    function it_throws_exception_if_doc_path_does_not_exist()
    {
        $this->shouldThrow('InvalidArgumentException')->during__construct(__CLASS__, __DIR__ . '/Foo', null);
    }

    function it_throws_exception_if_desc_path_does_not_exist()
    {
        $this->shouldThrow('InvalidArgumentException')->during__construct(__CLASS__, __DIR__, __DIR__ . '/Foo');
    }

    function it_should_make_doc_directory_for_service()
    {
        $this->filesystem->mkdir($this->getGenDocPath())->shouldBeCalled();

        $this->createDocDirectory();
    }

    function it_should_invoke_signature_writer_when_generating_signature_file(AbstractWriter $writer)
    {
        $fooMethod = $this->writerFactory->create('Signature', 'fooAction.signature.rst', Argument::type('\ReflectionMethod'));
        $fooMethod->shouldBeCalled();
        $fooMethod->willReturn($writer);

        $barMethod = $this->writerFactory->create('Signature', 'barAction.signature.rst', Argument::type('\ReflectionMethod'));
        $barMethod->shouldBeCalled();
        $barMethod->willReturn($writer);

        $this->createSignatureFiles();
    }

    function it_should_invoke_signature_writer_when_generating_code_sample_file(AbstractWriter $writer)
    {
        $fooMethod = $this->writerFactory->create('CodeSample', 'fooAction.sample.rst', Argument::type('\ReflectionMethod'));
        $fooMethod->shouldBeCalled();
        $fooMethod->willReturn($writer);

        $barMethod = $this->writerFactory->create('CodeSample', 'barAction.sample.rst', Argument::type('\ReflectionMethod'));
        $barMethod->shouldBeCalled();
        $barMethod->willReturn($writer);

        $this->createCodeSampleFiles();
    }

    function it_should_only_invoke_params_table_writer_when_method_has_additional_params(AbstractWriter $writer)
    {
        $fooMethod = $this->writerFactory->create('ParamsTable', 'fooAction.params.rst', Argument::type('\ReflectionMethod'));
        $fooMethod->shouldBeCalled();
        $fooMethod->willReturn($writer);

        $barMethod = $this->writerFactory->create('ParamsTable', 'barAction.params.rst', Argument::type('\ReflectionMethod'));
        $barMethod->shouldNotBeCalled();

        $this->createParamsTableFiles();
    }
}

abstract class AbstractClass
{
    public function abstractAction() {}
}

class FixturesClass extends AbstractClass
{
    public function fooAction($name, array $options = []) {}

    public function barAction($name, $expiry, $sku) {}
}