<?php
//error_reporting(E_ALL | E_STRICT);

if (!ini_get('date.timezone')) {
    date_default_timezone_set('Asia/Tokyo');
}

require_once '{{SYL_FRAMEWORK_DIR}}/SyL.php';

$config = array(
  'project_dir' => '{{PROJECT_DIR}}',
  'app_name'    => '{{APP_NAME}}',
  'cache'       => 'file',
  'log'         => SYL_LOG_WARN,
  'syslog'      => true
);

SyL_EventDispatcher::startup($config)->run();

