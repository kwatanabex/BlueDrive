<?php
SyL_Loader::userLib('Bl.Abstract');
SyL_Loader::userLib('Bl.FileStorageType');

/**
 * Index クラス
 */
class Admin_System_Area_Index extends AppAdminSystemAction
{
    /**
     * デフォルトアクション処理
     *
     * @param SyL_ContextAbstract コンテキストオブジェクト
     * @param SyL_Data データオブジェクト
     */
    public function execute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $this->addBreadcrumbs($data, 'ファイル保存領域管理', '');
        $data->set('storage_type_list', BlFileStorageType::getList());
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
            $storage_type = $data->get('storage_type');
            $page = $data->get('page');
            if (!is_numeric($page) || ($page < 1)) {
                $page = 1;
            }

            list($rows, $page_info) = BlAbstract::createInstance('file')->getFileAreas($storage_type, $page);

            $result['valid'] = true;
            $result['rows'] = $rows;
            $result['page_info'] = $page_info;
            break;

        case 'remove':
            $id = $data->get('file_area_id');
            if (!is_numeric($id) || ($id < 1)) {
                $result['message'] = "パラメータエラーが発生しました";
                $context->setViewJson($result);
                return;
            }

            BlAbstract::createInstance('file')->removeFileArea($id);

            $result['valid'] = true;
            break;
        }

        $context->setViewJson($result);
    }

}
