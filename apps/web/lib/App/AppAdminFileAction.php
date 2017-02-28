<?php
require_once 'AppAdminAction.php';

abstract class AppAdminFileAction extends AppAdminAction
{
    public function preExecute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        parent::preExecute($context, $data);
        $this->addBreadcrumbs($data, 'ファイル', $data->get('App.Admin.url_file_base'));
    }


}
