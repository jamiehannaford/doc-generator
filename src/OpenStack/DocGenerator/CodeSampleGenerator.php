<?php

namespace OpenStack\DocGenerator;

use GuzzleHttp\Command\Guzzle\Operation;
use GuzzleHttp\Stream\StreamInterface;

class CodeSampleGenerator extends AbstractGenerator
{
    private $operation;

    public function __construct(Operation $operation, StreamInterface $stream)
    {
        parent::__construct($operation, $stream);

        $this->operation = $operation;
    }

    public function writeAll()
    {
        $this->writeSectionHeader('Sample code');
        $this->writeDirective('code-block', 'php');

        $isIterator = $this->operation->getData('iterator') !== null;

        $this->writeOpeningVariableBlock($isIterator);
        $this->writeParametersBlock();
        $this->writeClosingVariableBlock();

        $this->write();
    }

    private function writeOpeningVariableBlock($isIterator = false)
    {
        $variableName = ($isIterator) ? 'iterator' : 'response';

        $string = '$' . $variableName . ' = $service->'
            . lcfirst($this->operation->getName()) . '([';

        $this->buffer($string, true);
    }

    private function writeParametersBlock()
    {
        // Find the longest parameter name count
        $longestParamNameCount = 0;
        foreach ($this->parameters as $name => $param) {
            $length = strlen($name);
            if ($length > $longestParamNameCount) {
                $longestParamNameCount = $length;
            }
        }

        foreach ($this->parameters as $param) {
            $name = $param->getName();

            $string  = $this->indent() . "'{$name}'";
            $string .= $this->inlineIndent($name, $longestParamNameCount);
            $string .= "=> '{" . $param->getType() . "}',";
            if ($param->getRequired() === true) {
                $string .= ' // required';
            }

            $this->buffer($string);
        }
    }

    private function indent()
    {
        return str_repeat(' ', Psr4CodeStyle::INDENT_SPACE_COUNT);
    }

    private function inlineIndent($string, $maxLength)
    {
        $count = $maxLength - strlen($string);

        return str_repeat(' ', $count > 0 ? $count : 1);
    }

    private function writeClosingVariableBlock()
    {
        $this->buffer(']);', true);
    }
}