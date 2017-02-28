<?php
SyL_Loader::userLib('Bl.LogManager');

class Index extends AppAction
{
    public function execute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        SyL_Logger::info('start logmanager');

        $all_log     = $data->is('a');
        $incremental = $data->is('i');
        $clear_incremental = $data->is('c');

        $timestamp = $data->geta('t', 0);
        $log_level = $data->geta('l', 0);

        if ($log_level === null) {
            // デフォルト
            $log_level = SyL_Config::get('MANAGE_PROJECT_GET_LOG_LEVEL');
        }
        switch ($log_level) {
        case 'error':
        case 'warn':
        case 'notice':
        case 'info':
        case 'debug':
        case 'trace':
            break;
        default:
            throw new SyL_InvalidParameterException("invalid log level parameter ({$log_level})");
        }

        $start_timestamp = null;
        $end_timestamp = null;
        if (!$all_log) {
            if ($timestamp !== null) {
                $timestamps = explode('_', $timestamp, 2);
                $start_timestamp = new DateTime($timestamps[0]);
                if (count($timestamps) == 2) {
                    if ($timestamps[1]) {
                        $end_timestamp = new DateTime($timestamps[1]);
                    }
                } else {
                    $end_timestamp = new DateTime($start_timestamp->format('Y-m-d 23:59:59'));
                }
            }
        }

        $project_dirs = array();
        $project_dirs[] = realpath(SYL_PROJECT_DIR);

        $i = 1;
        while (true) {
            $project_dir = SyL_Config::get('MANAGE_PROJECT_DIR' . $i);
            if (!$project_dir) {
                break;
            }
            if (!is_dir($project_dir)) {
                throw new SyL_InvalidConfigException("project_dir not found ({$project_dir})");
            }
            $project_dirs[] = $project_dir;
            $i++;
        }

        $keep_log_date = SyL_Config::get('MANAGE_PROJECT_KEEP_LOG_DATE');
        $manager = new BlLogManager();
        foreach ($project_dirs as $project_dir) {
            if ($clear_incremental) {
                $manager->clearIncremental($project_dir);
            }

            $manager->collectSylLog($project_dir, $log_level, $start_timestamp, $end_timestamp, $incremental);
            $manager->collectSysLog($project_dir, $start_timestamp, $end_timestamp, $incremental);

            $manager->removeLogFile($project_dir, $keep_log_date);
        }

        SyL_Logger::info('end logmanager');
    }

}
