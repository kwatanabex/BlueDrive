<?php
SyL_Loader::userLib('Bl.Abstract');
SyL_Loader::userLib('ConfigPath');
require_once SYL_FRAMEWORK_DIR . '/Lib/Form/SyL_Form.php';

/**
 * Index クラス
 */
class Admin_System_Group_New extends AppAdminSystemAction
{
    /**
     * デフォルトアクション処理
     *
     * @param SyL_ContextAbstract コンテキストオブジェクト
     * @param SyL_Data データオブジェクト
     */
    public function execute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $data->set('form', self::createForm()->getView());

        $group_root_url = $data->get('App.Admin.url_system_base') . 'group/';
        $this->addBreadcrumbs($data, 'グループ管理', $group_root_url);
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
            $valid_flag = ($view->getValue('valid_flag') == '1');
            $i_id = $context->getUser()->getId();

            BlAbstract::createInstance('group')->registerGroup($view->getValue('group_name'), $view->getValue('description'), $valid_flag, $i_id);
            $result['valid'] = true;
        } else {
            $result['valid'] = false;
            $result['messages'] = $view->getErrorMessages();
        }

        $context->setViewJson($result);
    }

    private static function createForm($default=true)
    {
        $form_config = SyL_FormConfigAbstract::createInstance(ConfigPath::getFormGroupNewFile());
        $form = new SyL_Form('form1');
        $form->createElementFromConfig($form_config, $default);
        return $form;
    }
}
