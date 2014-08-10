<?php

namespace features\OpenStack\DocGenerator;

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use OpenStack\DocGenerator\Generator;
use Symfony\Component\Filesystem\Filesystem;

class FeatureContext implements SnippetAcceptingContext
{
    private $filesystem;
    private $serviceFile;
    private $serviceDir;
    private $srcDir;
    private $desDir;
    private $generator;

    private static $deletePaths = [];

    public function __construct()
    {
        $this->filesystem = new Filesystem();

        $baseDir = __DIR__ . '/.test';
        $this->srcDir = $baseDir . '/src/OpenStack';
        $this->desDir = $baseDir . '/doc';

        self::$deletePaths[] = $baseDir;
    }

    /**
     * @AfterFeature
     */
    public static function tearDown()
    {
        (new Filesystem())->remove(self::$deletePaths);
    }

    /**
     * @Given /^the (.+) file exists inside the (.+) directory$/
     */
    public function thePathExists($file, $dir)
    {
        $this->serviceDir  = $this->srcDir . DIRECTORY_SEPARATOR . $dir;
        $this->serviceFile = $this->serviceDir . DIRECTORY_SEPARATOR . $file;

        $this->filesystem->mkdir($dir);
        $this->filesystem->touch($this->serviceFile);
    }

    /**
     * @Given the/a PHP file contains:
     */
    public function thePhpFileContains(PyStringNode $string)
    {
        file_put_contents($this->serviceFile, (string) $string);
    }

    /**
     * @Given the service description contains:
     */
    public function theServiceDescriptionContains(PyStringNode $string)
    {
        $path = $this->serviceDir . '/Description/Service.yml';
        file_put_contents($path, (string) $string);
    }

    /**
     * @When I generate documentation for this service
     */
    public function iGenerateDocFilesForThisService()
    {
        $this->generator = new Generator($this->srcDir, $this->desDir);
        $this->generator->buildDocs();
    }

    /**
     * @When I generate the parameter table for this service
     */
    public function iGenerateTheParameterTableForThisService()
    {
        throw new PendingException();
    }

    /**
     * @When I generate code samples for this service
     */
    public function iGenerateCodeSamples()
    {
        throw new PendingException();
    }

    /**
     * @When I generate the signatures for this service
     */
    public function iGenerateTheSignaturesForThisService()
    {
        throw new PendingException();
    }

    /**
     * @Then these doc files should exist:
     */
    public function theseDocFilesShouldExist(TableNode $table)
    {
        throw new PendingException();
    }

    /**
     * @Then the output should be nothing
     */
    public function itShouldOutputNothing()
    {
        throw new PendingException();
    }

    /**
     * @Then the output should be:
     */
    public function theOutputShouldBe(PyStringNode $string)
    {
        throw new PendingException();
    }
}