<?php
SyL_Loader::userLib('Bl.Abstract');
SyL_Loader::userLib('Data.User');
SyL_Loader::userLib('BlueDriveException');

class BlUserManager extends BlAbstract
{
    /**
     * ユーザーが存在するか確認する
     */
    public function existUsers()
    {
        $access = $this->getDaoAccess('bd_user');
        return ($access->countUsers() > 0);
    }

    /**
     * ログイン時にユーザーを取得する
     */
    public function getLoginUser($login_id, $login_passwd, $onetime)
    {
        $row = $this->getDaoAccess('bd_user')->selectConditionLoginId($login_id);
        if (count($row) == 0) {
            throw new BlueDriveAuthorizationException("ログインIDが正しくありません ({$login_id})");
        }

        $record = $row[0];
        if (!$record->VALID_FLAG) {
            throw new BlueDriveAuthorizationException("有効なユーザーではありません ({$login_id})");
        }

        $correct_password = hash('sha256', $onetime . $record->LOGIN_PASSWD);
        if ($login_passwd != $correct_password) {
            throw new BlueDriveAuthorizationException("ログインパスワードが正しくありません ({$login_id})");
        }
        return self::createUser($record);
    }

    /**
     * ユーザーを取得する
     */
    public function getUser($user_id)
    {
        $access = $this->getDaoAccess('bd_user');
        $condition = $access->createCondition();
        $condition->addNull('LOGIN_ID', false);
        $record = $access->select($user_id, $condition);
        if ($record == null) {
            throw new BlueDriveAuthorizationException("ユーザーIDが正しくないか、または削除されています ({$user_id})");
        }
        return self::createUser($record);
    }

    private static function createUser(SyL_DbRecord $record)
    {
        $user = new DataUser();
        $user->user_id = $record->USER_ID;
        $user->login_id = $record->LOGIN_ID;
        $user->user_name = $record->USER_NAME;
        $user->email = $record->EMAIL;
        $user->group_id = $record->GROUP_ID;
        $user->valid_flag = $record->VALID_FLAG;
        $user->admin_flag = $record->ADMIN_FLAG;
        $user->i_date = $record->I_DATE;
        $user->u_date = $record->U_DATE;
        return $user;
    }

    /**
     * ユーザーの一覧を取得する
     */
    public function getUsers($user_name, $email, $page)
    {
        $select_rows = 20;

        $access = $this->getDaoAccess('bd_user');
        list($dbrows, $pager) = $access->selectUsers($user_name, $email, $page, $select_rows);

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


    /**
     * ユーザーを登録する
     */
    public function registerUser($user_name, $login_id, $login_passwd, $email, $group_id, $admin_flag, $valid_flag, $i_id)
    {
        $current_date = date('Y-m-d H:i:s');

        $access = $this->getDaoAccess('bd_user');
        $record = $access->createRecord(true);
        $record->USER_NAME = $user_name;
        $record->LOGIN_ID =  $login_id;
        $record->LOGIN_PASSWD = $login_passwd;
        $record->EMAIL = $email;
        $record->GROUP_ID = $group_id;
        $record->ADMIN_FLAG = $admin_flag ? '1' : '0';
        $record->VALID_FLAG = $valid_flag ? '1' : '0';
        $record->I_DATE = $current_date;
        $record->I_ID = $i_id;
        $record->U_DATE = $current_date;
        $record->U_ID = $i_id;
        $access->insert($record);
    }

    /**
     * ユーザーを編集する
     */
    public function editUser($user_id, $user_name, $email, $group_id, $admin_flag, $valid_flag, $u_id)
    {
        $current_date = date('Y-m-d H:i:s');

        $access = $this->getDaoAccess('bd_user');
        $record = $access->createRecord(true);
        $record->USER_NAME = $user_name;
        $record->EMAIL = $email;
        $record->GROUP_ID = $group_id;
        $record->ADMIN_FLAG = $admin_flag ? '1' : '0';
        $record->VALID_FLAG = $valid_flag ? '1' : '0';
        $record->U_DATE = $current_date;
        $record->U_ID = $u_id;

        $condition = $access->createCondition();
        $condition->addNull('LOGIN_ID', false);

        if ($access->update($record, $user_id, $condition) == 0) {
            throw new SyL_DbRowNotFoundException('ユーザー更新時に更新件数がありませんでした');
        }
    }

    /**
     * パスワードを編集する
     */
    public function editUserPassword($user_id, $login_passwd, $u_id)
    {
        $current_date = date('Y-m-d H:i:s');

        $access = $this->getDaoAccess('bd_user');
        $record = $access->createRecord(true);
        $record->LOGIN_PASSWD = $login_passwd;
        $record->U_DATE = $current_date;
        $record->U_ID = $u_id;
        
        $condition = $access->createCondition();
        $condition->addNull('LOGIN_ID', false);

        if ($access->update($record, $user_id, $condition) == 0) {
            throw new SyL_DbRowNotFoundException('ユーザー更新時に更新件数がありませんでした');
        }
    }
    
    /**
     * ユーザーを削除する
     */
    public function removeUser($user_id, $u_id)
    {
        $current_date = date('Y-m-d H:i:s');

        $access = $this->getDaoAccess('bd_user');
        $record = $access->createRecord(true);
        $record->LOGIN_ID = null;
        $record->DELETED_LOGIN_ID = $this->getUser($user_id)->login_id;
        $record->U_DATE = $current_date;
        $record->U_ID = $u_id;

        $condition = $access->createCondition();
        $condition->addNull('LOGIN_ID', false);

        if ($access->update($record, $user_id, $condition) == 0) {
            throw new SyL_DbRowNotFoundException('ユーザー削除時に更新件数がありませんでした');
        }
    }
}
