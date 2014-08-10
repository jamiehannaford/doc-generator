<?php

namespace features\OpenStack\DocGenerator;

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use features\OpenStack\Assert;
use OpenStack\DocGenerator\BatchGenerator;
use OpenStack\DocGenerator\Generator;
use OpenStack\DocGenerator\ServiceGenerator;
use OpenStack\DocGenerator\ServiceRetriever;
use Symfony\Component\Filesystem\Filesystem;

class FeatureContext implements SnippetAcceptingContext
{
    private static $filesystem;
    private static $serviceDir;
    private static $serviceName;
    private static $serviceDesc;
    private static $docDir;

    public function __construct()
    {
        self::$filesystem = new Filesystem();
        self::$serviceDir = __DIR__ . '/Fixtures/src';
        self::$docDir     = __DIR__ . '/Fixtures/doc';

        $path = self::$serviceDir;

        spl_autoload_register(function ($class) use ($path) {
            if (strpos($class, 'OpenStack') !== false) {
                $file = __DIR__ . '/Fixtures/src/'
                    . str_replace('OpenStack/', '', str_replace('\\', '/', $class))
                    . '.php';
                if (file_exists($file)) {
                    require_once $file;
                }
            }
        }, true, true);
    }

    public static function setUp()
    {
        if (file_exists(__DIR__ . '/Fixtures')) {
            self::tearDown();
        }

        $dir = self::getFullServiceDir();
        self::$filesystem->mkdir([$dir, self::getDocPath() . '/_generated']);
        self::$filesystem->touch($dir . '/Service.php');

        self::setupDescDir();
    }

    private static function setupDescDir()
    {
        $descPath = self::getDescPath();
        self::$filesystem->mkdir($descPath);
        file_put_contents($descPath . '/Service.yml', (string) self::$serviceDesc);
    }

    private static function getFullServiceDir()
    {
        return self::$serviceDir . '/' . self::$serviceName . '/v2';
    }

    /**
     * @AfterScenario
     */
    public static function tearDown()
    {
        self::$filesystem->remove(__DIR__ . '/Fixtures');
    }

    /**
     * @Given the service is named :name
     */
    public function theServiceIs($service)
    {
        self::$serviceName = $service;
        self::setUp();
    }

    /**
     * @Given the/a PHP file contains:
     */
    public function thePhpFileContains(PyStringNode $string)
    {
        $file = self::getFullServiceDir() . '/Service.php';
        file_put_contents($file, (string) $string);
    }

    /**
     * @Given the service description contains:
     */
    public function theServiceDescriptionContains(PyStringNode $string)
    {
        self::$serviceDesc = (string) $string;

        if (self::$serviceName) {
            self::setupDescDir();
        }
    }

    /**
     * @When I generate documentation for this service
     */
    public function iGenerateDocFilesForThisService()
    {
        $generator = new BatchGenerator(self::$serviceDir, self::$docDir);
        $generator->buildDocs();
    }

    private function getFqcn()
    {
        return 'OpenStack\\' . self::$serviceName . '\\v2\\Service';
    }

    private static function getDescPath()
    {
        return self::getFullServiceDir() . '/Description';
    }

    private static function getDocPath()
    {
        return self::$docDir . '/' . ServiceRetriever::getDocPath(self::$serviceName, 'v2');
    }

    private function getServiceGenerator()
    {
        $generator = new ServiceGenerator($this->getFqcn(), self::getDocPath(), self::getDescPath());
        $generator->createDocDirectory();
        return $generator;
    }

    /**
     * @When I generate the parameter table for this service
     */
    public function iGenerateTheParameterTableForThisService()
    {
        $this->getServiceGenerator()->createParamsTableFiles();
    }

    /**
     * @When I generate code samples for this service
     */
    public function iGenerateCodeSamples()
    {
        $this->getServiceGenerator()->createCodeSampleFiles();
    }

    /**
     * @When I generate the signatures for this service
     */
    public function iGenerateTheSignaturesForThisService()
    {
        $this->getServiceGenerator()->createSignatureFiles();
    }

    /**
     * @Then these doc files should exist:
     */
    public function theseDocFilesShouldExist(TableNode $table)
    {
        foreach ($table as $path) {
            Assert::equals(file_exists(self::getDocPath() . '/_generated/' . $path['filename']), true);
        }
    }

    /**
     * @Then the output should be nothing
     */
    public function itShouldOutputNothing()
    {
        throw new PendingException();
    }

    /**
     * @Then /^(.+) should contain:$/
     */
    public function theOutputShouldBe($path, PyStringNode $string)
    {
        $content = file_get_contents(self::getDocPath() . '/_generated/' . $path);

        Assert::equals(trim($content), trim((string) $string));
    }

    /**
     * @Then /^(.+) should not exist$/
     */
    public function theFileShouldNotExist($path)
    {
        Assert::equals(file_exists(self::getDocPath() . '/_generated/' . $path), false);
    }
}