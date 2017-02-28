<?php
require_once 'DaoAccessAbstract.php';
require_once dirname(__FILE__) . '/../Entity/DaoEntityBd_syl_log.php';

class DaoAccessBd_syl_log extends DaoAccessAbstract
{
protected $main_alias = 'a';
protected $class_names = array (
  'a' => 'DaoEntityBd_syl_log'
);
protected $relations = array(
);

    public function selectLogs($system_log, array $log_levels, $project_name, $log, $page_count=1, $row_count=20)
    {
        $pager = $this->dao->getPager($row_count, $page_count);

        $table = $this->createTableObject();
        $condition = $this->dao->createCondition();
        if ($system_log !== null) {
            $condition->addEqual('system_log', $system_log);
        }
        if (count($log_levels) > 0) {
            $condition->addIn('log_level', $log_levels);
        }
        if ($project_name !== null) {
            $condition->addEqual('project_name', $project_name);
        }
        if (($log !== '') && ($log !== null)) {
            $condition->addLike('log', '%' . $log . '%');
        }
        $table->addCondition($condition);
        $table->addSortColumn('log_date', false);
        $table->addSortColumn('log_id', false);

        return array($this->dao->select(array($table), null, $pager), $pager);
    }

}
