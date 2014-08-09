<?php

namespace OpenStack\DocGenerator\Writer;

class Signature extends AbstractWriter
{
    public function write()
    {
        $this->writeTopLine();
        $this->writeParamsExplanation();

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
            $class = $this->method->getDeclaringClass();
            $className = $class->getNamespaceName()
                ? $class->getNamespaceName() . '\\' . $class->getName()
                : $class->getName();

            throw new \RuntimeException(sprintf(
                "%s::%s() does not have an accompanying docblock",
                $className, $this->method->getName()
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
                    $operation = $this->description->getOperation($tag->getOperationName());
                    $parameter = $operation->getParam($paramName);
                    $type = $parameter->getType();
                    $desc = $parameter->getDescription();
                }
            }

            $string = sprintf(':param %s $%s: %s', $type, $name, $desc);
            $this->buffer($string, 4);
        }
    }
}