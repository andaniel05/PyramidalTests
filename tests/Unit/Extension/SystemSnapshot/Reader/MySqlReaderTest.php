<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Tests\Unit\Extension\SystemSnapshot\Reader;

use PDO;
use ThenLabs\PyramidalTests\Extension\SystemSnapshot\Reader\MySqlReader;
use ThenLabs\PyramidalTests\Tests\Unit\UnitTestCase;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class MySqlReaderTest extends UnitTestCase
{
    /**
     * @var MySqlReader
     */
    protected static $db;

    public static function setUpBeforeClass(): void
    {
        $pdo = new PDO($_ENV['MYSQL_DSN'], $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASS']);
        $sql = file_get_contents(__DIR__.'/db.mysql');

        $pdo->exec($sql);

        static::$db = new MySqlReader($pdo);
    }

    public function testGetTables()
    {
        $expected = [
            'country',
            'user',
        ];

        $this->assertEquals($expected, static::$db->getTables());
    }

    public function testGetSchema()
    {
        $expected = [
            'country' => [
                [
                    'Field' => 'id',
                    'Type' => 'int',
                    'Null' => 'NO',
                    'Key' => 'PRI',
                    'Default' => null,
                    'Extra' => 'auto_increment'
                ],
                [
                    'Field' => 'code',
                    'Type' => 'varchar(45)',
                    'Null' => 'NO',
                    'Key' => 'UNI',
                    'Default' => null,
                    'Extra' => ''
                ]
            ],
            'user' => [
                [
                    'Field' => 'id',
                    'Type' => 'int',
                    'Null' => 'NO',
                    'Key' => 'PRI',
                    'Default' => null,
                    'Extra' => 'auto_increment'
                ],
                [
                    'Field' => 'username',
                    'Type' => 'varchar(45)',
                    'Null' => 'NO',
                    'Key' => 'UNI',
                    'Default' => null,
                    'Extra' => ''
                ],
                [
                    'Field' => 'country_id',
                    'Type' => 'int',
                    'Null' => 'YES',
                    'Key' => '',
                    'Default' => null,
                    'Extra' => ''
                ]
            ]
        ];

        $schema = static::$db->getSchema();

        $this->assertEquals($expected, $schema);
    }

    public function testGetSnapshot()
    {
        $expected = [
            'country' => [
                ['id' => 2, 'code' => 'cu'],
                ['id' => 1, 'code' => 'es'],
            ],
            'user' => [
                ['id' => 1, 'username' => 'andy', 'country_id' => 1],
                ['id' => 2, 'username' => 'daniel', 'country_id' => 2],
            ],
        ];

        $this->assertEquals($expected, static::$db->getSnapshot());
    }

    public function testTruncateTables()
    {
        static::$db->truncateTables();

        $expected = [
            'country' => [],
            'user' => [],
        ];

        $this->assertEquals($expected, static::$db->getSnapshot());
    }
}
