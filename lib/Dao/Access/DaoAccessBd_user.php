<?php
require_once 'DaoAccessAbstract.php';
require_once dirname(__FILE__) . '/../Entity/DaoEntityBd_user.php';
require_once dirname(__FILE__) . '/../Entity/DaoEntityBd_group.php';

class DaoAccessBd_user extends DaoAccessAbstract
{
protected $main_alias = 'a';
protected $class_names = array (
  'a' => 'DaoEntityBd_user'
);
protected $relations = array(
);

    /**
     * ユーザー数を取得する
     */
    public function countUsers()
    {
        $table = $this->createTableObject();
        $table->set('user_id', 'cnt', array('count'));
        $result = $this->dao->select(array($table));
        return (int)$result[0]->cnt;
    }

    /**
     * ログインIDでユーザーを検索する
     */
    public function selectConditionLoginId($login_id)
    {
        $table = $this->createTableObject();
        $condition = $this->dao->createCondition();
        $condition->addEqual('login_id', $login_id);
        $table->addCondition($condition);
        return $this->dao->select(array($table));
    }
    
    /**
     * ユーザー一覧を取得する
     */
    public function selectUsers($user_name=null, $email=null, $page_count=1, $row_count=20)
    {
        $pager = $this->dao->getPager($row_count, $page_count);

        $table = $this->createTableObject();
        $table->set('USER_ID');
        $table->set('USER_NAME');
        $table->set('LOGIN_ID');
        $table->set('EMAIL');
        $table->set('GROUP_ID');
        $table->set('ADMIN_FLAG');
        $table->set('VALID_FLAG');
        $table->set('I_DATE');
        $table->set('U_DATE');
        $condition = $this->dao->createCondition();
        if (($user_name !== '') && ($user_name !== null)) {
            $condition->addLike('USER_NAME', '%' . $user_name . '%');
        }
        if (($email !== '') && ($email !== null)) {
            $condition->addLike('EMAIL', '%' . $email . '%');
        }
        $condition->addNull('LOGIN_ID', false);
        $table->addCondition($condition);
        $table->addSortColumn('I_DATE', false);

        $table_group = new DaoEntityBd_group();
        $table_group->set('GROUP_NAME');
        $relation = $this->dao->createRelation();
        $relation->addLeftJoin($table, $table_group, array('GROUP_ID'));

        return array($this->dao->select(array($table, $table_group), $relation, $pager), $pager);
    }
}
