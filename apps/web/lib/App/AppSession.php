<?php

class AppSession extends SyL_SessionAbstract
{
    /**
     * セッション名
     *
     * @var string
     */
    protected $name = 'bdsid';
    /**
     * クッキーパス
     *
     * @var string
     */
    protected $cookie_path = '/';

    /**
     * セッションを開始直後の処理
     */
    protected function startAfter()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            // GETの場合のみ、セッションIDを変更する
            session_regenerate_id(true);
        }
    }
}
