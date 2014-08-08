<?php

namespace OpenStack\DocGenerator;

use OpenStack\Common\Rest\ServiceDescription;
use Symfony\Component\Filesystem\Filesystem;
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
        return $this->filesystem;
    }

    public function setFilesystem(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    private function getYamlParser()
    {
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
            $this->getFilesystem()->remove($service['docPath']);
            $this->getFilesystem()->mkdir($service['docPath']);

            $description = $this->createDescription($service['descPath']);

            $reflection = new \ReflectionClass($service['namespace']);
            foreach ($reflection->getMethods() as $method) {
                // Create a signature, params table + code sample file
                $this->writeSignatureFile($docPath, $method, $description);
                $this->writeSampleFile($docPath, $method);
                $this->writeParamsFile($docPath, $method);
            }
        }
    }

    private function createDescription($path)
    {
        $serviceFile = $this->trim($path) . 'Service.yml';
        if (!file_exists($serviceFile)) {
            throw new \RuntimeException("{$serviceFile} does not exist");
        }

        $yamlData = '';

        $paramsFile = $this->trim($path) . 'Params.yml';
        if (file_exists($paramsFile)) {
            $yamlData .= $this->getYamlParser()->parse(file_get_contents($paramsFile));
        }

        $yamlData .= $this->getYamlParser()->parse(file_get_contents($serviceFile));

        return new ServiceDescription($yamlData);
    }

    private function writeSignatureFile($path, \ReflectionMethod $method, ServiceDescription $description)
    {
        $file = sprintf("%s%s.signature.rst", $this->trim($path), $method->getName());
        $this->getFilesystem()->touch($file);


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

    private function trim($string)
    {
        return rtrim($string, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    private function getServiceDocPath($dir)
    {
        return $this->trim($this->destinationPath) . $this->trim($dir) . '_generated/';
    }
}