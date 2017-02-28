<?php
/**
 * Index クラス
 */
class Admin_Table_AtomRouter extends AppAdminTableAction
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

        $action_path = dirname(__FILE__) . '/_Template/Atom/' . $crud_name . '.php';

        if (is_file($action_path)) {
            include_once $action_path;

            $class_name = 'Table_Template_Atom_' . $crud_name;
            $action = new $class_name();
            $action->process($context, $data);
        } else {
            throw new SyL_ResponseNotFoundException('crud atom file not found');
        }
    }
}
