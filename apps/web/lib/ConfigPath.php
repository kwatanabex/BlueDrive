<?php
class ConfigPath
{
    public static function getFormUserInitializeFile() { return SYL_APP_CONFIG_DIR . '/form/user_initialize.xml'; }
    public static function getFormUserNewFile() { return SYL_APP_CONFIG_DIR . '/form/user_new.xml'; }
    public static function getFormUserEditFile() { return SYL_APP_CONFIG_DIR . '/form/user_edit.xml'; }
    public static function getFormUserPasswordFile() { return SYL_APP_CONFIG_DIR . '/form/user_password.xml'; }
    public static function getFormUserEditSelfFile() { return SYL_APP_CONFIG_DIR . '/form/user_edit_self.xml'; }
    public static function getFormUserPasswordSelfFile() { return SYL_APP_CONFIG_DIR . '/form/user_password_self.xml'; }

    public static function getFormGroupNewFile() { return SYL_APP_CONFIG_DIR . '/form/group_new.xml'; }
    public static function getFormGroupEditFile() { return SYL_APP_CONFIG_DIR . '/form/group_edit.xml'; }

    public static function getFormRealmNewFile() { return SYL_APP_CONFIG_DIR . '/form/realm_new.xml'; }
    public static function getFormRealmEditFile() { return SYL_APP_CONFIG_DIR . '/form/realm_edit.xml'; }

    public static function getFormFileAreaNewFile() { return SYL_APP_CONFIG_DIR . '/form/file_area_new.xml'; }
    public static function getFormFileAreaEditFile() { return SYL_APP_CONFIG_DIR . '/form/file_area_edit.xml'; }
}
