<?php

namespace OpenStack\DocGenerator\Writer;

use GuzzleHttp\Stream\Stream;
use OpenStack\Common\Rest\ServiceDescription;
use OpenStack\DocGenerator\HasFileHelpersTrait;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Parser;

class WriterFactory
{
    use HasFileHelpersTrait;

    private $namespace;
    private $docPath;
    private $descPath;
    private $yamlParser;
    private $filesystem;

    public function __construct(
        $namespace,
        $docPath,
        $descPath,
        Parser $parser = null,
        Filesystem $filesystem = null
    ) {
        //$this->validate($namespace, $docPath, $descPath);

        $this->namespace = $namespace;
        $this->docPath   = $this->trim($docPath);
        $this->descPath  = $this->trim($descPath);

        $this->yamlParser = $parser ?: new Parser();
        $this->filesystem = $filesystem ?: new Filesystem();
    }

    private function parseYamlPath($path, &$array)
    {
        if (file_exists($path)) {
            $data = $this->yamlParser->parse(file_get_contents($path));
            if (is_array($data)) {
                $array = array_merge($array, $data);
            } elseif (!$data) {
                throw new \RuntimeException("{$path} could not be parsed");
            }
        }
    }

    private function createDescription()
    {
        $serviceFile = $this->descPath . 'Service.yml';
        $paramsFile  = $this->descPath . 'Params.yml';
        $this->validatePath($serviceFile);

        $data = [];
        $this->parseYamlPath($paramsFile, $data);
        $this->parseYamlPath($serviceFile, $data);

        return new ServiceDescription($data);
    }

    private function createStream($file)
    {
        $path = $this->docPath . '_generated' . DIRECTORY_SEPARATOR . $file;

        if (!file_exists($path)) {
            $this->filesystem->touch($path);
        }

        return Stream::factory(fopen($path, 'w+'));
    }

    public function create($writerType, $file, \ReflectionMethod $method)
    {
        $className = sprintf('%s\\%s', __NAMESPACE__, ucfirst($writerType));
        $this->validateNamespace($className);

        return new $className(
            $this->createStream($file),
            $method,
            $this->createDescription()
        );
    }
}