<?php
SyL_Loader::userLib('BlueDriveException');

abstract class AppAction extends SyL_ActionAbstract
{
    /**
     * アクションメソッド実行前に実行されるメソッド
     *
     * @param SyL_ContextAbstract フィールド情報管理オブジェクト
     * @param SyL_Data データオブジェクト
     */
    public function preExecute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
            $this->setActionMethod('executeAjax');
        }

        $url_base = $context->getRequest()->getUrlBase() . '/';
        $url_admin_base = $url_base . 'admin/';

        $url_root = dirname($url_base);
        if ($url_root == '/') {
            $url_root = '';
        }

        $data->set('App.url_root', $url_root);
        $data->set('App.url_base', $url_base);
        $data->set('App.url_admin_base', $url_admin_base);
    }

    /**
     * Ajax呼び出し時にコールされるアクションメソッド
     * 
     * @param SyL_ContextAbstract フィールド情報管理オブジェクト
     * @param SyL_Data データオブジェクト
     */
    protected function executeAjax(SyL_ContextAbstract $context, SyL_Data $data)
    {
        throw new SyL_NotImplementedException('ajax method not implemented in action class');
    }

    /**
     * アクションメソッド実行後に実行されるメソッド
     * 
     * @param SyL_ContextAbstract フィールド情報管理オブジェクト
     * @param SyL_Data データオブジェクト
     */
    public function postExecute(SyL_ContextAbstract $context, SyL_Data $data)
    {
    }


    protected static function getCrudDatabases()
    {
        $config = SyL_ConfigFileAbstract::createInstance('dao');
        $config->parse(false);
        $result = $config->getConfig();

        return $result['database'];
    }
}
