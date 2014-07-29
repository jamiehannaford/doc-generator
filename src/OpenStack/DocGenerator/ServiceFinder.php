<?php

namespace OpenStack\DocGenerator;

class ServiceFinder
{
    private $services;
    private $sourceDirectory;

    public function __construct($sourceDirectory, array $map)
    {
        $this->sourceDirectory = $sourceDirectory;
        $this->retrieveDescriptionFiles($map);
    }

    private function retrieveDescriptionFiles(array $map)
    {
        foreach ($map as $srcDir => $docDir) {
            $path = $this->getDescPath($srcDir);

            if (!file_exists($path)) {
                throw new \RuntimeException(sprintf("%s does not exist", $path));
            }

            $dirIterator = new \DirectoryIterator($path);

            foreach ($dirIterator as $file) {
                if ($file->getExtension() == 'yml') {
                    $this->services[$docDir][] = $file->getPath();
                }
            }
        }
    }

    private function getDescPath($serviceDir)
    {
        return rtrim($this->sourceDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
                . $serviceDir . DIRECTORY_SEPARATOR
                . 'Description';
    }

    public function getServices()
    {
        return $this->services;
    }
}