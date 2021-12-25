<?php
declare(strict_types=1);

namespace ThenLabs\PyramidalTests\Extension\SystemSnapshot\Reader;

use PDO;
use PDOStatement;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class MySqlReader extends AbstractPDOReader
{
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

    public function getSnapshot(): array
    {
        $result = [];

        foreach ($this->getTables() as $tableName) {
            $sql = "SELECT * FROM {$tableName}";
            $query = $this->pdo->query($sql);
            $result[$tableName] = $query->fetchAll(PDO::FETCH_ASSOC);
        }

        return $result;
    }

    public function truncateTables(): void
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
