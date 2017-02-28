<?php
require_once 'Index.php';
/**
 * Index クラス
 */
class Admin_File_Manager_Dialog extends Admin_File_Manager_Index
{
    /**
     * デフォルトアクション処理
     *
     * @param SyL_ContextAbstract コンテキストオブジェクト
     * @param SyL_Data データオブジェクト
     */
    public function execute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $name = $data->get('name');
        if (!$name) {
            throw new BlueDriveException('ファイル領域が選択されていません');
        }

        parent::execute($context, $data);

        $data->set('area_view', false);
        $data->set('current_area_name', $name);

        $context->getRouter()->setLayoutName('admin_plain');
        $context->getRouter()->setTemplateFile('/Admin/File/Manager/Index.html');
    }
}
