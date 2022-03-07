<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests;

use DirectoryIterator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\TextUI\Command;
use PHPUnit\Util\TestDox\CliTestDoxPrinter;
use ReflectionFunction;
use Symfony\Component\Yaml\Yaml;
use ThenLabs\PyramidalTests\Exception\PyramidalTestsException;
use ThenLabs\PyramidalTests\Model\AbstractModel;
use ThenLabs\PyramidalTests\Model\Record;
use ThenLabs\PyramidalTests\Model\TestCaseModel;
use ThenLabs\PyramidalTests\Model\TestModel;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Framework extends Command
{
    public const CREDITS = "\e[1;33mPyramidalTests %s\e[0m by Andy Daniel Navarro Taño and contributors.\n";
    public const VERSION = '2.0.0';

    public const DEFAULT_OPTIONS = [
        'dsl'          => ['phpunit', 'describe-it'],
        'file_pattern' => '^test.*\.php$',
        'colors'       => true,
        'testdox'      => false,
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

        printf(self::CREDITS, self::VERSION);

        if (isset($this->arguments['configuration'])) {
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

            if (true === $options['testdox']) {
                $argv[] = '--printer='.PyramidalTestDoxPrinter::class;
            }

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

            // load the DSL to use.
            if (is_string($options['dsl'])) {
                $this->loadDsl($options['dsl']);
            } elseif (is_array($options['dsl'])) {
                foreach ($options['dsl'] as $dsl) {
                    $this->loadDsl($dsl);
                }
            }

            $options['file_pattern'] = '/'.$options['file_pattern'].'/';

            // load the test files.
            $loader = new \PHPUnit\TextUI\XmlConfiguration\Loader();
            $configuration = $loader->load($configurationFileName);

            foreach ($configuration->testSuite() as $testSuite) {
                if (isset($this->arguments['testsuite']) &&
                    $this->arguments['testsuite'] != $testSuite->name()
                ) {
                    continue;
                }

                foreach ($testSuite->directories() as $directory) {
                    $this->includeDirectory($directory->path(), $options['file_pattern']);
                }
            }
        } elseif (isset($argv[1]) && file_exists($argv[1])) {
            require_once $argv[1];
        }

        if (true == $options['colors']) {
            $argv[] = '--colors';
        }

        $argv[] = '--do-not-cache-result';

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
        foreach (new DirectoryIterator($directoryPath) as $fileInfo) {
            $pathName = $fileInfo->getPathname();
            $fileName = $fileInfo->getFilename();

            if (! $fileInfo->isDot() && $fileInfo->isDir()) {
                $this->includeDirectory($pathName, $filePattern);
            } elseif (preg_match($filePattern, $fileName)) {
                // reset the base test case class per each file.
                setTestCaseClass(TestCase::class);
                Record::setCurrentTestCaseModel(null);

                require_once $pathName;
            }
        }
    }

    private function loadDsl(string $dsl): void
    {
        if (0 === strcasecmp($dsl, 'PHPUnit')) {
            require_once __DIR__.'/DSL/PHPUnit.php';
        } elseif (0 === strcasecmp($dsl, 'describe-it')) {
            require_once __DIR__.'/DSL/DescribeIt.php';
        } else {
            throw new PyramidalTestsException("The value '{$dsl}' is not a valid DSL.");
        }
    }
}
