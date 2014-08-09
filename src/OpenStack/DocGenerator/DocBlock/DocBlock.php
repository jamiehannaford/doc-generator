<?php

namespace OpenStack\DocGenerator\DocBlock;

class DocBlock
{
    private $params;
    private $returnTag;

    public function __construct($string)
    {
        $this->parseTags($this->cleanInput($string));
    }

    private function cleanInput($string)
    {
        $comment = trim(preg_replace(
            '#[ \t]*(?:\/\*\*|\*\/|\*)?[ \t]{0,1}(.*)?#u', '$1', $string
        ));

        // remove */ from a single line docblock
        if (substr($comment, -2) == '*/') {
            $comment = trim(substr($comment, 0, -2));
        }

        // normalize strings
        return str_replace(array("\r\n", "\r"), "\n", $comment);
    }

    private function parseTags($tags)
    {
        $lines = explode(PHP_EOL, $tags);

        foreach ($lines as $line) {
            if (preg_match('#^\@param#', $line)) {
                $paramTag = new ParamTag($line);
                $this->params[$paramTag->getName()] = $paramTag;
            } elseif (preg_match('#^\@return\s\{(\w+)\}$#', $line)) {
                $this->returnTag = new ReturnTag($line);
            }
        }
    }

    public function getParamTags()
    {
        return $this->params;
    }

    public function getParamTag($name)
    {
        foreach ($this->params as $param) {
            if ($param->getName() == $name) {
                return $param;
            }
        }

        return null;
    }

    public function getReturnTag()
    {
        return $this->returnTag;
    }
}