<?php

namespace OpenStack\DocGenerator\DocBlock;

class ReturnTag
{
    private $type;
    private $operation;

    public function __construct($string)
    {
        if (!preg_match('#^\@return#', $string)) {
            throw new \InvalidArgumentException('Invalid format');
        }

        $string = str_replace('@return ', '', $string);

        if (preg_match('#^\{(\w+)\}$#', $string, $matches)) {
            $this->operation = $matches[1];
        } else {
            $this->type = $string;
        }
    }

    public function getType()
    {
        return $this->type;
    }

    public function getOperation()
    {
        return $this->operation;
    }
}