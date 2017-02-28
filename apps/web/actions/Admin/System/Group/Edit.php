<?php
SyL_Loader::userLib('Bl.Abstract');
SyL_Loader::userLib('ConfigPath');
require_once SYL_FRAMEWORK_DIR . '/Lib/Form/SyL_Form.php';

/**
 * Index クラス
 */
class Admin_System_Group_Edit extends AppAdminSystemAction
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

        $group = BlAbstract::createInstance('group')->getGroup($id);
        $data->set('form', self::createForm(array($group, '__get'))->getView());

        $realm_list = BlAbstract::createInstance('realm')->getRealmAll();
        $data->set('realm_list', $realm_list);

        $group_root_url = $data->get('App.Admin.url_system_base') . 'group/';
        $this->addBreadcrumbs($data, 'グループ管理', $group_root_url);
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
        $result['valid'] = false;

        $type = $data->get('type');

        switch ($type) {
        case 'edit':
            $form = self::createForm();
            $form->validate();
            $view = $form->getView();

            if (!$view->isErrors()) {
                $valid_flag = ($view->getValue('valid_flag') == '1');
                $id = $context->getUser()->getId();

                BlAbstract::createInstance('group')->editGroup($view->getValue('group_id'), $view->getValue('group_name'), $view->getValue('description'), $valid_flag, $id);
                $result['valid'] = true;
            } else {
                $result['valid'] = false;
                $result['messages'] = $view->getErrorMessages();
            }
            break;

        case 'list':
            $id = $data->get('group_id');
            if (!is_numeric($id) || ($id < 1)) {
                throw new SyL_InvalidParameterException('パラメータが正しくありません');
            }

            $rows = BlAbstract::createInstance('group')->getGroupInRealms($id);
            $result['valid'] = true;
            $result['rows'] = $rows;
            break;

        case 'update':
            $id = $data->get('group_id');
            if (!is_numeric($id) || ($id < 1)) {
                throw new SyL_InvalidParameterException('パラメータが正しくありません');
            }

            $realm_id_tmp = $data->get('realm_ids');
            $realm_ids = array();
            if (trim($realm_id_tmp)) {
                $realm_ids = array_map('trim', explode(',', $realm_id_tmp));
            }

            BlAbstract::createInstance('group')->updateGroupInRealms($id, $realm_ids);

            $result['valid'] = true;
            break;
        }

        $context->setViewJson($result);
    }

    private static function createForm($callback=null)
    {
        if ($callback) {
            SyL_Form::registerInputCallback($callback);
        }
        $form_config = SyL_FormConfigAbstract::createInstance(ConfigPath::getFormGroupEditFile());
        $form = new SyL_Form('form1');
        $form->createElementFromConfig($form_config);
        return $form;
    }
}
