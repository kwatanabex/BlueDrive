<?php
/**
 * Index クラス
 */
class Admin_Tablemeta_Backup_Index extends AppAdminTablemetaAction
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

        $current_database = $context->getUser()->crudCurrentDatabase;
        $root_dir = $context->getUser()->crudCurrentOutputDir;

        if ($context->getRequest()->isPost()) {
            if (!$enable_compress) {
                throw new BlueDriveException('パラメータ不正');
            }

            $type = $data->get('type');
            $ext  = $data->get('ext');
            if ($type != 'backup') {
                throw new BlueDriveException('パラメータ不正');
            }

            // 一時ファイル
            $temp_file = tempnam(sys_get_temp_dir(), 'bd_tablemeta_backup_');
            // 一時ファイル削除用
            register_shutdown_function(create_function('', 'if (file_exists("' . $temp_file . '")) { unlink("' . $temp_file . '"); }'));

            $format = '';
            $content_type = 'application/octet-stream';
            switch ($ext) {
            case '.zip':
                $format = Phar::ZIP;
                $content_type = 'application/zip';
                $temp_file .= '.zip';
                break;
            case '.tar.gz':
                $format = Phar::TAR;
                $content_type = 'application/x-gzip';
                $temp_file .= '.tar';
                break;
            default:
                throw new BlueDriveException('パラメータ不正');
            }

            // 一時ファイル削除用（拡張子つき）
            register_shutdown_function(create_function('', 'if (file_exists("' . $temp_file . '")) { unlink("' . $temp_file . '"); }'));

            $archive = new PharData($temp_file, 0, null, $format);
            $archive->buildFromDirectory($root_dir);
            switch ($format) {
            case Phar::ZIP:
                $archive->compressFiles(Phar::GZ);
                break;
            case Phar::TAR:
                $archive->compress(Phar::GZ);
                $temp_file .= '.gz';
                // 一時ファイル削除用（拡張子.gz）
                register_shutdown_function(create_function('', 'if (file_exists("' . $temp_file . '")) { unlink("' . $temp_file . '"); }'));
                break;
            }

            $filename = sprintf('export_%s_%s%s', $current_database, date('YmdHis'), $ext);
            $size = filesize($temp_file);

            while (ob_get_level()) {
                ob_end_clean();
            }
            ignore_user_abort(false);

            header('Content-Type: ' . $content_type);
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . $size);
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
            header('Connection: close');

            $stream = fopen($temp_file, 'rb');
            while (!feof($stream)) {
                echo fread($stream, 8192);
                flush();
            }
            fclose($stream);
            $stream = null;

            exit;
        }

        $crud_display_list_file = $root_dir . SyL_Config::get('CRUD_DISPLAY_LIST_FILE');
        $generate_crud = file_exists($crud_display_list_file);

        $this->addBreadcrumbs($data, 'メタデータのバックアップ', '');

        $data->set('generate_crud', $generate_crud);
        $data->set('metadata_dir', $root_dir);
        $data->set('enable_compress', $enable_compress);

    }


}
