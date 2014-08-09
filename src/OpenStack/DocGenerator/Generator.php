<?php

namespace OpenStack\DocGenerator;

use GuzzleHttp\Stream\Stream;
use OpenStack\Common\Rest\ServiceDescription;
use OpenStack\DocGenerator\Writer\Signature;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;

class Generator
{
    private $sourcePath;
    private $destinationPath;
    private $retriever;
    private $filesystem;
    private $yamlParser;

    public function __construct($sourcePath, $destinationPath)
    {
        $this->sourcePath = $sourcePath;
        $this->destinationPath = $destinationPath;
    }

    private function getRetriever()
    {
        if (null === $this->retriever) {
            $this->retriever = new ServiceRetriever($this->sourcePath);
        }

        return $this->retriever;
    }

    public function setRetriever(ServiceRetriever $retriever)
    {
        $this->retriever = $retriever;
    }

    private function getFilesystem()
    {
        if (null === $this->filesystem) {
            $this->filesystem = new Filesystem();
        }

        return $this->filesystem;
    }

    public function setFilesystem(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    private function getYamlParser()
    {
        if (null === $this->yamlParser) {
            $this->yamlParser = new Parser();
        }

        return $this->yamlParser;
    }

    public function setYamlParser(Yaml $parser)
    {
        $this->yamlParser = $parser;
    }

    public function buildDocs()
    {
        $services = $this->getRetriever()->retrieve();

        if (empty($services)) {
            return;
        }

        foreach ($services as $service) {
            // Wipe and create new _generated directory
            $docPath = $this->getServiceDocPath($service['docPath']);
            $this->getFilesystem()->remove($docPath);
            $this->getFilesystem()->mkdir($docPath);

            $description = $this->createDescription($service['descPath']);

            $reflection = new \ReflectionClass($service['namespace']);
            foreach ($reflection->getMethods() as $method) {
                // Ignore methods from abstract classes and non-public ones
                if ($method->getDeclaringClass()->isAbstract() || !$method->isPublic()) {
                    continue;
                }

                // Create a signature
                $this->writeSignatureFile($docPath, $method, $description);

                // Create code sample
                $this->writeSampleFile($docPath, $method);

                // Create additional params table if necessary
                if ($this->methodRequiresParamsFile($method)) {
                    $this->writeParamsFile($docPath, $method);
                }
            }
        }
    }

    private function createDescription($path)
    {
        $servicePath = $this->sourcePath . $this->trim($path);

        $serviceFile = $servicePath . 'Service.yml';
        if (!file_exists($serviceFile)) {
            throw new \RuntimeException("{$serviceFile} does not exist");
        }

        $yamlData = [];

        $paramsFile = $servicePath . 'Params.yml';
        if (file_exists($paramsFile)) {
            $yamlData += $this->getYamlParser()->parse(file_get_contents($paramsFile));
        }

        $yamlData += $this->getYamlParser()->parse(file_get_contents($serviceFile));

        return new ServiceDescription($yamlData);
    }

    private function writeSignatureFile($path, \ReflectionMethod $method, ServiceDescription $description)
    {
        $file = sprintf("%s%s.signature.rst", $this->trim($path), $method->getName());
        $this->getFilesystem()->touch($file);

        $stream = Stream::factory(fopen($file, 'w+'));

        $writer = new Signature($stream, $method, $description);
        $writer->write();

        $stream->close();
    }

    private function writeSampleFile($path, \ReflectionMethod $method)
    {
        $file = sprintf("%s%s.sample.rst", $this->trim($path), $method->getName());
        $this->getFilesystem()->touch($file);
    }

    private function writeParamsFile($path, \ReflectionMethod $method)
    {
        $file = sprintf("%s%s.params.rst", $this->trim($path), $method->getName());
        $this->getFilesystem()->touch($file);
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

    private function trim($string)
    {
        return rtrim($string, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    private function getServiceDocPath($dir)
    {
        return $this->trim($this->destinationPath) . $this->trim($dir) . '_generated/';
    }
}