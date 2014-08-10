<?php

namespace OpenStack\DocGenerator;

use OpenStack\DocGenerator\Writer\WriterFactory;
use Symfony\Component\Filesystem\Filesystem;

class ServiceGenerator
{
    use HasFileHelpersTrait;

    private $namespace;
    private $docPath;
    private $filesystem;
    private $writerFactory;
    private $serviceMethods;

    public function __construct($namespace, $docPath, $descPath)
    {
        $this->validate($namespace, $docPath, $descPath);

        $this->namespace = $namespace;
        $this->docPath   = $this->trim($docPath);

        $this->filesystem    = new Filesystem();
        $this->writerFactory = new WriterFactory($namespace, $docPath, $descPath);
    }

    public function setFilesystem(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function setWriterFactory(WriterFactory $factory)
    {
        $this->writerFactory = $factory;
    }

    public function createDocDirectory()
    {
        $this->filesystem->mkdir($this->docPath . '_generated');
    }

    private function getServiceMethods()
    {
        if (null === $this->serviceMethods) {
            $reflection = new \ReflectionClass($this->namespace);
            foreach ($reflection->getMethods() as $method) {
                if ($method->getDeclaringClass()->isAbstract() || !$method->isPublic()) {
                    continue;
                }

                $this->serviceMethods[$method->getName()] = $method;
            }
        }

        return $this->serviceMethods;
    }

    private function invokeWriterFactory($type, $file, \ReflectionMethod $method)
    {
        $writer = $this->writerFactory->create($type, $file, $method);
        $writer->write();
    }

    private function createSignature($name, \ReflectionMethod $method)
    {
        $this->invokeWriterFactory('Signature', "{$name}.signature.rst", $method);
    }

    private function createCodeSample($name, \ReflectionMethod $method)
    {
        $this->invokeWriterFactory('CodeSample', "{$name}.sample.rst", $method);
    }

    private function createParamsTable($name, \ReflectionMethod $method)
    {
        foreach ($method->getParameters() as $param) {
            if ($param->getName() == 'options') {
                $this->invokeWriterFactory('ParamsTable', "{$name}.params.rst", $method);
                break;
            }
        }
    }

    public function buildDocs()
    {
        $this->createDocDirectory();

        foreach ($this->getServiceMethods() as $name => $method) {
            $this->createSignature($name, $method);
            $this->createCodeSample($name, $method);
            $this->createParamsTable($name, $method);
        }
    }

    public function createSignatureFiles()
    {
        foreach ($this->getServiceMethods() as $name => $method) {
            $this->createSignature($name, $method);
        }
    }

    public function createCodeSampleFiles()
    {
        foreach ($this->getServiceMethods() as $name => $method) {
            $this->createCodeSample($name, $method);
        }
    }

    public function createParamsTableFiles()
    {
        foreach ($this->getServiceMethods() as $name => $method) {
            $this->createParamsTable($name, $method);
        }
    }
}