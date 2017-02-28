<?php
require_once 'AppAction.php';

abstract class AppAdminAction extends AppAction
{
    /**
     * アクションメソッド実行前に実行されるメソッド
     *
     * @param SyL_ContextAbstract フィールド情報管理オブジェクト
     * @param SyL_Data データオブジェクト
     */
    public function preExecute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        parent::preExecute($context, $data);

        $url_self = $context->getRequest()->getUrlSelf();
        $main_menu_name = '';
        if (preg_match('|^/admin/([^/]+)/?.*$|', $url_self, $matches)) {
            $main_menu_name = $matches[1];
        }

        $url_base       = $data->get('App.url_base');
        $url_admin_base = $data->get('App.url_admin_base');

        $url_file_base      = $url_admin_base . 'file/';
        $url_table_base     = $url_admin_base . 'table/';
        $url_tablemeta_base = $url_admin_base . 'tablemeta/';
        $url_system_base    = $url_admin_base . 'system/';
        $url_user_base      = $url_admin_base . 'user/';

        $system_data = self::getSystemData($data);
        if ($system_data->crudDatabaseCount == 0) {
            $url_tablemeta_base = '';
        }

        $crud_outpur_dir = $context->getUser()->crudCurrentOutputDir;
        $crud_display_list_file = $crud_outpur_dir . SyL_Config::get('CRUD_DISPLAY_LIST_FILE');
        if (!is_file($crud_display_list_file)) {
            $url_table_base = '';
        }

        $classname = get_class($this);
        $tmp = explode('_', $classname);
        $action = (count($tmp) > 2) ? strtolower($tmp[2]) : null;

        $breadcrumbs['TOP'] = $url_admin_base;

        $data->set('App.Admin.url_file_base', $url_file_base);
        $data->set('App.Admin.url_table_base', $url_table_base);
        $data->set('App.Admin.url_tablemeta_base', $url_tablemeta_base);
        $data->set('App.Admin.url_system_base', $url_system_base);
        $data->set('App.Admin.url_user_base', $url_user_base);
        $data->set('App.Admin.breadcrumbs', $breadcrumbs);
        $data->set('App.Admin.main_menu_name', $main_menu_name);

        $data->set('App.Admin.login_user_name', $context->getUser()->getName());
        $data->set('App.Admin.Action', $action);
    }

    /**
     * メニュー情報を追加する
     *
     * @param SyL_Data データオブジェクト
     * @param string メニュー名
     * @param string URL
     */
    protected function addBreadcrumbs(SyL_Data $data, $name, $url)
    {
        $breadcrumbs = $data->get('App.Admin.breadcrumbs');
        $breadcrumbs[$name] = $url;
        $data->set('App.Admin.breadcrumbs', $breadcrumbs);
    }

    /**
     * システムセッション情報を取得する
     *
     * @param SyL_Data データオブジェクト
     * @return DataSessionSystem システムセッション情報
     */
    protected static function getSystemData(SyL_Data $data)
    {
        return $data->getSession(DataSessionSystem::SESSION_KEY);
    }
}
