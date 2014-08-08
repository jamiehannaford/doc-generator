<?php

namespace OpenStack\DocGenerator\Writer;

use Sami\Parser\DocBlockParser;

abstract class AbstractWriter
{
    protected $stream;
    protected $buffer;
    private $docBlockParser;

    private function getDocBlockParser()
    {
        if (null === $this->docBlockParser) {
            $this->docBlockParser = new DocBlockParser();
        }

        return $this->docBlockParser;
    }

    public function setDocBlockParser(DocBlockParser $parser)
    {
        $this->docBlockParser = $parser;
    }

    private function buffer($string, $indent = false)
    {
        if (is_int($indent)) {
            $this->buffer .= str_repeat(' ', $indent);
        }

        $this->buffer .= $string . PHP_EOL;
    }
}