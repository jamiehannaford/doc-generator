<?php

namespace OpenStack\DocGenerator\Writer;

use OpenStack\DocGenerator\DocBlock\DocBlock;
use ReflectionMethod;
use GuzzleHttp\Stream\StreamInterface;
use OpenStack\Common\Rest\ServiceDescription;

abstract class AbstractWriter
{
    private $stream;
    protected $buffer;
    private $docBlock;

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

    protected function getDocBlock()
    {
        if (null === $this->docBlock) {
            $this->docBlock = new DocBlock($this->method->getDocComment());
        }

        return $this->docBlock;
    }

    public function setDocBlock(DocBlock $docBlock)
    {
        $this->docBlock = $docBlock;
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

    protected function getFullMethodName()
    {
        $class = $this->method->getDeclaringClass();
        $className = $class->getNamespaceName()
            ? $class->getNamespaceName() . '\\' . $class->getName()
            : $class->getName();

        return sprintf("%s::%s()", $className, $this->method->getName());
    }

    abstract public function write();
}