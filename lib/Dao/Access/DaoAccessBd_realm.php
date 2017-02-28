<?php
require_once 'DaoAccessAbstract.php';
require_once dirname(__FILE__) . '/../Entity/DaoEntityBd_realm.php';

class DaoAccessBd_realm extends DaoAccessAbstract
{
protected $main_alias = 'a';
protected $class_names = array (
  'a' => 'DaoEntityBd_realm'
);
protected $relations = array(
);

    /**
     * ”ÍˆÍˆê——‚ğæ“¾‚·‚é
     */
    public function selectRealms($realm_name=null, $realm=null, $page_count=1, $row_count=20)
    {
        $pager = $this->dao->getPager($row_count, $page_count);

        $table = $this->createTableObject();
        $condition = $this->dao->createCondition();
        if (($realm_name !== '') && ($realm_name !== null)) {
            $condition->addLike('REALM_NAME', '%' . $realm_name . '%');
        }
        if (($realm !== '') && ($realm !== null)) {
            $condition->addLike('REALM', '%' . $realm . '%');
        }
        $table->addCondition($condition);
        $table->addSortColumn('REALM');
        $table->addSortColumn('I_DATE', false);

        return array($this->dao->select(array($table), null, $pager), $pager);
    }
}
