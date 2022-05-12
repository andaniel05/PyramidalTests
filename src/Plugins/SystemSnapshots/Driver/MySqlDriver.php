<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Plugins\SystemSnapshots\Driver;

use PDO;
use PDOStatement;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class MySqlDriver extends AbstractDriver
{
    /**
     * @var PDO
     */
    protected $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getPDO(): PDO
    {
        return $this->pdo;
    }

    public function getTables(): array
    {
        $result = [];

        $sql = 'SHOW TABLES';
        $query = $this->pdo->query($sql);

        if ($query instanceof PDOStatement) {
            foreach ($query as $row) {
                $result[] = $row[0];
            }
        }

        return $result;
    }

    public function getData(): array
    {
        $result = [];

        foreach ($this->getTables() as $tableName) {
            $sql = "SELECT * FROM {$tableName}";
            $query = $this->pdo->query($sql);
            $result[$tableName] = $query->fetchAll(PDO::FETCH_ASSOC);
        }

        return $result;
    }

    public function reset(): void
    {
        $sql = 'SET FOREIGN_KEY_CHECKS=0;';

        foreach ($this->getTables() as $tableName) {
            $sql .= "TRUNCATE TABLE {$tableName};";
        }

        $sql .= 'SET FOREIGN_KEY_CHECKS=1;';

        $this->pdo->exec($sql);
    }

    public function getSchema(): array
    {
        $result = [];

        foreach ($this->getTables() as $tableName) {
            $sql = "DESCRIBE {$tableName}";
            $query = $this->pdo->query($sql);
            $result[$tableName] = $query->fetchAll(PDO::FETCH_ASSOC);
        }

        return $result;
    }
}
