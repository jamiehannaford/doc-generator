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
        $this->writeDirective('code-block', 'php', true);

        $isIterator = $this->operation->getData('iterator') !== null;

        $this->writeOpeningVariableBlock($isIterator);
        $this->writeParametersBlock();
        $this->writeClosingVariableBlock();

        if ($isIterator) {
            $this->writeForeachLoop();
        } else {
            $this->writeModelElements();
        }

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
            $type = $parameter->getType();
            if (is_array($type)) {
                $type = implode('|', $type);
            }
            $string .= '{' . $type . '}';
        }

        $string .= "',";

        return $string;
    }

    private function indent($offset = 2)
    {
        return str_repeat(' ', $offset + Psr4CodeStyle::INDENT_SPACE_COUNT);
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

    private function writeForeachLoop()
    {
        $this->buffer('');
        $this->buffer('foreach ($iterator as $resource) {', true);

        $properties = $this->operation->getData('iterator')['modelSchema']['properties'];

        foreach ($properties as $name => $array) {
            $string = $this->indent(0) . 'echo $resource[\'' . $name . '\'];';
            $this->buffer($string, true);
        }

        $this->buffer('}', true);
    }

    private function writeModelElements()
    {
        $this->buffer('');

        $modelName = $this->operation->getResponseModel();
        $responseModel = $this->operation->getServiceDescription()->getModel($modelName);

        foreach ($responseModel->getProperties() as $name => $param) {
            $var  = ' $response[\'' . $name . '\'];';

            if ($param->getType() == 'array') {
                $string = '$' . lcfirst($name) . ' =' . $var . ' // Array';
            } else {
                $string = 'echo' . $var;
            }

            $this->buffer($string, true);
        }
    }
}
