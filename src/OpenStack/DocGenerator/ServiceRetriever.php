<?php

namespace OpenStack\DocGenerator;

class ServiceRetriever
{
    use HasFileHelpersTrait;

    private $sourcePath;

    public function __construct($sourcePath)
    {
        $this->validatePath($sourcePath);
        $this->sourcePath = $sourcePath;
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
                    $services[] = [
                        'docPath'   => self::getDocPath($service, $version),
                        'namespace' => $class,
                        'descPath'  => $this->getDescPath($service, $version)
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

    private function getDescPath($service, $version)
    {
        return $service . DIRECTORY_SEPARATOR . $version . DIRECTORY_SEPARATOR . 'Description';
    }

    public static function getDocPath($service, $version)
    {
        $dir = strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $service));
        return $dir . '-' . $version;
    }
}