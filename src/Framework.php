<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests;

use ReflectionFunction;
use PHPUnit\TextUI\Command;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\Version;
use Symfony\Component\Yaml\Yaml;
use PHPUnit\Util\TestDox\CliTestDoxPrinter;
use ThenLabs\PyramidalTests\Model\TestModel;
use ThenLabs\PyramidalTests\Model\AbstractModel;
use ThenLabs\PyramidalTests\Model\TestCaseModel;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Framework extends Command
{
    public const VERSION = '2.0.0';

    public const DEFAULT_OPTIONS = [
        'file_pattern' => '*-test.php',
    ];

    /**
     * @var string
     */
    protected static $filter;

    protected function handleCustomTestSuite(): void
    {
        $mainTestSuite = new TestSuite;

        foreach (Record::getAllTestCaseModels() as $testCaseModel) {
            if ($testCaseModel instanceof TestCaseModel) {
                $this->loadSuiteFromTestCaseModel($testCaseModel, $mainTestSuite);
            }
        }

        $this->arguments['test'] = $mainTestSuite;
    }

    public function run(array $argv, bool $exit = true): int
    {
        $options = self::DEFAULT_OPTIONS;

        $this->handleArguments($argv);

        if (version_compare(Version::id(), '9', '>=')) {
            //  Compatibility with PHPUnit 9.
            $this->arguments['colors'] = \PHPUnit\TextUI\DefaultResultPrinter::COLOR_AUTO;
        } else {
            //  Compatibility with PHPUnit 8.
            $this->arguments['colors'] = \PHPUnit\TextUI\ResultPrinter::COLOR_AUTO;
        }

        printf(
            "\e[1;33mPyramidalTests %s\e[0m\n",
            self::VERSION
        );

        if (isset($this->arguments['printer']) &&
            $this->arguments['printer'] == CliTestDoxPrinter::class
        ) {
            $argv[] = '--printer='.PyramidalTestDoxPrinter::class;
            $argv[] = '--columns=max';
        }

        if (array_key_exists('filter', $this->arguments)) {
            self::$filter = $this->arguments['filter'];

            // unsetting the original filter argument.
            $filterArgKey = array_search('--filter', $argv);
            if (is_int($filterArgKey)) { // style: --filter '...'
                unset($argv[$filterArgKey], $argv[$filterArgKey + 1]);
            } elseif (is_bool($filterArgKey)) { // style: --filter='...'
                foreach ($argv as $key => $value) {
                    if (0 === strpos($value, '--filter=')) {
                        unset($argv[$key]);
                        break;
                    }
                }
            }

            unset($this->arguments['filter']);
        }

        $configurationFileName = $this->arguments['configuration'];
        $directory = dirname($configurationFileName);
        $pyramidalYamlFileName = $directory.'/pyramidal.yaml';

        // load config from pyramidal.yaml file if exists.
        if (file_exists($pyramidalYamlFileName)) {
            $options = array_merge(
                $options,
                Yaml::parseFile($pyramidalYamlFileName)['pyramidal']
            );
        }

        // load the test files.
        if (version_compare(Version::id(), '9', '>=')) {
            //  Compatibility with PHPUnit 9.
            $loader = new \PHPUnit\TextUI\XmlConfiguration\Loader();
            $configuration = $loader->load($configurationFileName);

            foreach ($configuration->testSuite() as $testSuite) {
                foreach ($testSuite->directories() as $directory) {
                    $this->includeDirectory($directory->path(), $options['file_pattern']);
                }
            }
        } else {
            //  Compatibility with PHPUnit 8.
            $configuration = \PHPUnit\Util\Configuration::getInstance($configurationFileName);

            $xpath = (function () {
                return $this->xpath;
            })->call($configuration);

            $directoryNodes = $xpath->query('testsuites/testsuite/directory');
            foreach ($directoryNodes as $directoryNode) {
                $this->includeDirectory(
                    dirname($configurationFileName).'/'.strval($directoryNode->textContent),
                    $options['file_pattern']
                );
            }
        }

        return parent::run($argv, $exit);
    }

    private function loadSuiteFromTestCaseModel(TestCaseModel $testCaseModel, TestSuite $mainTestSuite): void
    {
        if (is_string(self::$filter)) {
            if (preg_match('/^(.+):(\d+)$/', self::$filter, $matches)) {
                // the filtering pattern is "path/to/file:line"

                $fileName = $matches[1];
                $line = (int) $matches[2];

                $this->filterByClosure($testCaseModel, $mainTestSuite, $fileName, $line);
            } elseif (false !== strpos($testCaseModel->getTitle(), self::$filter)) {
                // the title of the test case contains the filtering pattern.

                $this->registerTestCaseModel($testCaseModel, $mainTestSuite);
            } else {
                // drop tests wich title don't matches with the filtering pattern.

                foreach ($testCaseModel->getRootTestModels() as $testModel) {
                    if (false === strpos($testModel->getTitle(), self::$filter)) {
                        $testCaseModel->dropChild($testModel, false);
                    }
                }

                $this->registerTestCaseModel($testCaseModel, $mainTestSuite);
            }
        } else {
            $this->registerTestCaseModel($testCaseModel, $mainTestSuite);
        }
    }

    private function registerTestCaseModel(TestCaseModel $testCaseModel, TestSuite $mainTestSuite, bool $deep = true): void
    {
        if (count($testCaseModel->getRootTestModels())) {
            $testCaseModel->buildClass();

            $newTestSuite = new TestSuite($testCaseModel->getClassBuilder()->getFCQN());
            $mainTestSuite->addTestSuite($newTestSuite);
        }

        if ($deep) {
            foreach ($testCaseModel->getRootTestCaseModels() as $child) {
                if ($child instanceof TestCaseModel) {
                    $this->loadSuiteFromTestCaseModel($child, $mainTestSuite);
                }
            }
        }
    }

    private function filterByClosure(AbstractModel $model, TestSuite $mainTestSuite, string $fileName, int $line): void
    {
        $closureReflector = new ReflectionFunction($model->getClosure());
        $closureFileName = $closureReflector->getFileName();
        $closureStartLine = $closureReflector->getStartLine();

        if ($fileName == $closureFileName &&
            $line == $closureStartLine
        ) {
            if ($model instanceof TestCaseModel) {
                $this->registerTestCaseModel($model, $mainTestSuite, false);
            } elseif ($model instanceof TestModel) {
                $parentTestCaseModel = $model->getParent();
                foreach ($parentTestCaseModel->getRootTestModels() as $childTestModel) {
                    if ($childTestModel !== $model) {
                        $parentTestCaseModel->dropChild($childTestModel);
                    }
                }
                $this->registerTestCaseModel($parentTestCaseModel, $mainTestSuite, false);
            }
        } elseif ($model instanceof TestCaseModel) {
            foreach ($model->children(false) as $child) {
                $this->filterByClosure($child, $mainTestSuite, $fileName, $line);
            }
        }
    }

    private function includeDirectory(string $directoryPath, string $filePattern): void
    {
        $pattern = $directoryPath.'/'.$filePattern;
        $fileNames = glob($pattern);

        // include the test files.
        foreach ($fileNames as $fileName) {
            require_once $fileName;

            // reset the base test case class per each file.
            setTestCaseClass(TestCase::class);
            Record::setCurrentTestCaseModel(null);
        }
    }
}
