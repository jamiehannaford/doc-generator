<?php

namespace OpenStack\DocGenerator;

use OpenStack\DocGenerator\Writer\WriterFactory;
use Symfony\Component\Filesystem\Filesystem;

class ServiceGenerator
{
    use HasFileHelpersTrait;

    private $namespace;
    private $docPath;
    private $serviceMethods;
    private $filesystem;
    private $writerFactory;

    public function __construct($namespace, $docPath, $descPath)
    {
        // Make sure namespace and paths exist
        $this->validate($namespace, $docPath, $descPath);
        $this->namespace = $namespace;
        $this->docPath   = $this->trim($docPath);

        // Set collaborators
        $this->filesystem = new Filesystem();
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
                // Ignore methods from abstract classes and non-public ones
                if ($method->getDeclaringClass()->isAbstract() || !$method->isPublic()) {
                    continue;
                }

                $this->serviceMethods[$method->getName()] = $method;
            }
        }

        return $this->serviceMethods;
    }

    private function methodRequiresParamsFile(\ReflectionMethod $method)
    {
        $proceed = false;

        foreach ($method->getParameters() as $param) {
            if ($param->getName() == 'options') {
                $proceed = true;
            }
        }

        return $proceed;
    }

    private function invokeWriterFactory($type, $file, \ReflectionMethod $method)
    {
        $writer = $this->writerFactory->create($type, $file, $method);
        $writer->write();
    }

    public function createSignatureFiles()
    {
        foreach ($this->getServiceMethods() as $name => $method) {
            $this->invokeWriterFactory('Signature', "{$name}.signature.rst", $method);
        }
    }

    public function createCodeSampleFiles()
    {
        foreach ($this->getServiceMethods() as $name => $method) {
            $this->invokeWriterFactory('CodeSample', "{$name}.sample.rst", $method);
        }
    }

    public function createParamsTableFiles()
    {
        foreach ($this->getServiceMethods() as $name => $method) {
            if ($this->methodRequiresParamsFile($method)) {
                $this->invokeWriterFactory('ParamsTable', "{$name}.params.rst", $method);
            }
        }
    }
}
