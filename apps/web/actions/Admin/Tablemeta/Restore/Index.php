<?php
/**
 * Index クラス
 */
class Admin_Tablemeta_Restore_Index extends AppAdminTablemetaAction
{
    /**
     * デフォルトアクション処理
     *
     * @param SyL_ContextAbstract コンテキストオブジェクト
     * @param SyL_Data データオブジェクト
     */
    public function execute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $enable_compress = false;
        if (is_writable(sys_get_temp_dir())) {
            $enable_tmpfile = true;
            $enable_compress = in_array('Phar', get_loaded_extensions());
        }

        $metadata_dir = $context->getUser()->crudCurrentOutputDir;

        if ($context->getRequest()->isPost()) {
            // インポートは先にヘッダを出力
            // jquery.upload の仕様で text/html
            $response_content_type = 'text/html; charset=UTF-8';

            $upload = null;
            try {
                $upload = $context->getRequest()->getFileUploadInstance();
            } catch (SyL_InvalidConfigException $e) {
                self::outputJsonError($e->getMessage(), $response_content_type);
            } catch (SyL_HttpFileUploadException $e) {
                self::outputJsonError($e->getMessage(), $response_content_type);
            }
            $upload_file = $upload->getFileInfo('restore_file');

            $mimetype = $upload_file->getType();
            $tmp_file = $upload_file->getTmpName();

            $format = '';
            $savedir = dirname($tmp_file);
            $savename = basename($tmp_file);
            if (preg_match('/^application\/(.*)gzip/', $mimetype)) {
                $format = Phar::TAR;
                $savename .= '.tar.gz';
            } else if (preg_match('/^application\/(.*)zip/', $mimetype)) {
                $format = Phar::ZIP;
                $savename .= '.zip';
            } else {
                self::outputJsonError('アップロードファイルが正しくありません', $response_content_type);
            }
            $upload_file->upload($savedir, $savename);

            $filename = $savedir . '/' . $savename;

            // 一時ファイル削除用
            register_shutdown_function(create_function('', 'if (file_exists("' . $filename . '")) { unlink("' . $filename . '"); }'));

            $archive = new PharData($filename, 0, null, $format);
            foreach (new RecursiveIteratorIterator($archive) as $file) {
                $path = $file->getPathname();
                // -> phar:///tmp/phpOTJ2Le.zip/Crud/Config/CrudConfigStock_company.php
                $pos = strpos($path, $filename) + strlen($filename);
                $path = substr($path, $pos);
                // -> /Crud/Config/CrudConfigStock_company.php
                $dirpath = substr(dirname($path), 1);
                // -> Crud/Config

                // ディレクトリ作成
                $current_dir = $metadata_dir;
                if (!is_dir($current_dir)) {
                    mkdir($current_dir);
                }
                foreach (explode('/', $dirpath) as $name) {
                    $current_dir .= '/' . $name;
                    if (!is_dir($current_dir)) {
                        mkdir($current_dir);
                    }
                }

                file_put_contents($metadata_dir . $path, file_get_contents($file->getPathname()));
            }
            $archive->decompressFiles();

            self::outputJson(array(), $response_content_type);
        }

        $breadcrumbs = $data->get('App.Admin.breadcrumbs');
        $breadcrumbs['メタデータのリストア'] = '';
        $data->set('App.Admin.breadcrumbs', $breadcrumbs);

        $data->set('metadata_dir', $metadata_dir);
        $data->set('enable_compress', $enable_compress);

    }

    /**
     * JSONデータを出力する
     *
     * @param mixed 出力データ
     */
    private static function outputJson(array $result, $content_type='application/json; charset=UTF-8')
    {
        if (!array_key_exists('valid', $result)) {
            $result['valid'] = true;
        }
        if ($content_type) {
            header('Content-type: ' . $content_type);
        }
        echo json_encode($result);
        exit;
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
