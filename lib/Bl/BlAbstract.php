<?php
//require_once 'BlException.php';
SyL_Loader::lib('Db.Dao');

// DAO Access —p
if (!defined('DAO_ACCESS_CONNECTION_STRING')) {
    define('DAO_ACCESS_CONNECTION_STRING', SyL_Config::get('SYL_DB_CONNECTION_STRING'));
}

abstract class BlAbstract
{
    private static $db = null;

    public function __construct()
    {
        if (!self::$db) {
            self::$db = SyL_DbAbstract::getInstance(DAO_ACCESS_CONNECTION_STRING);
            self::$db->setCallbackSql(array('SyL_Logger', 'debug'));
        }
    }

    public static function createInstance($name)
    {
        $classname = SyL_Loader::userLib('Bl.' . ucfirst($name) . 'Manager');
        return new $classname();
    }

    protected function getDaoAccess($name)
    {
        $classname = SyL_Loader::userLib('Dao.Access.' . ucfirst($name));
        return new $classname();
    }

    protected function beginTransaction()
    {
        self::$db->beginTransaction();
    }

    protected function commit()
    {
        self::$db->commit();
    }

    protected function rollBack()
    {
        self::$db->rollBack();
    }

}
