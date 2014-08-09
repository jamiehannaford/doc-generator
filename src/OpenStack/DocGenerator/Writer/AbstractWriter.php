<?php

namespace OpenStack\DocGenerator\Writer;

use ReflectionMethod;
use GuzzleHttp\Stream\StreamInterface;
use OpenStack\Common\Rest\ServiceDescription;
use Sami\Parser\DocBlockParser;

abstract class AbstractWriter
{
    private $stream;
    protected $buffer;
    private $docBlockParser;

    protected $method;
    protected $description;

    public function __construct(
        StreamInterface $stream,
        ReflectionMethod $method,
        ServiceDescription $description
    ) {
        $this->stream = $stream;
        $this->method = $method;
        $this->description = $description;
    }

    protected function getParsedDocBlock()
    {
        if (null === $this->docBlockParser) {
            $this->docBlockParser = new DocBlockParser();
        }

        return $this->docBlockParser->parse($this->method->getDocComment());
    }

    public function setDocBlockParser(DocBlockParser $parser)
    {
        $this->docBlockParser = $parser;
    }

    protected function buffer($string, $indent = false, $endOfLine = true)
    {
        if (is_int($indent)) {
            $this->buffer .= str_repeat(' ', $indent);
        }

        $this->buffer .= $string;

        if ($endOfLine === true) {
            $this->buffer .= PHP_EOL;
        }
    }

    protected function writeSectionHeader($title)
    {
        $this->buffer($title);
        $this->buffer(str_repeat('~', strlen($title)));
        $this->buffer('');
    }

    protected function flushBuffer()
    {
        $this->stream->write($this->buffer);
    }

    abstract public function write();
}