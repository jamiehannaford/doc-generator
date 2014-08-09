<?php

namespace OpenStack\DocGenerator\DocBlock;

class ParamTag
{
    private $type;
    private $name;
    private $description;
    private $isOperationParam = false;
    private $operationName;
    private $operationParamName;

    public function __construct($line)
    {
        if (!preg_match('#^\@param\s(?:(\w+)\s)?\$(\w+)(?:\s(.+))?$#', $line, $matches)) {
            throw new \InvalidArgumentException('Invalid format');
        }

        if (isset($matches[1])) {
            $this->type = $matches[1];
        }

        if (isset($matches[2])) {
            $this->name = $matches[2];
        }

        if (isset($matches[3])) {
            $this->parseDescription($matches[3]);
        }
    }

    private function parseDescription($string)
    {
        if (preg_match('#^\{(\w+)(?:\:\:(\w+))?\}$#', $string, $matches)) {
            $this->isOperationParam = true;
            $this->operationName = $matches[1];
            if (isset($matches[2])) {
                $this->operationParamName = $matches[2];
            }
        } else {
            $this->description = $string;
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function isOperationParam()
    {
        return $this->isOperationParam === true;
    }

    public function getOperationName()
    {
        return $this->operationName;
    }

    public function getOperationParamName()
    {
        return $this->operationParamName;
    }
}