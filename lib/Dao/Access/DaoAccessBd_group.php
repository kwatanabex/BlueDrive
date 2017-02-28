<?php
require_once 'DaoAccessAbstract.php';
require_once dirname(__FILE__) . '/../Entity/DaoEntityBd_group.php';
require_once dirname(__FILE__) . '/../Entity/DaoEntityBd_realm.php';
require_once dirname(__FILE__) . '/../Entity/DaoEntityBd_realm_group.php';

class DaoAccessBd_group extends DaoAccessAbstract
{
protected $main_alias = 'a';
protected $class_names = array (
  'a' => 'DaoEntityBd_group'
);
protected $relations = array(
);

    /**
     * グループ一覧を取得する
     */
    public function selectGroups($group_name=null, $page_count=1, $row_count=20)
    {
        $pager = $this->dao->getPager($row_count, $page_count);

        $table = $this->createTableObject();
        $condition = $this->dao->createCondition();
        if (($group_name !== '') && ($group_name !== null)) {
            $condition->addLike('GROUP_NAME', '%' . $group_name . '%');
        }
        $table->addCondition($condition);
        $table->addSortColumn('GROUP_NAME');
        $table->addSortColumn('I_DATE', false);

        return array($this->dao->select(array($table), null, $pager), $pager);
    }
    
    /**
     * グループに紐づく範囲の一覧を取得する
     */
    public function selectGroupInRealms($group_id)
    {
        $realm_group = new DaoEntityBd_realm_group();
        $condition = $this->dao->createCondition();
        $condition->addEqual('GROUP_ID', $group_id);
        $realm_group->addCondition($condition);

        $realm = new DaoEntityBd_realm();
        $realm->set('REALM_NAME');
        $realm->set('REALM');
        $realm->set('VALID_FLAG');
        $realm->addSortColumn('REALM');
        $realm->addSortColumn('I_DATE', false);

        $relation = $this->dao->createRelation();
        $relation->addJoin($realm_group, $realm, array('REALM_ID'));

        return $this->dao->select(array($realm_group, $realm), $relation);
    }
}
