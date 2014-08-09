<?php

namespace OpenStack\DocGenerator\Writer;

use OpenStack\Common\Rest\Operation;
use Pandoc\Pandoc;

class ParamsTable extends AbstractWriter
{
    public function write()
    {
        $this->writeSectionHeader('Additional Parameters');
        $this->writeTitles();
        $this->writeParamTable();

        $this->flushBuffer();
    }

    private function writeTitles()
    {
        return 'Name|Type|Required|Description' . PHP_EOL . '---|---|---|---' . PHP_EOL;
    }

    private function writeParamTable()
    {
        $content = $this->writeTitles();

        if ($operationParamTag = $this->getDocBlock()->getParamTag('options')) {
            $operation = $this->description->getOperation(
                $operationParamTag->getOperationName()
            );
        }

        if (!isset($operation) || !($operation instanceof Operation)) {
            return;
        }

        foreach ($operation->getParams() as $param) {
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

            $content .= sprintf(
                '%s|%s|%s|%s',
                $param->getName(),
                $type,
                $param->getRequired() ? 'Yes' : 'No',
                wordwrap($param->getDescription(), 50, '\\'.PHP_EOL)
            ) . PHP_EOL;
        }

        $rstContent = (new Pandoc())->convert($content, 'markdown', 'rst');

        $this->buffer(trim($rstContent), false, false);
    }
}