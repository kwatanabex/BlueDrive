<?php
SyL_Loader::userLib('Bl.Abstract');

class BlLogManager extends BlAbstract
{
    const FILEUPDATE_FILENAME = '.logmn_fileupdate';
    const FIX_TIMESTAMP_FILENAME = '.logmn_fix_timestamp';

    public function getLogs($system_log, array $log_levels, $project_name, $log, $page)
    {
        $select_rows = 20;

        $access = $this->getDaoAccess('bd_syl_log');
        list($dbrows, $pager) = $access->selectLogs($system_log, $log_levels, $project_name, $log, $page, $select_rows);

        $rows = array();
        foreach ($dbrows as &$record) {
            $row = array();
            foreach ($record as $name => $value) {
                $row[$name] = $value;
            }
            $rows[] = $row;
        }

        $page_info['select_rows'] = $select_rows;
        $page_info['row_count'] = $pager->getPageCount();
        $page_info['row_max'] = $pager->getSum();
        $page_info['page_current'] = $pager->getCurrentPage();
        $page_info['page_max'] = $pager->getTotalPage();
        $page_info['range'] = $pager->getRange(4);

        return array($rows, $page_info);
    }

    public function collectSylLog($project_dir, $log_level, DateTime $start_timestamp=null, DateTime $end_timestamp=null, $incremental=false)
    {
        $project_name = basename($project_dir);
        $log_dir = $project_dir . '/var/logs';

        $logfiles = self::getLogFiles($log_dir, 'SyL_', $start_timestamp, $end_timestamp);

        $log_levels = self::getValidLogLevels($log_level);
        $access = $this->getDaoAccess('bd_syl_log');

        foreach ($logfiles as $logfile) {
            $fileupdate_log_file = dirname($logfile) . '/' . self::FILEUPDATE_FILENAME;
            $increment_log_file = dirname($logfile) . '/' . self::FIX_TIMESTAMP_FILENAME;

            clearstatcache();

            if ($incremental) {
                if (is_file($fileupdate_log_file)) {
                    $latest_fileupdate = (float)file_get_contents($fileupdate_log_file);
                    $current_fileupdate = (float)filemtime($logfile);
                    if ($latest_fileupdate >= $current_fileupdate) {
                        continue;
                    }
                }
                if (is_file($increment_log_file)) {
                    $start_timestamp = new DateTime(file_get_contents($increment_log_file));
                    $start_timestamp->add(new DateInterval('PT1S'));
                } else {
                    $start_timestamp = null;
                }
                $end_timestamp = null;
            }

            if ($start_timestamp) {
                if (!self::isEnableLogFile($logfile, $start_timestamp)) {
                    continue;
                }
            }

            SyL_Logger::info('start log maintenance SyL : ' . $logfile);

            $fp = fopen($logfile, 'rb');
            $loginfo = null;
            while (($buffer = fgets($fp)) !== false) {
                if (preg_match('/^([0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}) \[(\w+)\] (\w+) \{([^}]+)\} (.+)$/', $buffer, $maches)) {
                    if ($loginfo) {
                        self::insertLog($access, $project_name, '0', $loginfo);
                        if ($incremental) {
                            file_put_contents($increment_log_file, $loginfo[1]);
                        }
                    }
                    if (in_array($maches[2], $log_levels)) {
                        $timestamp = new DateTime($maches[1]);
                        if ($start_timestamp && ($start_timestamp > $timestamp)) {
                            $loginfo = null;
                        } else if ($end_timestamp && ($end_timestamp < $timestamp)) {
                            $loginfo = null;
                            break;
                        } else {
                            $loginfo = $maches;
                        }
                    } else {
                        $loginfo = null;
                    }
                } else {
                    if ($loginfo) {
                        $loginfo[5] .= $buffer;
                    }
                }
            }
            if ($loginfo) {
                self::insertLog($access, $project_name, '0', $loginfo);
                if ($incremental) {
                    file_put_contents($increment_log_file, $loginfo[1]);
                }
            }

            fclose($fp);
            $fp = null;

            if ($incremental) {
                clearstatcache();
                file_put_contents($fileupdate_log_file, filemtime($logfile));
            }

            SyL_Logger::info('end log maintenance SyL : ' . $logfile);
        }
    }

    public function collectSysLog($project_dir, DateTime $start_timestamp=null, DateTime $end_timestamp=null, $incremental=false)
    {
        $project_name = basename($project_dir);
        $log_dir = $project_dir . '/var/syslogs';

        $logfiles = self::getLogFiles($log_dir, 'phperror_', $start_timestamp, $end_timestamp);

        $access = $this->getDaoAccess('bd_syl_log');

        foreach ($logfiles as $logfile) {
            $fileupdate_log_file = dirname($logfile) . '/' . self::FILEUPDATE_FILENAME;
            $increment_log_file = dirname($logfile) . '/' . self::FIX_TIMESTAMP_FILENAME;

            clearstatcache();

            if ($incremental) {
                if (is_file($fileupdate_log_file)) {
                    $latest_fileupdate = (float)file_get_contents($fileupdate_log_file);
                    $current_fileupdate = (float)filemtime($logfile);
                    if ($latest_fileupdate >= $current_fileupdate) {
                        continue;
                    }
                }
                if (is_file($increment_log_file)) {
                    $start_timestamp = new DateTime(file_get_contents($increment_log_file));
                    $start_timestamp->add(new DateInterval('PT1S'));
                } else {
                    $start_timestamp = null;
                }
                $end_timestamp = null;
            }

            $application_name = substr(dirname($logfile), strlen($log_dir) + 1);
            if (!$application_name) {
                $application_name = null;
            }

            if ($start_timestamp) {
                if (!self::isEnableLogFile($logfile, $start_timestamp)) {
                    continue;
                }
            }

            SyL_Logger::info('start log maintenance Sys : ' . $logfile);

            $fp = fopen($logfile, 'rb');
            $loginfo = null;
            while (($buffer = fgets($fp)) !== false) {
                if (preg_match('/^\[[^\]]+\] (PHP [ \d]+\. .+)$/', $buffer, $maches)) {
                    if ($loginfo) {
                        $loginfo[5] .= $maches[1];
                    }
                } else if (preg_match('/^\[([^\]]+)\] (.+)$/', $buffer, $maches)) {
                    if ($loginfo) {
                        self::insertLog($access, $project_name, '1', $loginfo);
                        if ($incremental) {
                            file_put_contents($increment_log_file, $loginfo[1]);
                        }
                    }

                    $timestamp = new DateTime($maches[1]);
                    if ($start_timestamp && ($start_timestamp > $timestamp)) {
                        $loginfo = null;
                    } else if ($end_timestamp && ($end_timestamp < $timestamp)) {
                        $loginfo = null;
                        break;
                    } else {
                        $loginfo = array();
                        $loginfo[0] = null;
                        $loginfo[1] = $timestamp->format('Y-m-d H:i:s');
                        $loginfo[2] = 'error';
                        $loginfo[3] = $application_name;
                        $loginfo[4] = null;
                        $loginfo[5] = $maches[2];
                    }

                } else {
                    if ($loginfo) {
                        $loginfo[5] .= $buffer;
                    }
                }
            }
            if ($loginfo) {
                self::insertLog($access, $project_name, '1', $loginfo);
                if ($incremental) {
                    file_put_contents($increment_log_file, $loginfo[1]);
                }
            }

            fclose($fp);
            $fp = null;

            if ($incremental) {
                clearstatcache();
                file_put_contents($fileupdate_log_file, filemtime($logfile));
            }

            SyL_Logger::info('end log maintenance Sys : ' . $logfile);
        }
    }

    public function removeLogFile($project_dir, $keep_days)
    {
        SyL_Logger::info('start clear log file: ' . $project_dir);

        clearstatcache();

        $keep_datetime = new DateTime();
        $keep_datetime->sub(new DateInterval('P' . $keep_days . 'D'));
        $keep_datetime->setTime(0, 0, 0);
        $keep_timestamp = $keep_datetime->getTimestamp();

        $log_dir = $project_dir . '/var/logs';
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($log_dir));
        while ($it->valid()) {
            if (!$it->isDot()) {
                if (preg_match('/(\d{8})\.log$/', $it->getPathname())) {
                    if (is_file($it->getPathname())) {
                        $current_timestamp = strtotime(date('Y-m-d 00:00:00', $it->getMTime()));
                        if ($keep_timestamp > $current_timestamp) {
                            SyL_Logger::info('remove log file : ' . $it->getPathname());
                            unlink($it->getPathname());
                        }
                    }
                }
            }
            $it->next();
        }

        $log_dir = $project_dir . '/var/syslogs';
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($log_dir));
        while ($it->valid()) {
            if (!$it->isDot()) {
                if (preg_match('/(\d{8})\.log$/', $it->getPathname())) {
                    if (is_file($it->getPathname())) {
                        $current_timestamp = strtotime(date('Y-m-d 00:00:00', $it->getMTime()));
                        if ($keep_timestamp > $current_timestamp) {
                            SyL_Logger::info('remove log file : ' . $it->getPathname());
                            unlink($it->getPathname());
                        }
                    }
                }
            }
            $it->next();
        }

        SyL_Logger::info('end clear log file: ' . $project_dir);
    }

    public function clearIncremental($project_dir)
    {
        SyL_Logger::info('start clear timestamp: ' . $project_dir);

        clearstatcache();

        $log_dir = $project_dir . '/var/logs';
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($log_dir));
        while ($it->valid()) {
            if (!$it->isDot()) {
                if (preg_match('/' . preg_quote(self::FIX_TIMESTAMP_FILENAME) . '$/', $it->getSubPathName())) {
                    if (is_file($it->getPathname())) {
                        SyL_Logger::info('remove incremental file: ' . $it->getPathname());
                        unlink($it->getPathname());
                    }
                } else if (preg_match('/' . preg_quote(self::FILEUPDATE_FILENAME) . '$/', $it->getSubPathName())) {
                    if (is_file($it->getPathname())) {
                        SyL_Logger::info('remove incremental file: ' . $it->getPathname());
                        unlink($it->getPathname());
                    }
                }
            }
            $it->next();
        }

        $log_dir = $project_dir . '/var/syslogs';
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($log_dir));
        while ($it->valid()) {
            if (!$it->isDot()) {
                if (preg_match('/' . preg_quote(self::FIX_TIMESTAMP_FILENAME) . '$/', $it->getSubPathName())) {
                    if (is_file($it->getPathname())) {
                        SyL_Logger::info('remove incremental file: ' . $it->getPathname());
                        unlink($it->getPathname());
                    }
                } else if (preg_match('/' . preg_quote(self::FILEUPDATE_FILENAME) . '$/', $it->getSubPathName())) {
                   if (is_file($it->getPathname())) {
                        SyL_Logger::info('remove incremental file: ' . $it->getPathname());
                        unlink($it->getPathname());
                    }
                }
            }
            $it->next();
        }

        SyL_Logger::info('end clear timestamp');
    }

    private static function getValidLogLevels($log_level)
    {
        $log_levels = array();
        switch ($log_level) {
        case 'trace':  $log_levels[] = 'trace';
        case 'debug':  $log_levels[] = 'debug';
        case 'info':   $log_levels[] = 'info';
        case 'notice': $log_levels[] = 'notice';
        case 'warn':   $log_levels[] = 'warn';
        case 'error':  $log_levels[] = 'error';
        }
        return $log_levels;
    }

    private static function getLogFiles($log_dir, $log_file_prefix, DateTime $start_timestamp=null, DateTime $end_timestamp=null)
    {
        $start_ymd = 0;
        $end_ymd = 99999999;
        if ($start_timestamp != null) {
            $start_ymd = (int)$start_timestamp->format('Ymd');
            if ($end_timestamp != null) {
                $end_ymd = (int)$end_timestamp->format('Ymd');
            }
        }

        $logfiles = array();
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($log_dir));
        while ($it->valid()) {
            if (!$it->isDot()) {
                // logmanager アプリケーションは対象外
                if ($it->getSubPath() != 'logmanager') {
                    if (preg_match('/' . $log_file_prefix . '(\d{8})\.log$/', $it->getSubPathName(), $maches)) {
                        if (((int)$maches[1] >= $start_ymd) && ((int)$maches[1] <= $end_ymd)) {
                            $logfiles[$maches[1]] = $log_dir . '/' . $it->getSubPathName();
                        }
                    }
                }
            }
            $it->next();
        }

        ksort($logfiles);

        return array_values($logfiles);
    }

    private static function isEnableLogFile($log_file, DateTime $current_timestamp)
    {
        if (preg_match('/(\d{8})\.log$/', $log_file, $matches)) {
            if ((int)$matches[1] >= (int)$current_timestamp->format('Ymd')) {
                return true;
            } else {
                return false;
            }
        } else {
            throw new SyL_InvalidParameterException("invalid logfile ($log_file)");
        }
    }

    private static function insertLog(DaoAccessBd_syl_log $access, $project_name, $system_log, array $loginfo)
    {
        $record = $access->createRecord(true);
        $record->log_date = $loginfo[1];
        $record->system_log = $system_log;
        $record->log_level = $loginfo[2];
        $record->hostname = php_uname('n');
        $record->project_name = $project_name;
        $record->application_name = $loginfo[3];
        $record->method = $loginfo[4];
        $record->log = rtrim($loginfo[5]);

        try {
            $access->insert($record);
        } catch (Exception $e) {
            SyL_Logger::error('log insert failed: ' . $e->getMessage());
        }
    }

}
