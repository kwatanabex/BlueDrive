<?php
require_once 'AppAdminAction.php';

abstract class AppAdminUserAction extends AppAdminAction
{
    public function preExecute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        parent::preExecute($context, $data);
        $this->addBreadcrumbs($data, 'ユーザー', $data->get('App.Admin.url_user_base'));

        $classname = get_class($this);
        $tmp = explode('_', $classname);
        $action = strtolower($tmp[2]);

        $data->set('action', $action);
    }
}
