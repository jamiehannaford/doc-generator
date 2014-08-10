<?php

namespace OpenStack\DocGenerator;

class BatchGenerator
{
    private $sourcePath;
    private $destinationPath;
    private $retriever;

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

    public function buildDocs()
    {
        $services = $this->getRetriever()->retrieve();

        if (empty($services)) {
            return;
        }

        foreach ($services as $service) {
            $serviceGenerator = new ServiceGenerator(
                $service['namespace'],
                $this->destinationPath . DIRECTORY_SEPARATOR . $service['docPath'],
                $this->sourcePath . DIRECTORY_SEPARATOR . $service['descPath']
            );
            $serviceGenerator->buildDocs();
        }
    }
}