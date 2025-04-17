<?php

namespace Itb\Core;

use Bitrix\Main\ORM\Data\DataManager;

abstract class BaseTable extends DataManager
{
    public static function dropTable() : void
    {
        if (static::tableExists()) {
            $connection = \Bitrix\Main\Application::getConnection();
            $connection->dropTable(static::getTableName());
        }
    }

    public static function createTable() : void
    {
        if (!static::tableExists()) {
            static::getEntity()->createDbTable();
        }
    }

    public static function tableExists() : bool
    {
        return static::getEntity()->getConnection()->isTableExists(static::getTableName());
    }
}