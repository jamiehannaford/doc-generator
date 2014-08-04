<?php

namespace OpenStack\DocGenerator;

use GuzzleHttp\Command\Guzzle\Operation;
use GuzzleHttp\Stream\StreamInterface;

abstract class AbstractGenerator
{
    /**
     * @var \GuzzleHttp\Command\Guzzle\Parameter[]
     */
    protected $parameters;

    /**
     * @var StreamInterface
     */
    private $stream;

    /**
     * @var string
     */
    protected $buffer = '';

    /**
     * @param Operation       $operation
     * @param StreamInterface $stream
     */
    public function __construct(Operation $operation, StreamInterface $stream)
    {
        $this->parameters = $operation->getParams();
        $this->stream     = $stream;
    }

    /**
     * @return StreamInterface
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * Writes section header
     */
    public function writeSectionHeader($title)
    {
        $string = $title  . PHP_EOL . str_repeat('~', strlen($title)) . PHP_EOL;

        $this->buffer($string);
    }

    /**
     * Writes the Sphinx directive
     */
    public function writeDirective($name, $suffix = false)
    {
        $string = '.. ' . $name . '::';

        if ($suffix) {
            $string .= ' ' . $suffix;
        }

        $string .= PHP_EOL;

        $this->buffer($string);
    }

    protected function buffer($string, $indent = false)
    {
        if ($indent) {
            $string = '  ' . $string;
        }

        $this->buffer .= $string . PHP_EOL;
    }

    protected function write()
    {
        $this->stream->write($this->buffer);
    }
}