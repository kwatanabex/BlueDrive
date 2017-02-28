<?php
SyL_Loader::userLib('Bl.Abstract');
SyL_Loader::userLib('Bl.FileStorageType');

/**
 * Index クラス
 */
class Admin_File_Index extends AppAdminFileAction
{
    /**
     * デフォルトアクション処理
     *
     * @param SyL_ContextAbstract コンテキストオブジェクト
     * @param SyL_Data データオブジェクト
     */
    public function execute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $file_manager = BlAbstract::createInstance('file');

        $action = $data->get('action');
        if ($action) {
            $id  = $data->get('id');
            $area = $file_manager->getFileArea($id);
            $file_storage = $file_manager->createFileStorage($id);

            switch ($action) {
            case 'download':
                // ファイルダウンロード
                $file = $data->get('file');
                $fileinfo = $file_storage->getFile($file);

                $filename = $fileinfo['name'];
                $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
                if (strpos($ua, 'MSIE') !== false) {
                    $filename = urlencode($filename);
                }

                set_time_limit(0);
                while (ob_get_level()) {
                    ob_end_clean();
                }

                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . $fileinfo['size']);
                header('Cache-Control: no-cache, must-revalidate');
                header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
                header('Connection: close');

                $fp = $file_storage->getFileStream($file);
                while (!feof($fp)) {
                    echo fread($fp, 8192);
                    flush();
                }
                fclose($fp);
                exit;

            case 'preview':
                // プレビュー
                $file = $data->get('file');
                if (!preg_match('/\.(\w+)$/', $file, $matches)) {
                    throw new BlueDriveException('プレビューに対応していません');
                }

                $content_type = '';
                switch ($matches[1]) {
                case 'jpg': $content_type = 'image/jpeg'; break;
                case 'gif': $content_type = 'image/gif'; break;
                case 'png': $content_type = 'image/png'; break;
                default: throw new BlueDriveException('プレビューに対応していません');
                }

                $fileinfo = $file_storage->getFile($file);

                header('Content-Type: ' . $content_type);
                header('Content-Length: ' . $fileinfo['size']);
                header('Connection: close');

                $fp = $file_storage->getFileStream($file);
                while (!feof($fp)) {
                    echo fread($fp, 8192);
                    flush();
                }
                fclose($fp);
                exit;

            case 'upload':
                if (!$context->getRequest()->isPost()) {
                    throw new BlueDriveException('無効なリクエストメソッドです');
                }

                // jquery.upload の仕様で text/html
                $content_type = 'text/html; charset=UTF-8';

                $upload = null;
                try {
                    $upload = $context->getRequest()->getFileUploadInstance();
                } catch (SyL_Exception $e) {
                    SyL_Logger::warn($e);
                    $context->setViewJson(array('valid' => false), $content_type);
                    return;
                }

                $dir = $data->get('dir');
                try {
                    $file_storage->uploadFile($dir, $upload->getFileInfoArray('uploadfile'));
                } catch (SyL_Exception $e) {
                    SyL_Logger::warn($e);
                    $context->setViewJson(array('valid' => false), $content_type);
                    return;
                }

                $context->setViewJson(array('valid' => true), $content_type);
                return;

            default:
                throw new BlueDriveException('無効なパラメータです');
            }
        }

        $area_list = $file_manager->getFileAreaAll();
        $data->set('area_list', $area_list);
        $data->set('post_max_size', self::formatPostMaxFileSize());

        $this->addBreadcrumbs($data, 'ファイル管理', '');
    }

    /**
     * Ajax呼び出し時にコールされるアクションメソッド
     * 
     * @param SyL_ContextAbstract フィールド情報管理オブジェクト
     * @param SyL_Data データオブジェクト
     */
    protected function executeAjax(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $id = $data->get('id');
        $dir  = $data->get('dir');
        $action = $data->get('action');

        $result = array();
        $result['valid'] = false;

        if (($dir === null) || (strpos($dir, '/') === false)) {
            throw new BlueDriveAjaxException("invalid dir ({$dir})");
        }

        $file_storage = BlAbstract::createInstance('file')->createFileStorage($id);

        switch ($action) {
        case 'dir':
            // フォルダ一覧の取得
            $result['dirs'] = $file_storage->getDirectoryList($dir);
            $result['write'] = $file_storage->isWritable($dir);
            $result['selfWrite'] = $file_storage->isWritable(dirname($dir));
            $result['valid'] = true;
            break;

        case 'file':
            // ファイル一覧の取得
            $result['files'] = $file_storage->getFileList($dir);
            $result['dir']   = $dir;
            $result['valid'] = true;
            break;

        case 'dircreate':
            // フォルダの作成
            $dirname = $data->get('dirname');
            $file_storage->createDirectory($dir, $dirname);
            $result['valid'] = true;
            break;

        case 'dirdelete':
            // フォルダの削除
            $file_storage->removeDirectory($dir);
            $result['valid'] = true;
            break;

        case 'filedelete':
            // ファイルの削除
            $filenames = $data->geta('filename');
            $file_storage->removeFile($dir, $filenames);
            $result['valid'] = true;
            break;

        default:
            throw new BlueDriveAjaxException("invalid action ({$action})");
        }

        $context->setViewJson($result);
    }

    private static function getTargetPath($root_dir, $path)
    {
        $target = realpath($root_dir . $path);
        if (!$target) {
            throw new BlueDriveAjaxException("path not found ({$target})");
        }
        if (!preg_match('/^' . preg_quote($root_dir, '/') . '/', $target)) {
            throw new BlueDriveAjaxException("invalid path ({$target})");
        }

        if (!is_readable($target)) {
            throw new BlueDriveAjaxException("unable read file. permission denied ({$target})");
        }

        return $target;
    }

    private static function formatPostMaxFileSize()
    {
        $memory_limit = ini_get('memory_limit');
        $post_max_size = ini_get('post_max_size');
        $upload_max_filesize = ini_get('upload_max_filesize');

        if (preg_match('/(\d+)(K|M|G)/', $memory_limit, $matches)) {
            switch ($matches[2]) {
            case 'K': $memory_limit = $matches[1] * 1024; break;
            case 'M': $memory_limit = $matches[1] * 1024 * 1024; break;
            case 'G': $memory_limit = $matches[1] * 1024 * 1024 * 1024; break;
            }
        }

        if (preg_match('/(\d+)(K|M|G)/', $post_max_size, $matches)) {
            switch ($matches[2]) {
            case 'K': $post_max_size = $matches[1] * 1024; break;
            case 'M': $post_max_size = $matches[1] * 1024 * 1024; break;
            case 'G': $post_max_size = $matches[1] * 1024 * 1024 * 1024; break;
            }
        }

        if (preg_match('/(\d+)(K|M|G)/', $upload_max_filesize, $matches)) {
            switch ($matches[2]) {
            case 'K': $upload_max_filesize = $matches[1] * 1024; break;
            case 'M': $upload_max_filesize = $matches[1] * 1024 * 1024; break;
            case 'G': $upload_max_filesize = $matches[1] * 1024 * 1024 * 1024; break;
            }
        }

        $max_size = 0;
        if ($memory_limit > $post_max_size) {
            $max_size = $post_max_size;
        }
        if ($max_size > $upload_max_filesize) {
            $max_size = $upload_max_filesize;
        }

        $max_size = round($max_size / (1024 * 1024), 1);
        return $max_size . 'MB';
    }
}
