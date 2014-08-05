<?php

namespace OpenStack\DocGenerator;

use GuzzleHttp\Command\Guzzle\Description;
use Symfony\Component\Yaml\Yaml;

class ServiceFinder
{
    private $services;
    private $sourceDirectory;

    public function __construct($sourceDirectory, array $map = [])
    {
        $this->sourceDirectory = $sourceDirectory;

        $map = $map ?: $this->getDefaultServiceMapping();
        $this->retrieveDescriptionFiles($map);
    }

    private function getDefaultServiceMapping()
    {
        return [
            'ObjectStore' => 'object-store',
            'Compute'     => 'compute'
        ];
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
                    $this->services[$docDir][] = $file->getPathname();
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

    public function retrieveServiceDescriptions()
    {
        $yamlParser = new Yaml();
        $services = [];

        foreach ($this->services as $docDir => $versionsArray) {
            foreach ($versionsArray as $versionFile) {
                // Make sure version can be found in YAML file path
                preg_match('#Description\/v([\d|\.]+)\.yml$#', $versionFile, $match);
                if (!isset($match[1])) {
                    throw new \RuntimeException('Could not extract version from file');
                }

                // Append YAML file version onto path, e.g. object-store-v2.0
                $docPath = sprintf('%s-v%d', $docDir, $match[1]);

                // Parse YAML array and load into description
                $array = $yamlParser->parse(file_get_contents($versionFile));

                // Ignore empty files or those that cannot be parsed
                if (!is_array($array)) {
                    continue;
                }

                $services[$docPath] = new Description($array);
            }
        }

        return $services;
    }
}