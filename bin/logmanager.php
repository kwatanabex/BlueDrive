<?php
error_reporting(E_ALL | E_STRICT);

if (!ini_get('date.timezone')) {
    date_default_timezone_set('Asia/Tokyo');
}

$current_dir = dirname(__FILE__);

require_once $current_dir . '/../../../SyL/framework/SyL.php';

$config = array(
  'project_dir' => $current_dir . '/..',
  'app_name'    => 'logmanager',
  'cache'       => 'file',
  'log'         => SYL_LOG_INFO
);

SyL_EventDispatcher::startup($config)->run();

