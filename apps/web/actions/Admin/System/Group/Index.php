<?php
SyL_Loader::userLib('Bl.Abstract');

/**
 * Index クラス
 */
class Admin_System_Group_Index extends AppAdminSystemAction
{
    /**
     * デフォルトアクション処理
     *
     * @param SyL_ContextAbstract コンテキストオブジェクト
     * @param SyL_Data データオブジェクト
     */
    public function execute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $this->addBreadcrumbs($data, 'グループ管理', '');
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
            $group_name = $data->get('group_name');
            $page  = $data->get('page');

            if (!is_numeric($page) || ($page < 1)) {
                $page = 1;
            }

            list($rows, $page_info) = BlAbstract::createInstance('group')->getGroups($group_name, $page);

            $result['valid'] = true;
            $result['rows'] = $rows;
            $result['page_info'] = $page_info;
            break;

        case 'remove':
            $user_id = $data->get('user_id');
            if (!is_numeric($user_id) || ($user_id < 1)) {
                $result['message'] = "パラメータエラーが発生しました";
                $context->setViewJson($result);
                return;
            }

            $i_id = $context->getUser()->getId();
            BlAbstract::createInstance('user')->removeUser($user_id, $i_id);

            $result['valid'] = true;
            break;
        }

        $context->setViewJson($result);
    }

}
