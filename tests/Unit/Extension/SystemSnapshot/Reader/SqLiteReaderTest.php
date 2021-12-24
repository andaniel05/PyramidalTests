<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Tests\Unit\Extension\SystemSnapshot\Reader;

use PDO;
use ThenLabs\PyramidalTests\Extension\SystemSnapshot\Reader\SqLiteReader;
use ThenLabs\PyramidalTests\Tests\Unit\UnitTestCase;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class SqLiteReaderTest extends UnitTestCase
{
    /**
     * @var string
     */
    protected static $dbFileName;

    /**
     * @var SqLiteReader
     */
    protected $db;

    public static function setUpBeforeClass(): void
    {
        $originalDbFileName = __DIR__.'/db.sqlite';
        $dbFileName = __DIR__.'/db-'.time().'.sqlite';

        copy($originalDbFileName, $dbFileName);

        self::$dbFileName = $dbFileName;
    }

    public function setUp(): void
    {
        $pdo = new PDO('sqlite:'.static::$dbFileName);
        $this->db = new SqLiteReader($pdo);
    }

    public static function tearDownAfterClass(): void
    {
        unlink(static::$dbFileName);
    }

    public function testGetTables()
    {
        $expected = [
            'sqlite_sequence',
            'animals',
            'persons',
        ];

        $this->assertEquals($expected, $this->db->getTables());
    }
}
