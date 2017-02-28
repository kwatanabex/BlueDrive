<?php
SyL_Loader::userLib('Bl.Abstract');
SyL_Loader::userLib('Data.Realm');
SyL_Loader::userLib('BlueDriveException');

class BlRealmManager extends BlAbstract
{
    /**
     * 範囲を取得する
     */
    public function getRealm($realm_id)
    {
        $record = $this->getDaoAccess('bd_realm')->select($realm_id);
        if ($record == null) {
            throw new BlueDriveAuthorizationException("範囲IDが正しくないか、または削除されています ({$realm_id})");
        }

        $realm = new DataRealm();
        $realm->realm_id = $record->REALM_ID;
        $realm->realm_name = $record->REALM_NAME;
        $realm->realm = $record->REALM;
        $realm->valid_flag = $record->VALID_FLAG;
        $realm->i_date = $record->I_DATE;
        $realm->u_date = $record->U_DATE;
        return $realm;
    }

    /**
     * 範囲一覧を取得する
     */
    public function getRealms($realm_name, $realm, $page)
    {
        $select_rows = 20;

        list($dbrows, $pager) = $this->getDaoAccess('bd_realm')->selectRealms($realm_name, $realm, $page, $select_rows);

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
     * 範囲一覧を取得する
     */
    public function getRealmAll()
    {
        $access = $this->getDaoAccess('bd_realm');
        return $access->selects($access->createCondition() ,array('REALM' => true, 'I_DATE' => false));
    }

    /**
     * 範囲を登録する
     */
    public function registerRealm($realm_name, $realm, $valid_flag, $i_id)
    {
        $current_date = date('Y-m-d H:i:s');

        $access = $this->getDaoAccess('bd_realm');
        $record = $access->createRecord(true);
        $record->REALM_NAME = $realm_name;
        $record->REALM = $realm;
        $record->VALID_FLAG = $valid_flag ? '1' : '0';
        $record->I_DATE = $current_date;
        $record->I_ID = $i_id;
        $record->U_DATE = $current_date;
        $record->U_ID = $i_id;
        $access->insert($record);
    }

    /**
     * 範囲を編集する
     */
    public function editRealm($realm_id, $realm_name, $realm, $valid_flag, $u_id)
    {
        $access = $this->getDaoAccess('bd_realm');
        $record = $access->createRecord(true);
        $record->REALM_NAME = $realm_name;
        $record->REALM = $realm;
        $record->VALID_FLAG = $valid_flag ? '1' : '0';
        $record->U_DATE = date('Y-m-d H:i:s');
        $record->U_ID = $u_id;

        if ($access->update($record, $realm_id) == 0) {
            throw new SyL_DbRowNotFoundException('範囲更新時に更新件数がありませんでした');
        }
    }
}
