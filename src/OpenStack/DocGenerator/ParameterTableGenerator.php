<?php

namespace OpenStack\DocGenerator;
use Pandoc\Pandoc;

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
    private function writeHeaders()
    {
        $this->buffer('Name|Type|Required|Description');
        $this->buffer('---|---|---|---');
    }

    /**
     * Iterates over each parameter, and writes a CSV representation of
     * pertinent information - these are its name, type, whether its required
     * or not, and its description.
     */
    private function writeParameters()
    {
        foreach ($this->parameters as $param) {

            if ($param->getStatic()) {
                continue;
            }

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
                '%s|%s|%s|%s',
                $param->getName(),
                $type,
                $param->getRequired() ? 'Yes' : 'No',
                trim($param->getDescription())
            );

            $this->buffer($string);
        }
    }

    /**
     * Writes the full content by invoking every method
     */
    public function writeAll()
    {
        $this->writeHeaders();
        $this->writeParameters();

        $pandoc = new Pandoc();
        $rstContent = $pandoc->convert($this->buffer, 'markdown', 'rst');

        $this->buffer = '';

        $this->writeSectionHeader('Parameters');
        $this->buffer(trim($rstContent));

        $this->write();
    }
}
