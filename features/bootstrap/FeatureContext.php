<?php

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Hook\Scope\AfterFeatureScope;
use GuzzleHttp\Command\Guzzle\Operation;
use GuzzleHttp\Command\Guzzle\Description;
use GuzzleHttp\Stream\Stream;
use OpenStack\DocGenerator\ParameterTableGenerator;
use OpenStack\DocGenerator\Generator;
use OpenStack\DocGenerator\ServiceFinder;
use OpenStack\DocGenerator\CodeSampleGenerator;

class FeatureContext implements SnippetAcceptingContext
{
    private $operation;
    private $operationConfig;
    private $stream;
    private $tmpFile = '.foo';

    private $isIteratorOperation = false;

    private $srcDir;
    private $desDir;
    private static $basePath;

    public function __construct()
    {
        self::$basePath = __DIR__ . '/.behatDocTest/';
    }

    /**
     * @AfterFeature
     */
    public static function deleteFixtures(AfterFeatureScope $scope)
    {
        if ($scope->getFeature()->hasTag('file-clean')) {
            self::rrmdir(self::$basePath);
        }
    }

    private static function rrmdir($dir)
    {
        foreach (glob($dir . '/*') as $file) {
            if (is_dir($file)) {
                self::rrmdir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dir);
    }

    /**
     * @Given a :name operation has these parameters:
     */
    public function anOperationHasTheseParameters($operationName, TableNode $table)
    {
        $params = [];
        foreach ($table as $param) {
            $name = $param['name'];
            unset($param['name']);

            if (!empty($param['enum'])) {
                $param['enum'] = [$param['enum']];
            } else {
                unset($param['enum']);
            }

            if ($param['required'] === 'false') {
                $param['required'] = false;
            }

            $params[$name] = $param;
        }

        $this->operationConfig = ['name' => $operationName, 'parameters' => $params];

        $this->operation = new Operation($this->operationConfig, new Description([]));
    }

    /**
     * @Given /^the (.+) directory exists$/
     */
    public function theDirectoryExists($name)
    {
        mkdir(self::$basePath . $name, 0777, true);
    }

    /**
     * @Given /^the (.+) file contains:$/
     */
    public function theFileContains($name, PyStringNode $string)
    {
        file_put_contents(self::$basePath . $name, (string) $string);
    }

    /**
     * @Given it has these properties in its response model:
     */
    public function anOperationHasTheseProperties(TableNode $table)
    {
        $this->operationConfig['responseModel'] = $table->getColumnsHash();

        $this->operation = new Operation($this->operationConfig, new Description([]));
    }

    /**
     * @Given it has these properties for each resource:
     */
    public function anIteratorOperationHasTheseProperties(TableNode $table)
    {
        $properties = [];
        foreach ($table as $array) {
            $name = $array['name'];
            unset($array['name']);
            $properties[$name] = $array;
        }

        $this->operationConfig['data']['iterator']['modelSchema'] = [
            'type'       => 'object',
            'properties' => $properties
        ];

        $this->isIteratorOperation = true;

        $this->operation = new Operation($this->operationConfig, new Description([]));
    }

    /**
     * @When I generate a CSV table for :name
     */
    public function iGenerateACsvTableForOperation($name)
    {
        $this->stream = Stream::factory(fopen($this->tmpFile, 'w+'));

        $generator = new ParameterTableGenerator($this->operation, $this->stream);
        $generator->writeAll();
    }

    /**
     * @When /^I specify the source directory as (.+)$/
     */
    public function iSpecifyTheSourceDirectoryAs($path)
    {
        $this->srcDir = self::$basePath . $path;
    }

    /**
     * @When /^I specify the destination directory as (.+)$/
     */
    public function iSpecifyTheDestinationDirectoryAs($path)
    {
        $this->desDir = self::$basePath . $path;
    }

    /**
     * @When I generate files
     */
    public function iGenerateFiles()
    {
        $finder = new ServiceFinder($this->srcDir);

        $generator = new Generator($finder, null, $this->desDir);
        $generator->writeFiles();
    }

    /**
     * @When I generate sample code
     */
    public function iGenerateSampleCode()
    {
        $this->stream = Stream::factory(fopen($this->tmpFile, 'w+'));

        $generator = new CodeSampleGenerator($this->operation, $this->stream);
        $generator->writeAll();
    }

    /**
     * @Then the output is:
     */
    public function theOutputIs(PyStringNode $expected)
    {
        $this->stream->close();

        $actual = trim(file_get_contents($this->tmpFile));
        $expected = trim((string) $expected);

        if ($expected !== $actual) {
            throw new ErrorException(sprintf(
                "%s does not match expected %s",
                $actual, (string) $expected
            ));
        }

        unlink($this->tmpFile);
    }

    /**
     * @Then these files should exist:
     */
    public function theseFilesShouldExist(TableNode $table)
    {
        foreach ($table as $row) {
            $path = $this->desDir . $row['name'];
            if (!file_exists($path)) {
                throw new ErrorException(sprintf("%s does not exist", $path));
            }
        }
    }
}