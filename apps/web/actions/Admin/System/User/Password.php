<?php
SyL_Loader::userLib('Bl.Abstract');
SyL_Loader::userLib('ConfigPath');
require_once SYL_FRAMEWORK_DIR . '/Lib/Form/SyL_Form.php';

/**
 * Index クラス
 */
class Admin_System_User_Password extends AppAdminSystemAction
{
    /**
     * デフォルトアクション処理
     *
     * @param SyL_ContextAbstract コンテキストオブジェクト
     * @param SyL_Data データオブジェクト
     */
    public function execute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $id = $data->get('id');
        if (!is_numeric($id) || ($id < 1)) {
            throw new SyL_InvalidParameterException('パラメータが正しくありません');
        }

        $user = BlAbstract::createInstance('user')->getUser($id);

        $form = self::createForm();
        $form->getElement('user_id')->setValue($id);
        $form->getElement('user_name')->setValue($user->user_name);
        $form->getElement('i_date')->setValue($user->i_date);
        $form->getElement('u_date')->setValue($user->u_date);
        $data->set('form', $form->getView());

        $user_root_url = $data->get('App.Admin.url_system_base') . 'user/';
        $this->addBreadcrumbs($data, 'ユーザー管理', $user_root_url);
        $this->addBreadcrumbs($data, 'パスワード編集', '');
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
            BlAbstract::createInstance('user')->editUserPassword($view->getValue('user_id'), $view->getValue('passwd'), $context->getUser()->getId());
            $result['valid'] = true;
        } else {
            $result['valid'] = false;
            $result['messages'] = $view->getErrorMessages();
        }

        $context->setViewJson($result);
    }

    private static function createForm()
    {
        $form_config = SyL_FormConfigAbstract::createInstance(ConfigPath::getFormUserPasswordFile());
        $form = new SyL_Form('form1');
        $form->createElementFromConfig($form_config);
        return $form;
    }
}
