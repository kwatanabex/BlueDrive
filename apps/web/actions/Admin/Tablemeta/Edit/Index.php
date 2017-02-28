<?php
/**
 * Index クラス
 */
class Admin_Tablemeta_Edit_Index extends AppAdminTablemetaAction
{
    protected $current_title = 'メタデータ編集';

    /**
     * デフォルトアクション処理
     *
     * @param SyL_ContextAbstract コンテキストオブジェクト
     * @param SyL_Data データオブジェクト
     */
    public function execute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $root_dir = $context->getUser()->crudCurrentOutputDir;

        $crud_config_dir = $root_dir . '/Crud/Config';
        $crud_access_dir = $root_dir . '/Crud/Access';

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

        $access_list = array();
        $access_class_list = array();
        if (is_dir($crud_access_dir)) {
            foreach (scandir($crud_access_dir) as $file) {
                if (is_file($crud_access_dir . '/' . $file)) {
                    if (preg_match('/^(CrudAccess)(.+)\.php$/', $file, $matches)) {
                        if ($matches[2] != 'Abstract') {
                            $access_list[] = $matches[2];
                            $access_class_list[] = $matches[1] . $matches[2];
                        }
                    }
                }
            }
        }

        $dao_entity_dir = $root_dir . '/Dao/Entity';

        $dao_entity_list = array();
        if (is_dir($dao_entity_dir)) {
            foreach (scandir($dao_entity_dir) as $file) {
                if (is_file($dao_entity_dir . '/' . $file)) {
                    if (preg_match('/^(DaoEntity.+)\.php$/', $file, $matches)) {
                        $dao_entity_list[] = $matches[1];
                    }
                }
            }
        }

        $table_name = $data->get('name');
        if (!in_array($table_name, $config_list)) {
            $table_name = '';
        }

        $this->addBreadcrumbs($data, 'メタデータの編集', '');

        $data->set('table_name', $table_name);
        $data->set('config_list', $config_list);
        $data->set('access_list', $access_list);
        $data->set('access_class_list', $access_class_list);
        $data->set('dao_entity_list', $dao_entity_list);
    }

    /**
     * Ajax呼び出し時にコールされるアクションメソッド
     * 
     * @param SyL_ContextAbstract フィールド情報管理オブジェクト
     * @param SyL_Data データオブジェクト
     */
    protected function executeAjax(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $root_dir = $context->getUser()->crudCurrentOutputDir;

        $crud_config_dir = $root_dir . '/Crud/Config';
        $crud_access_dir = $root_dir . '/Crud/Access';

        $mode = $data->get('mode');
        $type = $data->get('type');
        $table = $data->get('table');
        $code = $data->get('code');

        $result = array();
        $result['valid'] = false;

        $file = '';
        $crud_dir = '';
        $crud_test_file = '';
        switch ($type) {
        case '1':
            $file = $crud_config_dir . '/CrudConfig' . $table . '.php';
            $crud_dir = $crud_config_dir;
            $crud_test_file = $root_dir . '/Crud/crud_config_test.php';
            break;
        case '2':
            $file = $crud_access_dir . '/CrudAccess' . $table . '.php';
            $crud_dir = $crud_access_dir;
            $crud_test_file = $root_dir . '/Crud/crud_access_test.php';
            break;
        default:
            $result['message'] = 'パラメータが正しくありません';
            $context->setViewJson($result);
            return;
        }

        if (!is_file($file)) {
            $result['message'] = '指定されたメタデータのファイルが見つかりません';
            $context->setViewJson($result);
            return;
        }

        switch ($mode) {
        case 'read':
            $result['code'] = file_get_contents($file);
            $result['valid'] = true;
            break;

        case 'write':
            $exist = true;
            $tmp_file = '';
            $tmp_filename = '';
            for ($i=0; $i<3; $i++) {
                $tmp_filename = '__' . md5(uniqid(rand(), true));
                $tmp_file = $crud_dir . '/' . $tmp_filename;
                if (!file_exists($tmp_file)) {
                    $exist = false;
                    break;
                }
            }
            if ($exist) {
                $result['message'] = '一時ファイルの作成ができませんでした';
                break;
            }

            file_put_contents($tmp_file, $code);
            // 一時ファイル削除用
            register_shutdown_function(create_function('', 'if (file_exists("' . $tmp_file . '")) { unlink("' . $tmp_file . '"); }'));

            $php_bin = SyL_Config::get('PHP_BIN');
            $result['message'] = shell_exec(sprintf('%s %s "%s" "%s" "%s"', $php_bin, $crud_test_file, SYL_DIR, $table, $tmp_filename));
            if (substr($result['message'], -10) != 'successed!') {
                break;
            }

            rename($tmp_file, $file);

            $result['valid'] = true;
            break;

        default:
            $result['valid'] = false;
            $result['message'] = 'パラメータが不正です';
        }

        $context->setViewJson($result);
    }
}
