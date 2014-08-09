<?php

namespace OpenStack\DocGenerator\DocBlock;

class ReturnTag
{
    private $type;
    private $operation;
    private $description;

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
        } elseif (preg_match('#(\w+)\s+(\w+)#', $string, $matches)) {
            $this->type = $matches[1];
            if ($matches[2]) {
                $this->description = $matches[2];
            }
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

    public function getDescription()
    {
        return $this->description;
    }
}