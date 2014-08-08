<?php

namespace OpenStack\DocGenerator\Writer;

use Pandoc\Pandoc;

class ParamsTable extends AbstractWriter
{
    public function write()
    {
        $this->writeSectionHeader('Additional Parameters');
        $this->writeTitles();
        $this->writeParamTable();
var_dump($this->buffer);die;
        $this->flushBuffer();
    }

    private function writeTitles()
    {
        $this->buffer('Name|Type|Required|Description');
    }

    private function writeParamTable()
    {
        $methodParams = [];
        foreach ($this->method->getParameters() as $param) {
            $methodParams[$param->getName()] = true;
        }

        if (!isset($methodParams['options'])) {
            return;
        }

        // @TODO create bespoke DocBlock class or something
        $docBlock = $this->getParsedDocBlock();
        foreach ($docBlock->getTag('param') as $param) {
            if ($param[0][0][0] == '$options') {
                $operationName = str_replace(['{', '}'], '', $param[2]);
            }
        }

        if (!isset($operationName)
            || !($operation = $this->description->getOperation($operationName))
        ) {
            return;
        }

        $rowData = '';

        foreach ($operation->getParams() as $param) {
            $name = $param->getName();

            if (!isset($methodParams[$name]) || $param->getStatic()) {
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

            $rowData .= sprintf(
                '%s|%s|%s|%s',
                $name,
                $type,
                $param->getRequired() ? 'Yes' : 'No',
                wordwrap($param->getDescription(), 50, '\\'.PHP_EOL)
            );
        }

        $content = (new Pandoc())->convert($rowData, 'markdown', 'rst');

        $this->buffer($content);
    }
}