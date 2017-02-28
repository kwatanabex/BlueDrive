<?php
require_once 'AppAdminAction.php';

define('DAO_ACCESS_CONNECTION_STRING', SyL_Config::get('SYL_DB_CONNECTION_STRING'));

abstract class AppAdminSystemAction extends AppAdminAction
{
    public function preExecute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        parent::preExecute($context, $data);
        $this->addBreadcrumbs($data, 'システム', $data->get('App.Admin.url_system_base'));
    }
}
