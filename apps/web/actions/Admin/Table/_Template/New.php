<?php
/**
 * Index クラス
 */
class Admin_Table_Template_New extends AppAdminTableAction
{
    /**
     * デフォルトアクション処理
     *
     * @param SyL_ContextAbstract コンテキストオブジェクト
     * @param SyL_Data データオブジェクト
     */
    public function execute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $config = $this->createCrudConfig($context, $data, SyL_CrudConfigAbstract::CRUD_TYPE_NEW);
        if (!$config->enableCrud()) {
            throw new BlueDriveAuthorizationException('新規登録権限がありません');
        }

        $page   = SyL_CrudPageAbstract::createInstance($config);
        $input_pages = $page->getInputPages();

        $form_types = array();
        $page_descriptions = array();
        foreach ($input_pages as $page_id => $values) {
            $form_types[$page_id] = $values['type'];
            $page_descriptions[$page_id] = array('header' => $values['header'], 'footer' => $values['footer']);
        }

        $this->addBreadcrumbs($data, $page->getName(), $config->getUrlLst());
        $this->addBreadcrumbs($data, '新規登録', '');

        $data->set('form_type_input', SyL_CrudConfigAbstract::FORM_TYPE_INPUT);
        $data->set('form_type_confirm', SyL_CrudConfigAbstract::FORM_TYPE_CONFIRM);
        $data->set('form_type_complete', SyL_CrudConfigAbstract::FORM_TYPE_COMPLETE);

        $data->set('title', $page->getName());
        $data->set('description', $page->getDescription());

        $data->set('form_types', $form_types);
        $data->set('page_descriptions', $page_descriptions);

        $data->set('elements', $page->getElements());
        $data->set('forms', $page->getFormViews());

        $data->set('url_lst', $config->getUrlLst());
    }

    /**
     * Ajax呼び出し時にコールされるアクションメソッド
     * 
     * @param SyL_ContextAbstract フィールド情報管理オブジェクト
     * @param SyL_Data データオブジェクト
     */
    protected function executeAjax(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $config = $this->createCrudConfig($context, $data, SyL_CrudConfigAbstract::CRUD_TYPE_NEW);
        if (!$config->enableCrud()) {
            throw new BlueDriveAjaxException('新規登録権限がありません');
        }

        $page = SyL_CrudPageAbstract::createInstance($config);
        $input_pages = $page->getInputPages();

        $result = array();
        $result['valid'] = false;
        $result['messages'] = array();

        $current_page_id = $data->get('__current_page_id');
        if (!isset($input_pages[$current_page_id])) {
            // ページが存在しないエラー
            $result['messages'][] = 'invalid page id';
            $context->setViewJson($result);
            return;
        }

        try {
            // バリデーション
            $page->validate($current_page_id);
            if ($input_pages[$current_page_id]['type'] == SyL_CrudConfigAbstract::FORM_TYPE_CONFIRM) {
                // 登録
                $page->execute();
            }
            $result['valid'] = true;
        } catch (SyL_CrudValidateException $e) {
            $result['messages'] = $e->getMessages();
        } catch (SyL_DbSqlExecuteException $e) {
            $result['messages'][] = $e->getMessage();
        }

        $context->setViewJson($result);
    }
}
