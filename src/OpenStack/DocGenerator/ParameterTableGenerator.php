<?php

namespace OpenStack\DocGenerator;

use GuzzleHttp\Command\Guzzle\Operation;
use GuzzleHttp\Stream\StreamInterface;

/**
 * Generates a representation of {@see \GuzzleHttp\Command\Guzzle\Parameter}s
 * in order for them to be read by end-users in documentation. The generated
 * content is rendered by Sphinx in its `csv-table' directive, which parses CSV
 * data as a table.
 *
 * @package OpenStack\DocGenerator
 * @link http://docutils.sourceforge.net/docs/ref/rst/directives.html#csv-table
 */
class ParameterTableGenerator
{
    /**
     * @var \GuzzleHttp\Command\Guzzle\Parameter[]
     */
    private $parameters;

    /**
     * @var StreamInterface
     */
    private $stream;

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
    public function writeSectionHeader()
    {
        $string = 'Parameters'  . PHP_EOL . '~~~~~~~~~~' . PHP_EOL;

        $this->output($string);
    }

    /**
     * Writes the Sphinx directive
     */
    public function writeDirective()
    {
        $string = '.. csv-table::';

        $this->output($string);
    }

    /**
     * Writes the titles of the columns
     */
    public function writeTitles()
    {
        $string = ':header: "Name", "Type", "Required", "Description"';

        $this->output($string, true);
    }

    /**
     * Writes the widths of the columns
     */
    public function writeWidths()
    {
        $string = ':widths: 20, 20, 10, 50' . PHP_EOL;

        $this->output($string, true);
    }

    /**
     * Iterates over each parameter, and writes a CSV representation of
     * pertinent information - these are its name, type, whether its required
     * or not, and its description.
     */
    public function writeParameters()
    {
        foreach ($this->parameters as $param) {

            $type = $param->getType();
            if (is_array($type)) {
                $type = implode('|', $type);
            }

            if ($enum = $param->getEnum()) {
                array_walk($enum, function(&$val) {
                    $val = "'{$val}'";
                });
                $type = implode(",", $enum);
            }

            $string = sprintf(
                '"%s", "%s", "%s", "%s"',
                $param->getName(),
                $type,
                $param->getRequired() ? 'Yes' : 'No',
                $param->getDescription()
            );

            $this->output($string, true);
        }
    }

    private function output($string, $indent = false)
    {
        if ($indent) {
            $string = '  ' . $string;
        }

        $this->stream->write($string . PHP_EOL);
    }

    /**
     * Writes the full content by invoking every method
     */
    public function writeAll()
    {
        $this->writeSectionHeader();
        $this->writeDirective();
        $this->writeTitles();
        $this->writeWidths();
        $this->writeParameters();
    }
}