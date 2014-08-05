<?php

namespace OpenStack\DocGenerator;

use GuzzleHttp\Command\Guzzle\Description;
use GuzzleHttp\Command\Guzzle\Operation;
use GuzzleHttp\Stream\Stream;

class Generator
{
    private $finder;
    private $destinationDir;
    private $sourceDir;

    public function __construct(
        $sourceDir = null,
        $destinationDir = null,
        ServiceFinder $finder = null
    ) {
        $this->sourceDir = $sourceDir ?: __DIR__ . '/src/OpenStack/';
        $this->destinationDir = $destinationDir ?: __DIR__ . '/doc/_build';
        $this->finder = $finder ?: new ServiceFinder($this->sourceDir);
    }

    public function getDestinationDir()
    {
        return $this->destinationDir;
    }

    public function writeFiles()
    {
        $map = $this->finder->retrieveServiceDescriptions();

        foreach ($map as $serviceVersion => $description) {
            $prefix = $this->getServicePath($serviceVersion);

            foreach ($description->getOperations() as $name => $operationData) {
                $this->ensureDirectoryExists($prefix);

                $operation = new Operation(['name' => $name] + $operationData, $description);

                $this->writeParamsTable($operation, $prefix);
                $this->writeCodeSample($operation, $prefix);
            }
        }
    }

    private function ensureDirectoryExists($path)
    {
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
    }

    private function writeParamsTable(Operation $operation, $baseDir)
    {
        $name   = $operation->getName();
        $path   = $baseDir . $name . '.params.rst';
        $stream = Stream::factory(fopen($path, 'w+'));

        $generator = new ParameterTableGenerator($operation, $stream);
        $generator->writeAll();

        $stream->close();
    }

    private function writeCodeSample(Operation $operation, $baseDir)
    {
        $name   = $operation->getName();
        $path   = $baseDir . $name . '.sample.rst';
        $stream = Stream::factory(fopen($path, 'w+'));

        $generator = new CodeSampleGenerator($operation, $stream);
        $generator->writeAll();

        $stream->close();
    }

    private function getServicePath($serviceDir)
    {
        return $this->appendSeparator($this->destinationDir)
            . $this->appendSeparator($serviceDir)
            . $this->appendSeparator('_generated');
    }

    private function appendSeparator($path)
    {
        return rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }
}