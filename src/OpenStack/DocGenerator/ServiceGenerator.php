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
        $this->filesystem = new Filesystem();
        $this->writerFactory = new WriterFactory($namespace, $docPath, $descPath);

        $this->namespace = $namespace;
        $this->docPath   = $this->trim($docPath);

        $this->createDocDirectory();
        $this->validate($namespace, $docPath, $descPath);
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
        $path = $this->docPath . '_generated';

        if (file_exists($path)) {
            $this->filesystem->remove($path);
        }

        $this->filesystem->mkdir($path);
    }

    private function getServiceMethods()
    {
        if (null === $this->serviceMethods) {
            $reflection = new \ReflectionClass($this->namespace);
            foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                if ($method->getDeclaringClass()->isAbstract()) {
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

    private function walkServices(callable $fn)
    {
        if (!($methods = $this->getServiceMethods())) {
            return;
        }

        array_walk($methods, $fn);
    }

    public function buildDocs()
    {
        $this->walkServices(function ($method, $name) {
            $this->createSignature($name, $method);
            $this->createCodeSample($name, $method);
            $this->createParamsTable($name, $method);
        });
    }

    public function createSignatureFiles()
    {
        $this->walkServices(function ($method, $name) {
            $this->createSignature($name, $method);
        });
    }

    public function createCodeSampleFiles()
    {
        $this->walkServices(function ($method, $name) {
            $this->createCodeSample($name, $method);
        });
    }

    public function createParamsTableFiles()
    {
        $this->walkServices(function ($method, $name) {
            $this->createParamsTable($name, $method);
        });
    }
}