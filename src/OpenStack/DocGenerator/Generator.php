<?php

namespace OpenStack\DocGenerator;

use GuzzleHttp\Command\Guzzle\Operation;
use GuzzleHttp\Stream\StreamInterface;

class Generator
{
    private $operation;
    private $stream;
    private $finder;

    private $serviceMapping;

    public function __construct(
        Operation $operation,
        StreamInterface $stream,
        ServiceFinder $finder
    ) {
        $this->operation = $operation;
        $this->stream = $stream;
        $this->finder = $finder;
    }

    public function writeParameterTable()
    {
        $paramMap = $this->finder->findServiceParameters($this->serviceMapping);

        foreach ($paramMap as $docPath => $params) {

        }
    }

    public function setServiceMapping(array $map)
    {
        $this->serviceMapping = $map;
    }

    public function getServiceMapping()
    {
        if (null === $this->serviceMapping) {
            $this->setServiceMapping($this->getDefaultServiceMapping());
        }

        return $this->serviceMapping;
    }

    private function getDefaultServiceMapping()
    {
        return [
            'OpenStack\\ObjectStore\\Service' => 'object-store'
        ];
    }
}