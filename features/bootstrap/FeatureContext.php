<?php

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use GuzzleHttp\Command\Guzzle\Operation;
use GuzzleHttp\Command\Guzzle\Description;
use Behat\Behat\Tester\Exception\PendingException;

class FeatureContext implements SnippetAcceptingContext
{
    private $operation;
    private $destinationDir;

    /**
     * @Given a :name operation has these parameters:
     */
    public function anOperationHasTheseParameters($name, TableNode $table)
    {
        $params = [];
        foreach ($table as $param) {
            $name = $param['name'];
            unset($param['name']);

            $params[$name] = $param;
        }

        $config = ['name' => $name, 'parameters' => $params];

        $description = new Description([]);
        $this->operation = new Operation($config, $description);
    }

    /**
     * @Given /^the destination directory is (.+)$/
     */
    public function theDestinationDirectoryIs($path)
    {
        $this->destinationDir = $path;
    }

    /**
     * @When I generate a CSV table for :name
     */
    public function iGenerateACsvTableForOperation($name)
    {
        $generator = new Generator;
    }

    /**
     * @Then /^a file called (.+) exists$/
     */
    public function aFileCalledExists($path)
    {
        throw new PendingException();
    }

    /**
     * @Then the output is:
     */
    public function theOutputIs(PyStringNode $string)
    {
        throw new PendingException();
    }
}
