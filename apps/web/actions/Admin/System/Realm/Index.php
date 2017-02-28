<?php
SyL_Loader::userLib('Bl.Abstract');

/**
 * Index クラス
 */
class Admin_System_Realm_Index extends AppAdminSystemAction
{
    /**
     * デフォルトアクション処理
     *
     * @param SyL_ContextAbstract コンテキストオブジェクト
     * @param SyL_Data データオブジェクト
     */
    public function execute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $this->addBreadcrumbs($data, 'アクセス範囲管理', '');
    }

    /**
     * Ajax呼び出し時にコールされるアクションメソッド
     * 
     * @param SyL_ContextAbstract フィールド情報管理オブジェクト
     * @param SyL_Data データオブジェクト
     */
    protected function executeAjax(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $type = $data->get('type');

        $result = array();
        $result['valid'] = false;
        switch ($type) {
        case 'list':
            $realm_name  = $data->get('realm_name');
            $realm = $data->get('realm');
            $page = $data->get('page');

            if (!is_numeric($page) || ($page < 1)) {
                $page = 1;
            }

            list($rows, $page_info) = BlAbstract::createInstance('realm')->getRealms($realm_name, $realm, $page);

            $result['valid'] = true;
            $result['rows'] = $rows;
            $result['page_info'] = $page_info;
            break;
        }

        $context->setViewJson($result);
    }

}
