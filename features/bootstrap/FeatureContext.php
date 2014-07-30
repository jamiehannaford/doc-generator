<?php

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use GuzzleHttp\Command\Guzzle\Operation;
use GuzzleHttp\Command\Guzzle\Description;
use GuzzleHttp\Stream\Stream;
use OpenStack\DocGenerator\ParameterTableGenerator;
use OpenStack\DocGenerator\Generator;
use OpenStack\DocGenerator\ServiceFinder;

class FeatureContext implements SnippetAcceptingContext
{
    private $operation;
    private $stream;
    private $tmpFile = '.foo';

    private $srcDir;
    private $desDir;

    /**
     * @AfterFeature
     */
    public static function deleteFixtures()
    {
        self::rrmdir('behatDocTest');
    }

    private static function rrmdir($dir)
    {
        foreach(glob($dir . '/*') as $file) {
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
    public function anOperationHasTheseParameters($name, TableNode $table)
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

        $config = ['name' => $name, 'parameters' => $params];

        $this->operation = new Operation($config, new Description([]));
    }

    /**
     * @Given /^the (.+) directory exists$/
     */
    public function theDirectoryExists($name)
    {
        mkdir($name, 0777, true);
    }

    /**
     * @Given /^the (.+) file contains:$/
     */
    public function theFileContains($name, PyStringNode $string)
    {
        file_put_contents($name, (string) $string);
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
        $this->srcDir = $path;
    }

    /**
     * @When /^I specify the destination directory as (.+)$/
     */
    public function iSpecifyTheDestinationDirectoryAs($path)
    {
        $this->desDir = $path;
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
        foreach ($table as $t) {
            var_dump($t);die;
        }
    }
}