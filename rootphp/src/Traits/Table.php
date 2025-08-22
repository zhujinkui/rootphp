<?php
declare(strict_types=1);

namespace Zhujinkui\Rootphp\Traits;

use Hyperf\DbConnection\Db;

trait Table
{
    /**
     * 获取所有表格
     *
     * @return array
     */
    public function getAllTableNames(): array
    {
        $connection = Db::connection();
        $tables     = [];
        foreach ($connection->select('SHOW TABLES') as $row) {
            $tables[] = array_values($row)[0];
        }
        return $tables;
    }

    /**
     * 清空指定表数据
     *
     * @param string $tableName
     */
    public function truncateTable(string $tableName): void
    {
        Db::table($tableName)->truncate();
    }
}