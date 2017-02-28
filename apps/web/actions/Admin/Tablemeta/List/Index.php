<?php
/**
 * Index クラス
 */
class Admin_Tablemeta_List_Index extends AppAdminTablemetaAction
{
    protected $current_title = 'テーブルリスト編集';

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
        $crud_display_list_file = $root_dir . SyL_Config::get('CRUD_DISPLAY_LIST_FILE');

        $exist = is_file($crud_display_list_file) && is_dir($crud_config_dir);
        $display_list = array();
        $config_list = array();
        if ($exist) {
            include_once $crud_display_list_file;
            $display_list = CrudDisplayList::getList();
            $config_list = $this->getConfigList($crud_config_dir);
        }

        $breadcrumbs = $data->get('App.Admin.breadcrumbs');
        $breadcrumbs['テーブルリストの編集'] = '';
        $data->set('App.Admin.breadcrumbs', $breadcrumbs);

        $data->set('exist', $exist);
        $data->set('display_list', $display_list);
        $data->set('config_list', $config_list);
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
        $config_list = $this->getConfigList($crud_config_dir);

        $tables = $data->get('tables');
        $crud_list = array();
        foreach (explode("\t", $tables) as $table) {
            $table = trim($table);
            if ($table) {
                if (in_array($table, $config_list)) {
                    $crud_list[] = $table;
                }
            }
        }

        $crud_display_list_file = $root_dir . SyL_Config::get('CRUD_DISPLAY_LIST_FILE');
        self::generateInitializeDisplayList($crud_display_list_file, $crud_list);

        $result = array();
        $result['valid'] = true;
        $context->setViewJson($result);
    }


    private function getConfigList($crud_config_dir)
    {
        $config_list = array();
        foreach (scandir($crud_config_dir) as $file) {
            if (is_file($crud_config_dir . '/' . $file)) {
                if (preg_match('/^CrudConfig(.+)\.php$/', $file, $matches)) {
                    $config_list[] = $matches[1];
                }
            }
        }
        return $config_list;
    }

    private static function generateInitializeDisplayList($crud_display_list_file, array $crud_list)
    {
        $export_tables = var_export($crud_list, true);

        $output = <<<PHP
<?php
class CrudDisplayList{public static function getList(){return {$export_tables};}}
PHP;
        file_put_contents($crud_display_list_file, $output);
    }
}
