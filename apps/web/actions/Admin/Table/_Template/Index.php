<?php
/**
 * Index クラス
 */
class Admin_Table_Template_Index extends AppAdminTableAction
{
    /**
     * デフォルトアクション処理
     *
     * @param SyL_ContextAbstract コンテキストオブジェクト
     * @param SyL_Data データオブジェクト
     */
    public function execute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        if ($context->getRequest()->isPost()) {
            $this->executeAjax($context, $data);
            return;
        }

        $config = $this->createCrudConfig($context, $data, SyL_CrudConfigAbstract::CRUD_TYPE_LST);
        $page   = SyL_CrudPageAbstract::createInstance($config);

        if (!$config->enableCrud()) {
            throw new BlueDriveAuthorizationException('一覧表示権限がありません');
        }

        $this->addBreadcrumbs($data, $page->getName(), '');

        $data->set('title', $page->getName());
        $data->set('description', $page->getDescription());

        $data->set('search_form', $page->getSearchFormView());
        $data->set('import_form', $page->getImportFormView());
        $data->set('elements', $page->getElements());

        $enable_export = $config->enableCrud(SyL_CrudConfigAbstract::CRUD_TYPE_EXP);
        $enable_tmpfile = false;
        $enable_zip = false;
        if ($enable_export) {
            if (is_writable(sys_get_temp_dir())) {
                $enable_tmpfile = true;
                $enable_zip = in_array('Phar', get_loaded_extensions());
            }
        }

        $data->set('enable_export', $enable_export);
        $data->set('enable_tmpfile', $enable_tmpfile);
        $data->set('enable_zip', $enable_zip);
        $data->set('enable_import', $config->enableCrud(SyL_CrudConfigAbstract::CRUD_TYPE_IMP));

        $data->set('meta_name', $config->getMetaName());
        $data->set('url_new', $config->getUrlNew());
        $data->set('url_rss', $config->getUrlRss());
        $data->set('url_feed', $config->getUrlAtmFeed());
    }

    /**
     * Ajax呼び出し時にコールされるアクションメソッド
     * 
     * @param SyL_ContextAbstract フィールド情報管理オブジェクト
     * @param SyL_Data データオブジェクト
     */
    protected function executeAjax(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $config = $this->createCrudConfig($context, $data, SyL_CrudConfigAbstract::CRUD_TYPE_LST);
        $page   = SyL_CrudPageAbstract::createInstance($config);

        $type = $data->get('__type');

        $result = array();
        $result['valid'] = false;
        $result['messages'] = array();
        switch ($type) {
        case 'list':
            if (!$config->enableCrud(SyL_CrudConfigAbstract::CRUD_TYPE_LST)) {
                throw new BlueDriveAuthorizationException('一覧表示権限がありません');
            }

            // ページパラメータチェック
            $page_count = $data->get('__page');
            if (!is_numeric($page_count) || ($page_count <= 0)) {
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
            }
            // 件数パラメータ
            $row_count = $data->get('__row');
            if ($row_count) {
                if (!is_numeric($row_count)) {
                    $result['messages'][] = 'invalid row parameter';
                    break;
                }
            } else {
                $row_count = 0;
            }

            $headers   = array();
            $rows      = array();
            $page_info = array();
            try {
                list($headers, $rows, $page_info) = $page->getList($page_count, $sorts, $row_count);
            } catch (SyL_DbDaoValidateException $e) {
                $messages = $e->getMessages();
                $result['messages'] = $messages;
                break;
            }

            $result['valid'] = true;
            $result['headers'] = $headers;
            $result['rows'] = $rows;
            $result['page'] = $page_info;
            break;

        case 'export':
            if (!$config->enableCrud(SyL_CrudConfigAbstract::CRUD_TYPE_LST) ||
                !$config->enableCrud(SyL_CrudConfigAbstract::CRUD_TYPE_EXP)) {
                throw new BlueDriveAuthorizationException('エクスポート権限がありません');
            }

            // ソートパラメータチェック
            $sort = $data->get('__sort');
            $sorts = array();
            if ($sort) {
                $tmp = explode('.', $sort, 2);
                if (count($tmp) != 2) {
                    self::outputJsonError('invalid sort parameter');
                }
                if (($tmp[1] !== 'ASC') && ($tmp[1] != 'DESC')) {
                    self::outputJsonError('invalid sort parameter');
                }
                $sorts[$tmp[0]] = ($tmp[1] == 'ASC');
            }

            $header_flag  = ($data->get('__header') == '1');
            $charset      = ($data->get('__sjis') == '1') ? 'SJIS-win' : null;
            $tmpfile_flag = ($data->get('__tmpfile') == '1');
            $zip_flag     = ($data->get('__zip') == '1');

            if ($tmpfile_flag) {
                // DBから取得したデータを一時ファイルに保存してからダウンロード
                $page->exportStream($sorts, $header_flag, $charset, $zip_flag);
            } else {
                // DBから取得したデータをそのままダウンロード
                $page->export($sorts, $header_flag, $charset);
            }
            break;

        case 'import':
            if (!$config->enableCrud(SyL_CrudConfigAbstract::CRUD_TYPE_LST) ||
                !$config->enableCrud(SyL_CrudConfigAbstract::CRUD_TYPE_IMP)) {
                throw new BlueDriveAuthorizationException('インポート権限がありません');
            }

            // インポートは先にヘッダを出力
            // jquery.upload の仕様で text/html
            $response_content_type = 'text/html; charset=UTF-8';

            $upload = null;
            try {
                $upload = $context->getRequest()->getFileUploadInstance();
            } catch (SyL_InvalidConfigException $e) {
                $result['message'] = $e->getMessage();
                $context->setViewJson($result, $response_content_type);
                return;
            } catch (SyL_HttpFileUploadException $e) {
                $result['message'] = $e->getMessage();
                $context->setViewJson($result, $response_content_type);
                return;
            }
            $upload_file = $upload->getFileInfo('__csvfile');

            $header_include_flag = ($data->get('__header') == '1');
            $charset = ($data->get('__sjis') == '1') ? 'SJIS-win' : null;

            try {
                $page->import($upload_file->getTmpName(), $header_include_flag, $charset);
                $result['valid'] = true;
            } catch (SyL_CrudValidateException $e) {
                $result['messages'] = $e->getMessages();
            }

            $context->setViewJson($result, $response_content_type);
            return;

        case 'delete':
            if (!$config->enableCrud(SyL_CrudConfigAbstract::CRUD_TYPE_DEL)) {
                throw new BlueDriveAuthorizationException('削除権限がありません');
            }

            $id = $data->get('__id');
            if (!$id) {
                $result['messages'][] = 'invalid id parameter';
                break;
            }
            $page->setId($id);
            $page->deleteRecord();

            $result['valid'] = true;
            break;

        default:
            throw new BlueDriveAjaxException('unknown type');
        }

        $context->setViewJson($result);
    }

    /**
     * エラー情報をJSONデータで出力する
     *
     * @param mixed 出力データ
     */
    private static function outputJsonError($error_message, $content_type='application/json; charset=UTF-8')
    {
        $result = array();
        $result['valid'] = false;
        if (!is_array($error_message)) {
            $error_message = array($error_message);
        }
        $result['messages'] = $error_message;

        self::outputJson($result, $content_type);
    }


}
