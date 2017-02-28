<?php
SyL_Loader::core('ErrorHandler.Web');

class AppErrorHandler extends SyL_ErrorHandlerWeb
{
    /**
     * ソースを表示する行数
     * ※奇数にする
     *
     * @var int
     */
    private static $display_source_line = 9;

    protected function handleNotFoundError(Exception $e)
    {
        header('HTTP/1.0 404 Not Found');
        header('Content-Type: text/html; charset=' . SYL_ENCODE_INTERNAL);

        include_once SYL_APP_DIR . '/templates/_App/error_template_not_found.html';
    }

    protected function handleError(Exception $e)
    {
        header('HTTP/1.0 500 Internal Server Error');
        header('Content-Type: text/html; charset=' . SYL_ENCODE_INTERNAL);

        if ($e instanceof BlueDriveAjaxException) {
            // Ajax通常例外
            $result = array();
            $result['message'] = $e->getMessage();
            echo json_encode($result);
        } else if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
            // Ajaxリクエスト判定
            echo json_encode(array());
        } else {
            if ($e instanceof BlueDriveException) {
                $context = self::getComponent('context');
                $url_base = '';
                if ($context) {
                    $url_base = $context->getRequest()->getUrlBase() . '/';
                }

                $url_root = dirname($url_base);
                if ($url_root == '/') {
                    $url_root = '';
                }

                $url_admin_base = $url_base . 'admin/';
                $url_file_base      = $url_admin_base . 'file/';
                $url_table_base     = $url_admin_base . 'table/';
                $url_tablemeta_base = $url_admin_base . 'tablemeta/';
                $url_system_base    = $url_admin_base . 'system/';
                $url_user_base      = $url_admin_base . 'user/';

                $user = $context->getUser();

                $error_message = $e->getMessage();
                if ($e instanceof BlueDriveAuthorizationException) {
                    // 認証エラー
                    include_once SYL_APP_DIR . '/templates/_Layout/error_auth.html';
                } else {
                    // デフォルト
                    include_once SYL_APP_DIR . '/templates/_Layout/error.html';
                }
            } else {
                $error_message = self::getErrorMessage($e);
                $error_trace = self::getTrace($e);
                $error_lines = self::getLines($error_trace);
                include_once SYL_APP_DIR . '/templates/_App/error_template_server_error.html';
            }
        }

        $address = SyL_Config::get('ERROR_MAIL_ADDRESS');
        if ($address) {
            $this->sendErrorMail($e, $address);
        }
    }

    /**
     * エラートレース配列をHTML表示用に変換する
     *
     * @param array エラートレース配列
     * @return array HTML表示用エラートレース
     */
    private static function getLines(array $error_trace)
    {
        $error_lines = array();

        foreach ($error_trace as $trace) {
            $error_line = $trace['line'];
            $error_file = $trace['file'];
            if (is_numeric($error_line) && file_exists($error_file)) {
                $error_half_line = floor(self::$display_source_line / 2);
                $start_line = 1;
                $crit_line  = 1;
                if (($error_line - $error_half_line) > 1) {
                    $start_line = $error_line - $error_half_line;
                }
                $i = 1;
                $tmp_lines = array();
                foreach (file($error_file) as $line => $source) {
                    if (($line + 1) >= $start_line) {
                        if (($line + 1) == $error_line) {
                            $tmp_lines[] = '<span style="color: #FF0000">Line ' . ($line + 1) . ': ' . htmlentities($source, ENT_QUOTES, SYL_ENCODE_INTERNAL) . '</span>';
                        } else {
                            $tmp_lines[] = 'Line ' . ($line + 1) . ': ' . htmlentities($source, ENT_QUOTES, SYL_ENCODE_INTERNAL);
                        }
                        if ($i >= self::$display_source_line) {
                            break;
                        }
                        $i++;
                    }
                }
                $error_lines[$trace['no']] = implode('', $tmp_lines);
            } else {
                $error_lines[$trace['no']] = '';
            }
        }

        return $error_lines;
    }
}

