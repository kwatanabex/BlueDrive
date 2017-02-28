
<?php
/**
 * Index クラス
 */
class Admin_System_Sql_Index extends AppAdminSystemAction
{
    /**
     * デフォルトアクション処理
     *
     * @param SyL_ContextAbstract コンテキストオブジェクト
     * @param SyL_Data データオブジェクト
     */
    public function execute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $data->set('current_database', $context->getUser()->crudCurrentDatabase);
        $this->addBreadcrumbs($data, 'SQL実行', '');
    }

    /**
     * Ajax呼び出し時にコールされるアクションメソッド
     * 
     * @param SyL_ContextAbstract フィールド情報管理オブジェクト
     * @param SyL_Data データオブジェクト
     */
    protected function executeAjax(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $sql = $data->get('sql');

        $result = array();

        if (($sql === '') || ($sql === null)) {
            $result['valid'] = false;
            return;
        }


        $rows = array();
        $page_info = array();

        $result = array();
        $result['valid'] = true;
        $result['rows'] = $rows;
        $result['page_info'] = $page_info;
        $context->setViewJson($result);
    }

}
