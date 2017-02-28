<?php
SyL_Loader::userLib('Bl.Abstract');
SyL_Loader::userLib('ConfigPath');
require_once SYL_FRAMEWORK_DIR . '/Lib/Form/SyL_Form.php';

/**
 * Index クラス
 */
class Admin_User_Edit_Index extends AppAdminUserAction
{
    /**
     * デフォルトアクション処理
     *
     * @param SyL_ContextAbstract コンテキストオブジェクト
     * @param SyL_Data データオブジェクト
     */
    public function execute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $id = $context->getUser()->getId();
        $user = BlAbstract::createInstance('user')->getUser($id);
        $data->set('form', self::createForm(array($user, '__get'))->getView());

        $user_root_url = $data->get('App.Admin.url_user_base');
        $this->addBreadcrumbs($data, 'ユーザー管理', $user_root_url);
        $this->addBreadcrumbs($data, 'ユーザー情報変更', '');
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

        $form = self::createForm();
        $form->validate();
        $view = $form->getView();

        if (!$view->isErrors()) {
            $user = $context->getUser();
            $id = $user->getId();

            $manager = BlAbstract::createInstance('user');
            $user = $manager->getUser($id);
            $admin_flag = ($user->admin_flag == '1');
            $valid_flag = ($user->valid_flag == '1');

            $manager->editUser($id, $view->getValue('user_name'), $view->getValue('email'), $user->group_id, $admin_flag, $valid_flag, $id);

            self::recreateUser($context, $id, $view->getValue('user_name'));

            $result['valid'] = true;
        } else {
            $result['valid'] = false;
            $result['messages'] = $view->getErrorMessages();
        }

        $context->setViewJson($result);
    }

    private static function createForm($callback=null)
    {
        if ($callback) {
            SyL_Form::registerInputCallback($callback);
        }
        $form_config = SyL_FormConfigAbstract::createInstance(ConfigPath::getFormUserEditSelfFile());
        $form = new SyL_Form('form1');
        $form->createElementFromConfig($form_config);
        return $form;
    }
    
    private static function recreateUser(SyL_ContextAbstract $context, $user_id, $user_name)
    {
        $user = $context->getUser();
        $new_user = $context->createUser($user_id, $user_name);
        $new_user->crudCurrentDatabase = $user->crudCurrentDatabase;
        $new_user->crudCurrentOutputDir = $user->crudCurrentOutputDir;
        $new_user->crudCurrentConnectionString = $user->crudCurrentConnectionString;
        $context->setUser($new_user);
    }
}
