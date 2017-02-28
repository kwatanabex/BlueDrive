<?php
SyL_Loader::userLib('Bl.Abstract');
SyL_Loader::userLib('ConfigPath');
require_once SYL_FRAMEWORK_DIR . '/Lib/Form/SyL_Form.php';

/**
 * Index クラス
 */
class Admin_User_Password_Index extends AppAdminUserAction
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

        $data->set('form', self::createForm()->getView());

        $user_root_url = $data->get('App.Admin.url_user_base');
        $this->addBreadcrumbs($data, 'ユーザー管理', $user_root_url);
        $this->addBreadcrumbs($data, 'パスワード変更', '');
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
            $id = $context->getUser()->getId();
            BlAbstract::createInstance('user')->editUserPassword($id, $view->getValue('passwd'), $id);
            $result['valid'] = true;
        } else {
            $result['valid'] = false;
            $result['messages'] = $view->getErrorMessages();
        }

        $context->setViewJson($result);
    }

    private static function createForm()
    {
        $form_config = SyL_FormConfigAbstract::createInstance(ConfigPath::getFormUserPasswordSelfFile());
        $form = new SyL_Form('form1');
        $form->createElementFromConfig($form_config);
        return $form;
    }
}
