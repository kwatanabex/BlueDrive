<?php
/**
 * Index クラス
 */
class Admin_Table_Template_Edt extends AppAdminTableAction
{
    /**
     * デフォルトアクション処理
     *
     * @param SyL_ContextAbstract コンテキストオブジェクト
     * @param SyL_Data データオブジェクト
     */
    public function execute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $id = $data->get('__id');

        $config = $this->createCrudConfig($context, $data, SyL_CrudConfigAbstract::CRUD_TYPE_EDT);
        if (!$config->enableCrud()) {
            throw new BlueDriveAuthorizationException('編集権限がありません');
        }

        $page   = SyL_CrudPageAbstract::createInstance($config);
        $page->setId($id);

        $input_pages = $page->getInputPages();

        $form_types = array();
        $page_descriptions = array();
        foreach ($input_pages as $page_id => $values) {
            $form_types[$page_id] = $values['type'];
            $page_descriptions[$page_id] = array('header' => $values['header'], 'footer' => $values['footer']);
        }

        $this->addBreadcrumbs($data, $page->getName(), $config->getUrlLst());
        $this->addBreadcrumbs($data, '編集', '');

        $data->set('form_type_input', SyL_CrudConfigAbstract::FORM_TYPE_INPUT);
        $data->set('form_type_confirm', SyL_CrudConfigAbstract::FORM_TYPE_CONFIRM);
        $data->set('form_type_complete', SyL_CrudConfigAbstract::FORM_TYPE_COMPLETE);

        $data->set('id', $id);

        $data->set('title', $page->getName());
        $data->set('description', $page->getDescription());

        $data->set('form_types', $form_types);
        $data->set('page_descriptions', $page_descriptions);

        $data->set('elements', $page->getElements());
        $data->set('forms', $page->getFormViews());

        $data->set('url_lst', $config->getUrlLst());
        $data->set('url_vew', $config->getUrlVew());
    }

    /**
     * Ajax呼び出し時にコールされるアクションメソッド
     * 
     * @param SyL_ContextAbstract フィールド情報管理オブジェクト
     * @param SyL_Data データオブジェクト
     */
    protected function executeAjax(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $id = $data->get('__id');

        $config = $this->createCrudConfig($context, $data, SyL_CrudConfigAbstract::CRUD_TYPE_EDT);
        if (!$config->enableCrud()) {
            throw new BlueDriveAjaxException('編集権限がありません');
        }

        $page   = SyL_CrudPageAbstract::createInstance($config);
        $page->setId($id);

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

            switch ($input_pages[$current_page_id]['type']) {
            case SyL_CrudConfigAbstract::FORM_TYPE_INPUT:
                $page_id = (int)$current_page_id + 1;
                if ($input_pages[$page_id]['type'] == SyL_CrudConfigAbstract::FORM_TYPE_CONFIRM) {
                    $result['row'] = $page->getRecord();
                }
                break;
            case SyL_CrudConfigAbstract::FORM_TYPE_CONFIRM:
                // 登録
                $page->execute();
                break;
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
