<?php
SyL_Loader::userLib('Bl.Abstract');
SyL_Loader::userLib('Data.Group');
SyL_Loader::userLib('BlueDriveException');

class BlGroupManager extends BlAbstract
{
    /**
     * グループを取得する
     */
    public function getGroup($group_id)
    {
        $record = $this->getDaoAccess('bd_group')->select($group_id);
        if ($record == null) {
            throw new BlueDriveAuthorizationException("グループIDが正しくないか、または削除されています ({$user_id})");
        }

        $group = new DataGroup();
        $group->group_id = $record->GROUP_ID;
        $group->group_name = $record->GROUP_NAME;
        $group->description = $record->DESCRIPTION;
        $group->valid_flag = $record->VALID_FLAG;
        $group->i_date = $record->I_DATE;
        $group->u_date = $record->U_DATE;
        return $group;
    }

    /**
     * グループ一覧を取得する
     */
    public function getGroups($group_name, $page)
    {
        $select_rows = 20;

        list($dbrows, $pager) = $this->getDaoAccess('bd_group')->selectGroups($group_name, $page, $select_rows);

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
     * グループ一覧を取得する
     */
    public function getGroupAll()
    {
        $access = $this->getDaoAccess('bd_group');
        return $access->selects($access->createCondition() ,array('GROUP_NAME' => true));
    }

    /**
     * グループを登録する
     */
    public function registerGroup($group_name, $description, $valid_flag, $i_id)
    {
        $current_date = date('Y-m-d H:i:s');

        $access = $this->getDaoAccess('bd_group');
        $record = $access->createRecord(true);
        $record->GROUP_NAME = $group_name;
        $record->DESCRIPTION = $description;
        $record->VALID_FLAG = $valid_flag ? '1' : '0';
        $record->I_DATE = $current_date;
        $record->I_ID = $i_id;
        $record->U_DATE = $current_date;
        $record->U_ID = $i_id;
        $access->insert($record);
    }

    /**
     * グループを編集する
     */
    public function editGroup($group_id, $group_name, $description, $valid_flag, $u_id)
    {
        $access = $this->getDaoAccess('bd_group');
        $record = $access->createRecord(true);
        $record->GROUP_NAME = $group_name;
        $record->DESCRIPTION = $description;
        $record->VALID_FLAG = $valid_flag ? '1' : '0';
        $record->U_DATE = date('Y-m-d H:i:s');
        $record->U_ID = $u_id;

        if ($access->update($record, $group_id) == 0) {
            throw new SyL_DbRowNotFoundException('グループ更新時に更新件数がありませんでした');
        }
    }

    /**
     * グループに紐づく範囲の一覧を取得する
     */
    public function getGroupInRealms($group_id)
    {
        $dbrows = $this->getDaoAccess('bd_group')->selectGroupInRealms($group_id);

        $rows = array();
        foreach ($dbrows as &$record) {
            $row = array();
            foreach ($record as $name => $value) {
                $row[$name] = $value;
            }
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * グループに紐づく範囲の更新する
     */
    public function updateGroupInRealms($group_id, array $realm_ids)
    {
        $access = $this->getDaoAccess('bd_realm_group');
        
        $this->beginTransaction();
        try {
            $condition = $access->createCondition();
            $condition->addEqual('GROUP_ID', $group_id);
            $access->deletes($condition);

            foreach ($realm_ids as $realm_id) {
                $record = $access->createRecord();
                $record->GROUP_ID = $group_id;
                $record->REALM_ID = $realm_id;
                $access->insert($record);
            }
            $this->commit();
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }
}
