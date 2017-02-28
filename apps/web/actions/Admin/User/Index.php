<?php
/**
 * Index クラス
 */
class Admin_User_Index extends AppAdminUserAction
{
    /**
     * デフォルトアクション処理
     *
     * @param SyL_ContextAbstract コンテキストオブジェクト
     * @param SyL_Data データオブジェクト
     */
    public function execute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $this->addBreadcrumbs($data, 'ユーザー', '');
    }


}
