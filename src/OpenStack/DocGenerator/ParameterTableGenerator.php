<?php

namespace OpenStack\DocGenerator;

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
     * @param \GuzzleHttp\Command\Guzzle\Parameter[] $parameters
     * @param StreamInterface                        $stream
     */
    public function __construct(array $parameters, StreamInterface $stream)
    {
        $this->parameters = $parameters;
        $this->stream = $stream;
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
        $string = "Parameters\n~~~~~~~~~~";

        $this->stream->write($string);
    }

    /**
     * Writes the Sphinx directive
     */
    public function writeDirective()
    {
        $string = '.. csv-table::';

        $this->stream->write($string);
    }

    /**
     * Writes the titles of the columns
     */
    public function writeTitles()
    {
        $string = ':header: "Name", "Type", "Required", "Description"';

        $this->stream->write($string);
    }

    /**
     * Writes the widths of the columns
     */
    public function writeWidths()
    {
        $string = ':widths: 20, 20, 10, 50';

        $this->stream->write($string);
    }

    /**
     * Iterates over each parameter, and writes a CSV representation of
     * pertinent information - these are its name, type, whether its required
     * or not, and its description.
     */
    public function writeParameters()
    {
        foreach ($this->parameters as $parameter) {
            $string = sprintf(
                '"%s","%s","%s","%s"',
                $parameter->getName(),
                $parameter->getType(),
                $parameter->getRequired() ? 'Yes' : 'No',
                $parameter->getDescription()
            );

            $this->stream->write($string);
        }
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