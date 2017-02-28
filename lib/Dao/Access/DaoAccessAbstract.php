<?php
if (defined('SYL_FRAMEWORK_DIR')) {
    require_once SYL_FRAMEWORK_DIR . '/Lib/Db/SyL_DbDao.php';
    require_once SYL_FRAMEWORK_DIR . '/Lib/Db/SyL_DbDaoAccessAbstract.php';
} else {
    require_once '/home/webuser/SyL/framework/../framework/Lib/Db/SyL_DbDao.php';
    require_once '/home/webuser/SyL/framework/../framework/Lib/Db/SyL_DbDaoAccessAbstract.php';
}

// SyL_DB connection string setting
//define('DAO_ACCESS_CONNECTION_STRING', '');

abstract class DaoAccessAbstract extends SyL_DbDaoAccessAbstract
{
    public function __construct()
    {
        if (!defined('DAO_ACCESS_CONNECTION_STRING')) {
            throw new SyL_DbException('SyL_DB connection string not setting');
        }
        parent::__construct(SyL_DbAbstract::getInstance(DAO_ACCESS_CONNECTION_STRING));
    }
}
