<?php

class AppUser extends SyL_UserAbstract
{
    protected $properties = array(
        'adminFlag' => false, // 管理者ユーザー
        'accessRealms' => array(), // アクセス許可範囲
        'crudCurrentDatabase' => null, // 現在の管理対象データベース
        'crudCurrentOutputDir' => null, // 現在のCRUD生成ディレクトリ
        'crudCurrentConnectionString' => null, // 現在のCRUDデータベース接続文字列
    );
}
