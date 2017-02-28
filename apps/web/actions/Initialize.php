<?php
SyL_Loader::userLib('Bl.Abstract');
SyL_Loader::userLib('ConfigPath');
require_once SYL_FRAMEWORK_DIR . '/Lib/Form/SyL_Form.php';

/**
 * Initialize クラス
 */
class Initialize extends AppAction
{
    /**
     * デフォルトアクション処理
     *
     * @param SyL_ContextAbstract コンテキストオブジェクト
     * @param SyL_Data データオブジェクト
     */
    public function execute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        if (BlAbstract::createInstance('user')->existUsers()) {
            $context->getResponse()->redirect('login.html');
        }

        $data->set('form', self::createForm()->getView());
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
            $manager = BlAbstract::createInstance('user');
            $manager->registerUser($view->getValue('user_name'), $view->getValue('login_id'), $view->getValue('passwd'), $view->getValue('email'), null, true, true, 0);
            $result['valid'] = true;
        } else {
            $result['valid'] = false;
            $result['messages'] = $view->getErrorMessages();
        }

        $context->setViewJson($result);
    }

    private static function createForm()
    {
        $form_config = SyL_FormConfigAbstract::createInstance(ConfigPath::getFormUserInitializeFile());
        $form = new SyL_Form('form1');
        $form->createElementFromConfig($form_config);
        return $form;
    }
}
