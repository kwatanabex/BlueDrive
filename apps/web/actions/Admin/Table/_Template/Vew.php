<?php
/**
 * Index クラス
 */
class Admin_Table_Template_Vew extends AppAdminTableAction
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

        $config = $this->createCrudConfig($context, $data, SyL_CrudConfigAbstract::CRUD_TYPE_VEW);
        if (!$config->enableCrud()) {
            throw new BlueDriveAuthorizationException('詳細表示権限がありません');
        }

        $page = SyL_CrudPageAbstract::createInstance($config);
        $page->setId($id);

        $this->addBreadcrumbs($data, $page->getName(), $config->getUrlLst());
        $this->addBreadcrumbs($data, '詳細', '');

        $data->set('id', $id);

        $data->set('title', $page->getName());
        $data->set('description', $page->getDescription());

        $data->set('elements', $page->getElements());
        $data->set('form', $page->getFormView());

        // 関連名パラメータチェック
        $relatedLinks = array();
        foreach ($page->getRelatedLinks() as $name => $values) {
            $related_config = $this->createCrudConfig($context, $data, SyL_CrudConfigAbstract::CRUD_TYPE_LST, $name);
            $relatedLinks[$name] = $related_config->getName();
        }

        $data->set('relatedLinks', $relatedLinks);

        $data->set('url_lst', $config->getUrlLst());
        $data->set('url_edt', $config->getUrlEdt());
        $data->set('enable_delete', $config->enableCrud(SyL_CrudConfigAbstract::CRUD_TYPE_DEL));
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

        $config = $this->createCrudConfig($context, $data, SyL_CrudConfigAbstract::CRUD_TYPE_VEW);
        if (!$config->enableCrud()) {
            throw new BlueDriveAuthorizationException('詳細表示権限がありません');
        }

        $page = SyL_CrudPageAbstract::createInstance($config);
        $page->setId($id);

        $result = array();
        $result['valid'] = false;
        $result['messages'] = array();

        $type = $data->get('__type');
        switch ($type) {
        case 'list':
            // 関連名パラメータチェック
            $related_name = $data->get('__name');
            $related_config = null;
            $row_count = 0;
            $default_sort = '';
            foreach ($page->getRelatedLinks() as $name => $values) {
                if ($related_name == $name) {
                    $related_config = $this->createCrudConfig($context, $data, SyL_CrudConfigAbstract::CRUD_TYPE_LST, $name);
                    $row_count    = $values['row_count'];
                    $default_sort = $values['default_sort'];
                    break;
                }
            }

            if ($related_config == null) {
                // 関連名エラー
                $result['messages'][] = 'invalid name parameter (' . $related_name . ')';
                break;
            }

            // ページパラメータチェック
            $page_count = $data->get('__page');
            if (!is_numeric($page_count) || ($page_count <= 0)) {
                // ページ数エラー
                $result['messages'][] = 'invalid page parameter';
                break;
            }

            // ソートパラメータチェック
            $sort = $data->get('__sort');
            $sorts = array();
            if ($sort) {
                $tmp = explode('.', $sort, 2);
                if (count($tmp) != 2) {
                    $result['messages'][] = 'invalid sort parameter';
                    break;
                }
                if (($tmp[1] !== 'ASC') && ($tmp[1] != 'DESC')) {
                    $result['messages'][] = 'invalid sort parameter';
                    break;
                }
                $sorts[$tmp[0]] = ($tmp[1] == 'ASC');
            } else {
                $sorts = $default_sort;
            }

            $related_page = SyL_CrudPageAbstract::createInstance($related_config);
            $related_page->setId($id);
            list($headers, $rows, $page_info) = $related_page->getList($page_count, $sorts, $row_count);

            $result['valid'] = true;
            $result['headers'] = $headers;
            $result['rows'] = $rows;
            $result['page'] = $page_info;
            break;

        default:
            throw new BlueDriveAjaxException('unknown type');
        }

        $context->setViewJson($result);
    }
}
