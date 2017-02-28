<?php
SyL_Loader::userLib('Data.SessionSystem');
SyL_Loader::userLib('Bl.Abstract');

/**
 * Login クラス
 */
class Login extends AppAction
{
    /**
     * デフォルトアクション処理
     *
     * @param SyL_ContextAbstract コンテキストオブジェクト
     * @param SyL_Data データオブジェクト
     */
    public function execute(SyL_ContextAbstract $context, SyL_Data $data)
    {
    }

    /**
     * Ajax呼び出し時にコールされるアクションメソッド
     * 
     * @param SyL_ContextAbstract フィールド情報管理オブジェクト
     * @param SyL_Data データオブジェクト
     */
    protected function executeAjax(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $session = $context->getSession();

        $type = $data->get('type');
        switch ($type) {
        case 'init':
            $result = array();
            if (!BlAbstract::createInstance('user')->existUsers()) {
                $result['init'] = true;
            } else {
                $onetime = md5(uniqid(rand(), true));
                $session->set('onetime', $onetime);
                $result['onetime'] = $onetime;
                $result['init'] = false;
            }

            $context->setViewJson($result);
            break;

        case 'auth':
            $login_id = $data->get('login_id');
            $password = $data->get('passward');
            $onetime  = $session->get('onetime');

            if (!$login_id || !$password || !$onetime) {
                throw new BlueDriveAjaxException('invalid access. onetime value not found');
            }

            $result = array();
            try {
                $login_user = BlAbstract::createInstance('user')->getLoginUser($login_id, $password, $onetime);
                $user = $context->createUser($login_user->user_id, $login_user->user_name);
                $user->adminFlag = ($login_user->admin_flag == '1');
                if (!$user->adminFlag) {
                    $access_realms = array();
                    // TOPはだれでもアクセス可能
                    $access_realms['0'] = '/admin/index\.php';
                    foreach (BlAbstract::createInstance('group')->getGroupInRealms($login_user->group_id) as $row) {
                        if ($row['VALID_FLAG'] == '1') {
                            $access_realms[$row['REALM_ID']] = $row['REALM'];
                        }
                    }
                    $user->accessRealms = $access_realms;
                }

                $databases = self::getCrudDatabases();
                if (count($databases) > 0) {
                    list($name, $values) = each($databases);
                    $user->crudCurrentDatabase = $name;
                    $user->crudCurrentOutputDir = $values['outputDir'];
                    $user->crudCurrentConnectionString = $values['connectionString'];
                }
                $context->setUser($user);

                $system_data = new DataSessionSystem();
                $system_data->crudDatabaseCount = count($databases);
                $session->set(DataSessionSystem::SESSION_KEY, $system_data);

                $session->remove('onetime');

                $result['valid'] = true;
                $result['url']   = $data->get('App.url_admin_base');

            } catch (BlueDriveAuthorizationException $e) {
                $login_log_message  = "login failure ({$login_id})";
                $login_log_message .= ' [REMOTE_ADDR: ' . (isset($_SERVER['REMOTE_ADDR'])     ? $_SERVER['REMOTE_ADDR']     : '') . ']';
                $login_log_message .= ' [USER_AGENT: '  . (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '') . ']';
                $login_log_message .= ' [REFERRER: '    . (isset($_SERVER['HTTP_REFERER'])    ? $_SERVER['HTTP_REFERER']    : '') . ']';
                SyL_Logger::notice($login_log_message);

                $result['valid']   = false;
            }

            $context->setViewJson($result);
            break;

        default:
            throw new BlueDriveAjaxException('unknown type');
        }

        $session->close();
    }
}
