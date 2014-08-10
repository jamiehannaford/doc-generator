<?php

namespace OpenStack\DocGenerator;

trait HasFileHelpersTrait
{
    protected function trim($string)
    {
        return rtrim($string, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    protected function validate($namespace, $docPath, $descPath)
    {
        $this->validateNamespace($namespace);
        $this->validatePath($docPath);
        $this->validatePath($descPath);
    }

    protected function validateNamespace($namespace)
    {
        if (!class_exists($namespace)) {
            throw new \InvalidArgumentException(sprintf(
                'The class %s does not exist', $namespace
            ));
        }
    }

    protected function validatePath($path)
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf(
                'The path %s does not exist', $path
            ));
        }
    }
}