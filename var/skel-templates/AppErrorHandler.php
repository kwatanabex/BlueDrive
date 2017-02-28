<?php
SyL_Loader::core('ErrorHandler.Web');

class AppErrorHandler extends SyL_ErrorHandlerWeb
{
    protected function __construct()
    {
        parent::__construct();

        $this->error_template_not_found = SYL_APP_DIR . '/templates/_App/error_template_not_found.html';
        $this->error_template_server_error = SYL_APP_DIR . '/templates/_App/error_template_server_error.html';
    }
}

