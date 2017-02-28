<?php
/**
 * Index クラス
 */
class Admin_Tablemeta_Remove_Index extends AppAdminTablemetaAction
{
    /**
     * デフォルトアクション処理
     *
     * @param SyL_ContextAbstract コンテキストオブジェクト
     * @param SyL_Data データオブジェクト
     */
    public function execute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $metadata_dir = $context->getUser()->crudCurrentOutputDir;

        $breadcrumbs = $data->get('App.Admin.breadcrumbs');
        $breadcrumbs['メタデータの削除'] = '';
        $data->set('App.Admin.breadcrumbs', $breadcrumbs);

        $data->set('config_list', self::getCrudList($metadata_dir));
    }

    /**
     * Ajax呼び出し時にコールされるアクションメソッド
     * 
     * @param SyL_ContextAbstract フィールド情報管理オブジェクト
     * @param SyL_Data データオブジェクト
     */
    protected function executeAjax(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $metadata_dir = $context->getUser()->crudCurrentOutputDir;

        $type = $data->get('type');

        $result = array();
        $result['valid'] = false;
        switch ($type) {
        case 'remove':
            $crud_list = self::getCrudList($metadata_dir);

            $tables = $data->get('tables');
            $remove_list = array();
            foreach (explode("\t", $tables) as $table) {
                $table = trim($table);
                if ($table) {
                    if (in_array($table, $crud_list)) {
                        $remove_list[] = $table;
                    }
                }
            }

            if (count($remove_list) == 0) {
                $result['message'] = 'メタデータが選択されていません';
                break;
            }

            $config_dir = $metadata_dir . '/Crud/Config';
            $access_dir = $metadata_dir . '/Crud/Access';

            if (!is_dir($config_dir) || !is_writable($config_dir)) {
                $result['message'] = "設定ファイルディレクトリ（{$config_dir}）がありません";
                break;
            }

            if (!is_dir($access_dir) || !is_writable($access_dir)) {
                $result['message'] = "アクセスファイルディレクトリ（{$access_dir}）がありません";
                break;
            }

            $config_files = array();
            $access_files = array();
            foreach ($remove_list as $remove) {
                $config_file = $config_dir . '/CrudConfig' . $remove . '.php';
                $access_file = $access_dir . '/CrudAccess' . $remove . '.php';
                if (!is_file($config_file)) {
                    $result['message'] = "設定ファイル（{$config_file}）がありません";
                    break;
                }
                if (!is_file($access_file)) {
                    $result['message'] = "アクセスファイル（{$access_file}）がありません";
                    break;
                }
                $config_files[] = $config_file;
                $access_files[] = $access_file;
            }

            foreach ($config_files as $config_file) {
                unlink($config_file);
            }
            foreach ($access_files as $access_file) {
                unlink($access_file);
            }

            $crud_display_list_file = $metadata_dir . SyL_Config::get('CRUD_DISPLAY_LIST_FILE');
            include_once $crud_display_list_file;
            $crud_display_list = CrudDisplayList::getList();

            $crud_display_list = array_diff($crud_display_list, $remove_list);

            self::generateDisplayList($crud_display_list_file, $crud_display_list);

            $result['valid'] = true;
            break;

        case 'clear':
            $dirs = array();
            $dirs[] = $metadata_dir;
            $dirs[] = $metadata_dir . '/Crud';
            $dirs[] = $metadata_dir . '/Dao';
            $dirs[] = $metadata_dir . '/Crud/Access';
            $dirs[] = $metadata_dir . '/Crud/Config';
            $dirs[] = $metadata_dir . '/Dao/Access';
            $dirs[] = $metadata_dir . '/Dao/Entity';

            $dirs = array_reverse($dirs);

            foreach ($dirs as $dir) {
                if (is_dir($dir) && !is_writable($dir)) {
                    $result['message'] = "ディレクトリ ({$dir}) のファイルが削除できません。WEBサーバーの実行ユーザーをご確認ください";
                    break;
                }
            }

            foreach ($dirs as $dir) {
                self::removeDirectory($dir);
            }

            $result['valid'] = true;
            break;
        }

        $context->setViewJson($result);
    }



    private static function removeDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        foreach (scandir($dir) as $file) {
            if (($file == '.') || ($file == '..')) {
                continue;
            }

            unlink($dir . '/' . $file);
        }
        rmdir($dir);
    }

    private static function getCrudList($metadata_dir)
    {
        $crud_config_dir = $metadata_dir . '/Crud/Config';
        $config_list = array();
        if (is_dir($crud_config_dir)) {
            foreach (scandir($crud_config_dir) as $file) {
                if (is_file($crud_config_dir . '/' . $file)) {
                    if (preg_match('/^CrudConfig(.+)\.php$/', $file, $matches)) {
                        $config_list[] = $matches[1];
                    }
                }
            }
        }

        return $config_list;
    }

    private static function generateDisplayList($crud_display_list_file, array $crud_display_list)
    {
        $code = var_export($crud_display_list, true);
        $output = <<<PHP
<?php
class CrudDisplayList{public static function getList(){return {$code};}}
PHP;
        file_put_contents($crud_display_list_file, $output);
    }
}
