<?php
SyL_Loader::core('Filter.Abstract');
SyL_Loader::userLib('AuthorizationHelper');
SyL_Loader::userLib('BlueDriveException');
SyL_Loader::userLib('Data.SessionSystem');

/**
 * 認証／認可フィルタクラス
 */
class FilterAuthorization extends SyL_FilterAbstract
{
    /**
     * アクション実行前フィルタメソッド
     *
     * @param SyL_ContextAbstract フィールド情報管理オブジェクト
     * @param SyL_Data データオブジェクト
     * @param array フィルタパラメータ
     */
    protected function preActionProcess(SyL_ContextAbstract $context, SyL_Data $data, array $paremeters)
    {
        // 認証チェック
        $user = $context->getUser();
        if (!$user) {
            SyL_Logger::warn("session authentication error. user session not found.");
            throw new BlueDriveAuthorizationException('認証されていません');
        }

        $system_data = $context->getSession()->get(DataSessionSystem::SESSION_KEY);
        if (!$system_data) {
            SyL_Logger::warn("session authentication error. system_data session not found.");
            throw new BlueDriveAuthorizationException('認証されていません');
        }

        AuthorizationHelper::initialize($user);

        // 認可チェック
        $action_file = $context->getRouter()->getActionFile();
        if (!AuthorizationHelper::enable($action_file)) {
            SyL_Logger::warn("session authorization error. [{$action_file}] access denied.");
            throw new BlueDriveAuthorizationException('認可されていません');
        }
    }
}
