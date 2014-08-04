<?php

namespace OpenStack\DocGenerator;

use GuzzleHttp\Command\Guzzle\Operation;
use GuzzleHttp\Command\Guzzle\Parameter;
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
        foreach ($this->parameters as $param) {
            $length = strlen($param->getName());
            if ($length > $longestParamNameCount) {
                $longestParamNameCount = $length;
            }
        }

        foreach ($this->parameters as $param) {
            $name = $param->getName();

            $string  = $this->indent() . "'{$name}'";
            $string .= $this->inlineIndent($name, $longestParamNameCount);
            $string .= '=> ';
            $string .= $this->printParamType($param);

            if ($param->getRequired() === true) {
                $string .= ' // required';
            }

            $this->buffer($string);
        }
    }

    private function printParamType(Parameter $parameter)
    {
        $string = "'";

        if ($enum = $parameter->getEnum()) {
            $string .= implode('|', $enum);
        } else {
            $string .= '{' . $parameter->getType() . '}';
        }

        $string .= "',";

        return $string;
    }

    private function indent()
    {
        return str_repeat(' ', 2 + Psr4CodeStyle::INDENT_SPACE_COUNT);
    }

    private function inlineIndent($string, $maxLength)
    {
        $count = $maxLength - strlen($string) + 1;

        return str_repeat(' ', $count > 0 ? $count : 1);
    }

    private function writeClosingVariableBlock()
    {
        $this->buffer(']);', true);
    }
}