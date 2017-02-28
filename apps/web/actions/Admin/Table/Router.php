<?php
/**
 * Index クラス
 */
class Admin_Table_Router extends AppAdminTableAction
{
    public function preExecute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        // CRUD, DAO用の接続文字列
        define('DAO_ACCESS_CONNECTION_STRING', $context->getUser()->crudCurrentConnectionString);

        parent::preExecute($context, $data);
    }

    /**
     * デフォルトアクション処理
     *
     * @param SyL_ContextAbstract コンテキストオブジェクト
     * @param SyL_Data データオブジェクト
     */
    public function execute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $crud_name = ucfirst($context->getPathMatch(2));

        $action_path = dirname(__FILE__) . '/_Template/' . $crud_name . '.php';
        $template_path = '/Admin/Table/_Template/' . $crud_name . '.html';

        if (is_file($action_path)) {
            include_once $action_path;

            $class_name = 'Admin_Table_Template_' . $crud_name;
            $action = new $class_name();
            $action->process($context, $data);
            $context->getRouter()->setTemplateFile($template_path);
        } else {
            throw new SyL_ResponseNotFoundException('crud file not found');
        }
    }

    /**
     * Ajax呼び出し時にコールされるアクションメソッド
     * 
     * @param SyL_ContextAbstract フィールド情報管理オブジェクト
     * @param SyL_Data データオブジェクト
     */
    protected function executeAjax(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $this->execute($context, $data);
    }

}
