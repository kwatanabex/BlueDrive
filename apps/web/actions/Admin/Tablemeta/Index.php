<?php
/**
 * Index クラス
 */
class Admin_Tablemeta_Index extends AppAdminTablemetaAction
{
    /**
     * デフォルトアクション処理
     *
     * @param SyL_ContextAbstract コンテキストオブジェクト
     * @param SyL_Data データオブジェクト
     */
    public function execute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $this->addBreadcrumbs($data, 'テーブルメタ', '');
    }

    /**
     * Ajax呼び出し時にコールされるアクションメソッド
     * 
     * @param SyL_ContextAbstract フィールド情報管理オブジェクト
     * @param SyL_Data データオブジェクト
     */
    protected function executeAjax(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $current_database = $data->get('database');
        if (!$current_database) {
            throw new BlueDriveAjaxException('データベースが選択されていません');
        }

        $values = array();
        foreach (self::getCrudDatabases() as $name => $database) {
            if ($name == $current_database) {
                $values = $database;
                break;
            }
        }
        if (count($values) == 0) {
            throw new BlueDriveAjaxException('データベースが正しくありません');
        }

        $user = $context->getUser();
        $user->crudCurrentDatabase = $current_database;
        $user->crudCurrentOutputDir = $values['outputDir'];
        $user->crudCurrentConnectionString = $values['connectionString'];
        $context->setUser($user);

        $context->setViewJson(array());
    }
}
