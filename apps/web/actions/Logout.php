<?php
/**
 * Login クラス
 */
class Logout extends AppAction
{
    /**
     * デフォルトアクション処理
     *
     * @param SyL_ContextAbstract コンテキストオブジェクト
     * @param SyL_Data データオブジェクト
     */
    public function execute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $session = $context->getSession();
        $session->close(true);
    }
}
