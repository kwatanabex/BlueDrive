<?php
/**
 * Index クラス
 */
class Admin_Tablemeta_Create_Index extends AppAdminTablemetaAction
{
    /**
     * デフォルトアクション処理
     *
     * @param SyL_ContextAbstract コンテキストオブジェクト
     * @param SyL_Data データオブジェクト
     */
    public function execute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $root_dir = $context->getUser()->crudCurrentOutputDir;
        $connection_string = $context->getUser()->crudCurrentConnectionString;

        $conn = $context->getDB($connection_string);
        $schema = $conn->getSchema();
        $tables = $schema->getTables();
        $conn->close();

        $this->addBreadcrumbs($data, 'メタデータの作成', '');

        $data->set('root_dir', $root_dir);
        $data->set('tables', $tables);
    }

    /**
     * Ajax呼び出し時にコールされるアクションメソッド
     * 
     * @param SyL_ContextAbstract フィールド情報管理オブジェクト
     * @param SyL_Data データオブジェクト
     */
    protected function executeAjax(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $current_database = $context->getUser()->crudCurrentDatabase;
        $root_dir = $context->getUser()->crudCurrentOutputDir;

        $table_name = $data->get('table');
        $crud_create = $data->get('crud_create');
        $crud_name = $data->get('crud_name');

        if (!is_dir($root_dir)) {
            mkdir($root_dir);
        }

        $php_dao_bin = SyL_Config::get('PHP_DAO_CREATE_BIN');
        $php_crud_bin = SyL_Config::get('PHP_CRUD_CREATE_BIN');

        $crud_config_file = $root_dir . '/Crud/Config/CrudConfig' . ucfirst($crud_name) . '.php';
        $crud_access_file = $root_dir . '/Crud/Access/CrudAccess' . ucfirst($crud_name) . '.php';

        $result = array();
        $result['valid'] = false;
        $result['message'] = null;

        // CRUDファイルの確認
        if ($crud_create == '1') {
            if (file_exists($crud_config_file)) {
                $result['message'] = "CRUDファイルが既に存在しています。({$crud_config_file})";
                $context->setViewJson($result);
                return;
            }
            if (file_exists($crud_access_file)) {
                $result['message'] = "CRUDファイルが既に存在しています。({$crud_access_file})";
                $context->setViewJson($result);
                return;
            }
        }

        // DAOファイル作成
        $command = sprintf($php_dao_bin, SYL_PROJECT_DIR, $table_name, $current_database);
        $exec_result = shell_exec($command);
        if (!$exec_result) {
            $result['message'] = "DAOファイル生成時にエラーが発生しました。コマンド [{$command}]";
            $context->setViewJson($result);
            return;
        }
        $result['dao'] = trim($exec_result);
        if (substr($result['dao'], -10) != 'successed!') {
            $result['message'] = "DAOファイル生成時にエラーが発生しました。{$result['dao']}";
            $context->setViewJson($result);
            return;
        }

        if ($crud_create == '1') {
            $command = sprintf($php_crud_bin, SYL_PROJECT_DIR, $table_name, $crud_name, $current_database);
            $exec_result = shell_exec($command);
            if (!$exec_result) {
                $result['message'] = "CRUDファイル生成時にエラーが発生しました。コマンド [{$command}]。DAOファイルは生成されています。";
                $context->setViewJson($result);
                return;
            }
            $result['crud'] = trim($exec_result);
            if (substr($result['crud'], -10) != 'successed!') {
                $result['message'] = "CRUDファイル生成時にエラーが発生しました。{$result['crud']}。DAOファイルは生成されています。";
                $context->setViewJson($result);
                return;
            }

            $crud_display_list_file = $root_dir . SyL_Config::get('CRUD_DISPLAY_LIST_FILE');
            if (!file_exists($crud_display_list_file)) {
                self::generateInitializeDisplayList($crud_display_list_file);
            }

            $crud_config_test_file = $root_dir . '/Crud/crud_config_test.php';
            if (!file_exists($crud_config_test_file)) {
                self::generateCrudConfigTest($crud_config_test_file);
            }

            $crud_access_test_file = $root_dir . '/Crud/crud_access_test.php';
            if (!file_exists($crud_access_test_file)) {
                self::generateCrudAccessTest($crud_access_test_file);
            }
        }

        $result['valid'] = true;
        $context->setViewJson($result);
    }


    private static function generateInitializeDisplayList($crud_display_list_file)
    {
        $output = <<<PHP
<?php
class CrudDisplayList{public static function getList(){return array();}}
PHP;
        file_put_contents($crud_display_list_file, $output);
    }

    private static function generateCrudConfigTest($crud_config_test_file)
    {
        $output = <<<PHP
<?php
error_reporting(E_ALL | E_STRICT);
include_once 'CrudFactory.php';
\$current_class = 'CrudConfig' . ucfirst(\$argv[2]);
try {
CrudFactory::createInstance(\$argv[2], SyL_CrudConfigAbstract::CRUD_TYPE_LST, \$argv[3]);
} catch (SyL_DbException \$e) {
}
if (class_exists(\$current_class)) {
echo 'successed!';
}
PHP;
        file_put_contents($crud_config_test_file, $output);
    }

    private static function generateCrudAccessTest($crud_access_test_file)
    {
        $output = <<<PHP
<?php
error_reporting(E_ALL | E_STRICT);
\$parent2_file = \$argv[1] . '/framework/Lib/Crud/SyL_CrudDbDaoAccessAbstract.php';
\$parent_file = dirname(__FILE__) . '/Access/CrudAccessAbstract.php';
\$current_class = 'CrudAccess' . ucfirst(\$argv[2]);
\$current_file = dirname(__FILE__) . '/Access/' . \$argv[3];
include_once \$parent2_file;
include_once \$parent_file;
include_once \$current_file;
if (class_exists(\$current_class)) {
echo 'successed!';
}
PHP;
        file_put_contents($crud_access_test_file, $output);
    }
}
