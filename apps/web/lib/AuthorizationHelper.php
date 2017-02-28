<?php
SyL_Loader::userLib('App.User');

/**
 * �F�w���p�[�N���X
 */
class AuthorizationHelper
{
    private static $user = null;

    /**
     * ���[�U�[����������
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
     * �Ώۂ�URL��������Ă��邩�m�F����
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
