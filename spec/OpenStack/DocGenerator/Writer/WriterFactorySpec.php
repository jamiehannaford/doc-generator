<?php

namespace spec\OpenStack\DocGenerator\Writer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Parser;

class WriterFactorySpec extends ObjectBehavior
{
    private $yamlParser;
    private $fixturesDir;
    private $namespace;
    private $docPath;
    private $descPath;
    private $filesystem;
    private $method;

    function let(Parser $parser)
    {
        $this->filesystem  = new Filesystem();
        $this->fixturesDir = __DIR__ . '/fixtures';

        $this->namespace = __NAMESPACE__ . '\\WriterFactoryFixturesClass';
        $this->docPath   = $this->fixturesDir . '/doc/foo-service-v2';
        $this->descPath  = $this->fixturesDir . '/src/OpenStack/FooService/v2/Description';

        $parser->parse(Argument::type('string'))->willReturn([]);

        $this->yamlParser = $parser;
        $this->setupFixtures();

        $this->beConstructedWith($this->namespace, $this->docPath, $this->descPath, $parser);

        $this->method = (new \ReflectionClass($this->namespace))->getMethod('fooAction');
    }

    function letgo()
    {
        $this->filesystem->remove([$this->fixturesDir . '/doc', $this->fixturesDir . '/src']);
    }

    private function setupFixtures()
    {
        $this->filesystem->mkdir([$this->descPath, $this->docPath . '/_generated']);
        $this->filesystem->copy($this->fixturesDir . '/Service.yml', $this->descPath . '/Service.yml');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('OpenStack\DocGenerator\Writer\WriterFactory');
    }

    function it_parses_yaml_files_and_creates_a_description()
    {
        $this->yamlParser->parse(Argument::type('string'))->shouldBeCalled();

        $this->create('signature', 'foo', $this->method);
    }

    function it_returns_signature_writer()
    {
        $this->create('Signature', 'foo', $this->method)
            ->shouldReturnAnInstanceOf('OpenStack\DocGenerator\Writer\Signature');
    }

    function it_returns_params_table_writer()
    {
        $this->create('ParamsTable', 'foo', $this->method)
            ->shouldReturnAnInstanceOf('OpenStack\DocGenerator\Writer\ParamsTable');
    }

    function it_returns_code_sample_writer()
    {
        $this->create('CodeSample', 'foo', $this->method)
            ->shouldReturnAnInstanceOf('OpenStack\DocGenerator\Writer\CodeSample');
    }

    function it_throws_exception_when_no_writer_matches()
    {
        $this->shouldThrow('InvalidArgumentException')->duringCreate('Foo', 'foo', $this->method);
    }
}

abstract class WriterFactoryAbstractClass
{
    public function abstractAction() {}
}

class WriterFactoryFixturesClass extends WriterFactoryAbstractClass
{
    public function fooAction($name, array $options = []) {}

    public function barAction($name, $expiry, $sku) {}
}