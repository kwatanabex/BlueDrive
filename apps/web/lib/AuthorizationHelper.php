<?php
SyL_Loader::userLib('App.User');

/**
 * 認可ヘルパークラス
 */
class AuthorizationHelper
{
    private static $user = null;

    /**
     * ユーザー情報を初期化
     */
    public static function initialize(AppUser $user)
    {
        if (self::$user == null) {
            self::$user = $user;
        } else {
            throw new SyL_InvalidOperationException('AuthorizationHelper already initialize');
        }
    }

    /**
     * 対象のURLが許可されているか確認する
     */
    public static function enable($url)
    {
        if (self::$user == null) {
            throw new SyL_InvalidOperationException('AuthorizationHelper already initialize');
        }

        if (self::$user->adminFlag) {
            return true;
        }

        $match = false;
        foreach (self::$user->accessRealms as $realm) {
            if (preg_match('|^' . $realm . '$|i', $url)) {
                $match = true;
                break;
            }
        }
        return $match;
    }
}
