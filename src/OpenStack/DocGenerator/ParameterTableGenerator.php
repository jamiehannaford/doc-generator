<?php

namespace OpenStack\DocGenerator;

/**
 * Generates a representation of {@see \GuzzleHttp\Command\Guzzle\Parameter}s
 * in order for them to be read by end-users in documentation. The generated
 * content is rendered by Sphinx in its `csv-table' directive, which parses CSV
 * data as a table.
 *
 * @package OpenStack\DocGenerator
 * @link http://docutils.sourceforge.net/docs/ref/rst/directives.html#csv-table
 */
class ParameterTableGenerator extends AbstractGenerator
{
    /**
     * Writes the titles of the columns
     */
    private function writeTitles()
    {
        $string = ':header: "Name", "Type", "Required", "Description"';

        $this->buffer($string, true);
    }

    /**
     * Writes the widths of the columns
     */
    private function writeWidths()
    {
        $string = ':widths: 20, 20, 10, 50' . PHP_EOL;

        $this->buffer($string, true);
    }

    /**
     * Iterates over each parameter, and writes a CSV representation of
     * pertinent information - these are its name, type, whether its required
     * or not, and its description.
     */
    private function writeParameters()
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

            $this->buffer($string, true);
        }
    }

    /**
     * Writes the full content by invoking every method
     */
    public function writeAll()
    {
        $this->writeSectionHeader('Parameters');
        $this->writeDirective('csv-table');
        $this->writeTitles();
        $this->writeWidths();
        $this->writeParameters();

        $this->write();
    }
}