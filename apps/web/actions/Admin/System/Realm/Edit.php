<?php
SyL_Loader::userLib('Bl.Abstract');
SyL_Loader::userLib('ConfigPath');
require_once SYL_FRAMEWORK_DIR . '/Lib/Form/SyL_Form.php';

/**
 * Index クラス
 */
class Admin_System_Realm_Edit extends AppAdminSystemAction
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

        $realm = BlAbstract::createInstance('realm')->getRealm($id);
        $data->set('form', self::createForm(array($realm, '__get'))->getView());

        $realm_root_url = $data->get('App.Admin.url_system_base') . 'realm/';
        $this->addBreadcrumbs($data, 'アクセス範囲管理', $realm_root_url);
        $this->addBreadcrumbs($data, '変更', '');
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
            $valid_flag = ($view->getValue('valid_flag') == '1');
            $id = $context->getUser()->getId();

            BlAbstract::createInstance('realm')->editRealm($view->getValue('realm_id'), $view->getValue('realm_name'), $view->getValue('realm'), $valid_flag, $id);
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
        $form_config = SyL_FormConfigAbstract::createInstance(ConfigPath::getFormRealmEditFile());
        $form = new SyL_Form('form1');
        $form->createElementFromConfig($form_config);
        return $form;
    }
}
