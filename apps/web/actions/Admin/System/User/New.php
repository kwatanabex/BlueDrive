<?php
SyL_Loader::userLib('Bl.Abstract');
SyL_Loader::userLib('ConfigPath');
require_once SYL_FRAMEWORK_DIR . '/Lib/Form/SyL_Form.php';

/**
 * Index クラス
 */
class Admin_System_User_New extends AppAdminSystemAction
{
    /**
     * デフォルトアクション処理
     *
     * @param SyL_ContextAbstract コンテキストオブジェクト
     * @param SyL_Data データオブジェクト
     */
    public function execute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $groups = BlAbstract::createInstance('group')->getGroupAll();

        $form = self::createForm();
        $select = $form->getElement('group_id');
        foreach ($groups as &$group) {
            $select->setOption($group->GROUP_NAME, $group->GROUP_ID);
        }
        $data->set('form', $form->getView());

        $user_root_url = $data->get('App.Admin.url_system_base') . 'user/';
        $this->addBreadcrumbs($data, 'ユーザー管理', $user_root_url);
        $this->addBreadcrumbs($data, '新規登録', '');
    }

    /**
     * Ajax呼び出し時にコールされるアクションメソッド
     * 
     * @param SyL_ContextAbstract フィールド情報管理オブジェクト
     * @param SyL_Data データオブジェクト
     */
    protected function executeAjax(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $result = array();

        $form = self::createForm(false);
        $form->validate();
        $view = $form->getView();

        if (!$view->isErrors()) {
            $admin_flag = ($view->getValue('admin_flag') == '1');
            $valid_flag = ($view->getValue('valid_flag') == '1');
            $group_id   = !$admin_flag ? $view->getValue('group_id') : null;
            $i_id = $context->getUser()->getId();

            $manager = BlAbstract::createInstance('user');
            $manager->registerUser($view->getValue('user_name'), $view->getValue('login_id'), $view->getValue('passwd'), $view->getValue('email'), $group_id, $admin_flag, $valid_flag, $i_id);
            $result['valid'] = true;
        } else {
            $result['valid'] = false;
            $result['messages'] = $view->getErrorMessages();
        }

        $context->setViewJson($result);
    }

    private static function createForm($default=true)
    {
        $form_config = SyL_FormConfigAbstract::createInstance(ConfigPath::getFormUserNewFile());
        $form = new SyL_Form('form1');
        $form->createElementFromConfig($form_config, $default);
        return $form;
    }
}
