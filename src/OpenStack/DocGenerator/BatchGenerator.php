<?php

namespace OpenStack\DocGenerator;

use GuzzleHttp\Stream\Stream;
use OpenStack\Common\Rest\ServiceDescription;
use OpenStack\DocGenerator\Writer\Signature;

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


            $description = $this->createDescription($service['descPath']);

            $reflection = new \ReflectionClass($service['namespace']);
            foreach ($reflection->getMethods() as $method) {


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

    private function writeSignatureFile($path, \ReflectionMethod $method, ServiceDescription $description)
    {
        $file = sprintf("%s%s.signature.rst", $this->trim($path), $method->getName());
        $this->filesystem->touch($file);

        $stream = Stream::factory(fopen($file, 'w+'));

        $writer = new Signature($stream, $method, $description);
        $writer->write();

        $stream->close();
    }

    private function writeSampleFile($path, \ReflectionMethod $method)
    {
        $file = sprintf("%s%s.sample.rst", $this->trim($path), $method->getName());
        $this->filesystem->touch($file);
    }

    private function writeParamsFile($path, \ReflectionMethod $method)
    {
        $file = sprintf("%s%s.params.rst", $this->trim($path), $method->getName());
        $this->filesystem->touch($file);
    }



    private function getServiceDocPath($dir)
    {
        return $this->trim($this->destinationPath) . $this->trim($dir) . '_generated/';
    }
}