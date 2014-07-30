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
        ServiceFinder $finder = null,
        $sourceDir = null,
        $destinationDir = null
    ) {
        $this->destinationDir = $destinationDir ?: __DIR__ . '/doc/_build';
        $this->sourceDir = $sourceDir ?: __DIR__ . '/src/OpenStack/';

        $this->finder = $finder ?: new ServiceFinder($this->sourceDir);
    }

    public function getDestinationDir()
    {
        return $this->destinationDir;
    }

    public function writeFiles()
    {
        $map = $this->finder->retrieveServiceParameters();

        foreach ($map as $serviceVersion => $operations) {
            $prefix = $this->getServicePath($serviceVersion);
            $description = new Description(['operations' => $operations]);

            foreach ($operations as $name => $operationArray) {
                if (!file_exists($prefix)) {
                    mkdir($prefix, 0755, true);
                }

                $operation = new Operation(['name' => $name] + $operationArray, $description);
                $this->writeParamsTable($operation, $prefix);
            }
        }
    }

    private function writeParamsTable(Operation $operation, $prefix)
    {
        $name = $operation->getName();

        $path = $prefix . $name . '.params.rst';
        $stream = Stream::factory(fopen($path, 'w+'));

        $generator = new ParameterTableGenerator($operation, $stream);
        $generator->writeAll();

        $stream->close();
    }

    private function getServicePath($serviceDir)
    {
        return rtrim($this->destinationDir, '/') . DIRECTORY_SEPARATOR
                . rtrim($serviceDir, '/') . DIRECTORY_SEPARATOR
                . '_generated' . DIRECTORY_SEPARATOR;
    }
}