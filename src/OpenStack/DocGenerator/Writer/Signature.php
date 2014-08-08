<?php

namespace OpenStack\DocGenerator\Writer;

use GuzzleHttp\Stream\StreamInterface;
use OpenStack\Common\Rest\ServiceDescription;
use Sami\Parser\DocBlockParser;

class Signature
{
    private $stream;
    private $method;
    private $description;
    private $docBlockParser;

    private $buffer;

    public function __construct(
        StreamInterface $stream,
        \ReflectionMethod $method,
        ServiceDescription $description
    ) {
        $this->stream = $stream;
        $this->method = $method;
        $this->description = $description;
    }

    private function getDocBlockParser()
    {
        if (null === $this->docBlockParser) {
            $this->docBlockParser = new DocBlockParser();
        }

        return $this->docBlockParser;
    }

    public function setDocBlockParser(DocBlockParser $parser)
    {
        $this->docBlockParser = $parser;
    }

    private function buffer($string, $indent = false)
    {
        if (is_int($indent)) {
            $this->buffer .= str_repeat(' ', $indent);
        }

        $this->buffer .= $string . PHP_EOL;
    }

    public function write()
    {
        $this->writeTopLine();
        $this->writeParamsExplanation();

        $this->stream->write(trim($this->buffer));
    }

    private function writeTopLine()
    {
        $string = $this->writeDirective()
            . $this->writeMethodName()
            . $this->writeParamBlock();

        $this->buffer($string . PHP_EOL);
    }

    private function writeDirective()
    {
        return '.. method:: ';
    }

    private function writeMethodName()
    {
        return $this->method->getName();
    }

    private function writeParamBlock()
    {
        $params = [];

        foreach ($this->method->getParameters() as $param) {

            $string = '';

            if ($param->isArray()) {
                $string .= 'array ';
            } elseif ($param->isCallable()) {
                $string .= 'callable ';
            } elseif ($typehint = $param->getClass()) {
                $string .= $typehint->getName() . ' ';
            }

            if ($param->isPassedByReference()) {
                $string .= '&';
            }

            $string .= '$' . $param->getName();

            if ($param->isDefaultValueAvailable() && !$param->getClass()) {

                $defaultValue = $param->isDefaultValueConstant()
                    ? $param->getDefaultValueConstantName()
                    : $param->getDefaultValue();

                if (is_array($defaultValue)) {
                    $defaultValue = '[]';
                } elseif ($defaultValue === null) {
                    $defaultValue = 'null';
                } elseif ($defaultValue === true) {
                    $defaultValue = 'true';
                } elseif ($defaultValue === false) {
                    $defaultValue = 'false';
                }

                $string .= ' = ' . $defaultValue;
            }

            $params[] = $string;
        }

        return '(' . implode(', ', $params) . ')';
    }

    private function writeParamsExplanation()
    {
        /** @var \Sami\Parser\Node\DocBlockNode $docComment */
        $docComment = $this->getDocBlockParser()->parse(
            $this->method->getDocComment()
        );

        // Go through each tag and find the operation annotations
        foreach ($docComment->getTag('param') as $paramTag) {
            $name = $paramTag[1];
            $type = $paramTag[0][0][0];
            $desc = $paramTag[2];

            if (preg_match('#^\{(\w+)(?:\:\:(\w+))?\}$#', $desc, $matches)) {
                $operation = $this->description->getOperation($matches[1]);

                // Because `$name {Operation::Param}` annotations are slightly
                // different from `$name string Desc`, we need to reshuffle
                $name = str_replace('$', '', $type);

                // Handle ::ParamName
                if (isset($matches[2])) {
                    $operationParam = $operation->getParam($matches[2]);
                    $type = $operationParam->getType();
                    $desc = $operationParam->getDescription();
                } elseif ($name == 'options') {
                    $type = 'array';
                    $desc = 'See Additional Parameters table';
                }
            }

            $string = sprintf(':param %s $%s: %s', $type, $name, $desc);
            $this->buffer($string, 4);
        }
    }
}