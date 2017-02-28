<?php
require_once 'AppAdminAction.php';

abstract class AppAdminTableAction extends AppAdminAction
{
    public function preExecute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        parent::preExecute($context, $data);
        $this->addBreadcrumbs($data, 'テーブル', $data->get('App.Admin.url_table_base'));

        $crud_outpur_dir = $context->getUser()->crudCurrentOutputDir;
        $crud_display_list_file = $crud_outpur_dir . SyL_Config::get('CRUD_DISPLAY_LIST_FILE');
        if (!is_file($crud_display_list_file)) {
            throw new SyL_FileNotFoundException("CRUD display list file not found ({$crud_display_list_file})");
        }

        $crud_factory_file = $crud_outpur_dir . '/Crud/CrudFactory.php';
        if (!is_file($crud_factory_file)) {
            throw new SyL_FileNotFoundException("CRUD factory file not found ({$crud_factory_file})");
        }

        include_once $crud_display_list_file;
        include_once $crud_factory_file;

        $crud_names = array();
        foreach (CrudDisplayList::getList() as $crud_name) {
            $config = CrudFactory::createInstance($crud_name, SyL_CrudConfigAbstract::CRUD_TYPE_LST);
            if ($config->enableCrud()) {
                // 一覧表示権限チェック
                $crud_names[$crud_name] = $config->getName();
            }
        }

        $data->set('crud_display_list', $crud_names);
        $data->set('App.Admin.Action', strtolower($context->getPathMatch(1)));
    }

    /**
     * CRUD設定オブジェクトを作成する
     *
     * @param SyL_ContextAbstract コンテキストオブジェクト
     * @param string CRUDタイプ
     * @return SyL_CrudConfigAbstract CRUD設定オブジェクト
     */
    protected function createCrudConfig(SyL_ContextAbstract $context, SyL_Data $data, $crud_type, $crud_name=null)
    {
        if (!$crud_name) {
            $crud_name = $context->getPathMatch(1);
        }

        $config = null;
        try {
            $config = CrudFactory::createInstance($crud_name, $crud_type);
        } catch (SyL_CrudNotFoundException $e) {
            throw new SyL_ResponseNotFoundException($e->getMessage(), $e, $e->getCode());
        }

        $config->buildElements();

        $url_admin_base = $data->get('App.url_admin_base');
        $url_table = $url_admin_base . 'table/' . $crud_name . '/';
        $config->setBaseUrl($url_table);

        // 個別フォームクラスロードディレクトリ
        $search_dir = SYL_APP_LIB_DIR . '/Form';
        SyL_FormElementAbstract::addSearchDir($search_dir);

        return $config;
    }

}
