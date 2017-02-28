<?php
/**
 * Index クラス
 */
class Admin_Table_Index extends AppAdminTableAction
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
        $crud_names = $data->get('crud_display_list');

        $crud_summary = array();
        foreach ($crud_names as $name => $display) {
            $config = CrudFactory::createInstance($name, SyL_CrudConfigAbstract::CRUD_TYPE_LST);
            $crud_summary[$name] = array(
              'name'    => $display,
              'description' => $config->getDescription()
            );
        }

        $this->addBreadcrumbs($data, 'テーブル', '');

        $data->set('crud_description', $crud_summary);
    }


}
