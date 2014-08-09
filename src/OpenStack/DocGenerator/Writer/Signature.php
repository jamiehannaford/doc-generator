<?php

namespace OpenStack\DocGenerator\Writer;

use OpenStack\Common\Rest\Parameter;

class Signature extends AbstractWriter
{
    public function write()
    {
        $this->writeTopLine();
        $this->writeParamsExplanation();
        $this->writeReturnType();
        $this->writeRaisesExceptions();

        $this->flushBuffer();
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
        $paramTags = $this->getDocBlock()->getParamTags();

        if (empty($paramTags)) {
            throw new \RuntimeException(sprintf(
                "%s does not have an accompanying docblock",
                $this->getFullMethodName()
            ));
        }

        // Go through each tag and find the operation annotations
        foreach ($paramTags as $tag) {
            $name = $tag->getName();
            $type = $tag->getType();
            $desc = $tag->getDescription();

            if ($tag->isOperationParam()) {
                if ($name == 'options') {
                    $type = 'array';
                    $desc = 'See Additional Parameters table';
                } elseif ($paramName = $tag->getOperationParamName()) {
                    $operationName = $tag->getOperationName();
                    $operation = $this->description->getOperation($operationName);
                    $parameter = $operation->getParam($paramName);

                    if (!($parameter instanceof Parameter)) {
                        throw new \RuntimeException(sprintf(
                            "%s referenced {%s::%s}, which is not defined",
                            $this->getFullMethodName(), $operationName, $paramName
                        ));
                    }

                    $type = $parameter->getType();
                    $desc = $parameter->getDescription();
                }
            }

            if (is_array($type)) {
                $type = implode('|', $type);
            }

            $string = sprintf(':param %s $%s: %s', $type, $name, $desc);
            $this->buffer($string, 4);
        }
    }

    private function writeReturnType()
    {
        if (!($returnTag = $this->getDocBlock()->getReturnTag())) {
            throw new \InvalidArgumentException(sprintf(
                'No @return tag for %s', $this->getFullMethodName()
            ));
        }

        $type = $returnTag->getType();
        $desc = $returnTag->getDescription();

        if ($operationName = $returnTag->getOperation()) {
            if (!($operation = $this->description->getOperation($operationName))) {
                throw new \InvalidArgumentException(sprintf(
                    '%s is not a valid operation', $operationName
                ));
            }

            if ($operation->getIteratorName()) {
                $type = 'OpenStack\\Common\\Iterator\\ResourceIterator';
                $desc = 'a resource iterator';
            } else {
                $type = 'OpenStack\\Common\\Model\\ModelInterface';
                $desc = 'an array-like model object (like a read-only struct) that implements \ArrayAccess';
            }
        }

        $this->buffer(':return: ' . $desc, 4);
        $this->buffer(':rtype: ' . $type, 4);
    }

    private function writeRaisesExceptions()
    {
        $string = ':raises CommandException: If a HTTP or network connection error occurs';
        $this->buffer($string, 4, false);
    }
}