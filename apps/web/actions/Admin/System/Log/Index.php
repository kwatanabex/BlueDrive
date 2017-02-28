<?php
SyL_Loader::userLib('Bl.Abstract');

/**
 * Index クラス
 */
class Admin_System_Log_Index extends AppAdminSystemAction
{
    /**
     * デフォルトアクション処理
     *
     * @param SyL_ContextAbstract コンテキストオブジェクト
     * @param SyL_Data データオブジェクト
     */
    public function execute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $collect_log_level = SyL_Config::get('MANAGE_PROJECT_GET_LOG_LEVEL');
        $keep_log_date = SyL_Config::get('MANAGE_PROJECT_KEEP_LOG_DATE');

        $data->set('projects', self::getProjectList());
        $data->set('collect_log_level', $collect_log_level);
        $data->set('keep_log_date', $keep_log_date);

        $this->addBreadcrumbs($data, 'ログ', '');
    }

    /**
     * Ajax呼び出し時にコールされるアクションメソッド
     * 
     * @param SyL_ContextAbstract フィールド情報管理オブジェクト
     * @param SyL_Data データオブジェクト
     */
    protected function executeAjax(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $system_log = $data->get('system_log');
        $log_level  = $data->get('log_level');
        $project    = $data->get('project');
        $log   = $data->get('log');
        $page  = $data->get('page');

        if (($system_log !== '0') && ($system_log !== '1')) {
            $system_log = null;
        }

        $log_levels = array();
        switch ($log_level) {
        case 'trace_u':
            $log_levels[] = 'trace';
        case 'debug_u':
            $log_levels[] = 'debug';
        case 'info_u':
            $log_levels[] = 'info';
        case 'notice_u':
            $log_levels[] = 'notice';
        case 'warn_u':
            $log_levels[] = 'warn';
            $log_levels[] = 'error';
            break;
        case 'error':
        case 'warn':
        case 'notice':
        case 'info':
        case 'debug':
        case 'trace':
            $log_levels[] = $log_level;
            break;
        }

        if (!in_array($project, self::getProjectList())) {
            $project = null;
        }

        if (!is_numeric($page) || ($page < 1)) {
            $page = 1;
        }

        list($rows, $page_info) = BlAbstract::createInstance('log')->getLogs($system_log, $log_levels, $project, $log, $page);

        $result = array();
        $result['valid'] = true;
        $result['rows'] = $rows;
        $result['page_info'] = $page_info;
        $context->setViewJson($result);
    }

    private static function getProjectList()
    {
        $projects = array();
        $projects[] = basename(realpath(SYL_PROJECT_DIR));

        $i = 1;
        while (true) {
            $project_dir = SyL_Config::get('MANAGE_PROJECT_DIR' . $i);
            if (!$project_dir) {
                break;
            }
            $projects[] = basename($project_dir);
            $i++;
        }

        return $projects;
    }
}
