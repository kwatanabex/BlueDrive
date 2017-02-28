<?php
require_once 'DaoAccessAbstract.php';
require_once dirname(__FILE__) . '/../Entity/DaoEntityBd_file_area.php';

class DaoAccessBd_file_area extends DaoAccessAbstract
{
protected $main_alias = 'a';
protected $class_names = array (
  'a' => 'DaoEntityBd_file_area'
);
protected $relations = array(
);

    /**
     * ƒtƒ@ƒCƒ‹•Û‘¶—Ìˆæˆê——‚ğæ“¾‚·‚é
     */
    public function selectFileAreas($storage_type, $page_count=1, $row_count=20)
    {
        $pager = $this->dao->getPager($row_count, $page_count);

        $table = $this->createTableObject();
        if (($storage_type !== '') && ($storage_type !== null)) {
            $condition = $this->dao->createCondition();
            $condition->addEqual('STORAGE_TYPE', $storage_type);
            $table->addCondition($condition);
        }
        $table->addSortColumn('FILE_AREA_NAME');
        $table->addSortColumn('I_DATE', false);

        return array($this->dao->select(array($table), null, $pager), $pager);
    }
}
