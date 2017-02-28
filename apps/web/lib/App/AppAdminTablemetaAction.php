<?php
require_once 'AppAdminAction.php';

abstract class AppAdminTablemetaAction extends AppAdminAction
{
    public function preExecute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        parent::preExecute($context, $data);
        $this->addBreadcrumbs($data, 'テーブルメタ', $data->get('App.Admin.url_tablemeta_base'));

        $data->set('current_database', $context->getUser()->crudCurrentDatabase);
        $data->set('databases', self::getCrudDatabases());
    }
}
