<?php

namespace OpenStack\DocGenerator\DocBlock;

class ReturnTag
{
    private $type;
    private $operation;

    public function __construct($line)
    {
        if (!preg_match('#^\@return#', $line)) {
            throw new \InvalidArgumentException(sprintf(
                '"%s" is not a valid return tag format', $line
            ));
        }

        $string = str_replace('@return ', '', $line);

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