<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Extension\SystemSnapshot\Reader;

use PDO;
use PDOStatement;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class SqLiteReader extends AbstractPDOReader
{
    public function getTables(): array
    {
        $result = [];

        $sql = "SELECT name FROM sqlite_master WHERE type = 'table'";
        $query = $this->pdo->query($sql);

        if ($query instanceof PDOStatement) {
            foreach ($query as $row) {
                $result[] = $row['name'];
            }
        }

        return $result;
    }

    public function getSnapshot(): array
    {
        $result = [];

        $tables = $this->getTables($this->pdo);
        foreach ($tables as $tableName) {
            $sql = "SELECT * FROM {$tableName}";
            $query = $this->pdo->query($sql);
            $result[$tableName] = $query->fetchAll(PDO::FETCH_ASSOC);
        }

        return $result;
    }

    public function truncateTables(): void
    {
        $tables = $this->getTables($this->pdo);
        $sql = 'SET FOREIGN_KEY_CHECKS=0;';

        foreach ($tables as $tableName) {
            $sql .= "TRUNCATE TABLE {$tableName};";
        }

        $sql .= 'SET FOREIGN_KEY_CHECKS=1;';

        $this->pdo->exec($sql);
    }

    public function getSchema(): array
    {
        // TODO

        return [];
    }
}
