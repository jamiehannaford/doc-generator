<?php

namespace OpenStack\DocGenerator;

class ServiceRetriever
{
    private $sourcePath;

    public function __construct($sourcePath)
    {
        $this->sourcePath = $sourcePath;

        if (!file_exists($this->sourcePath)) {
            throw new \RuntimeException(sprintf('%s does not exist', $this->sourcePath));
        }
    }

    public function retrieve()
    {
        $iterator = new \DirectoryIterator($this->sourcePath);
        $services = [];

        foreach ($iterator as $service) {
            if (!$service->isDir() || $service->isDot()) {
                continue;
            }

            $subIterator = new \DirectoryIterator($service->getPathname());
            foreach ($subIterator as $version) {
                $class = sprintf("OpenStack\\%s\\%s\\Service", $service, $version);
                if (class_exists($class)) {
                    $descPath = $version->getPathname() . DIRECTORY_SEPARATOR . 'Description';
                    $services[] = [
                        'docPath'   => $this->getDocPath($service, $version),
                        'namespace' => $class,
                        'descPath'  => $descPath
                    ];
                }
            }
        }

        if (empty($services)) {
            throw new \RuntimeException(sprintf(
                'No services could be retrieved from in %s', $this->sourcePath
            ));
        }

        return $services;
    }

    private function getDocPath($service, $version)
    {
        return $this->getHyphenatedServiceName($service) . '-' . $version;
    }

    private function getHyphenatedServiceName($service)
    {
        return strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $service));
    }
}
