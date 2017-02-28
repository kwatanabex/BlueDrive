<?php
SyL_Loader::userLib('Bl.Abstract');
SyL_Loader::userLib('ConfigPath');
SyL_Loader::userLib('Bl.FileStorageType');
require_once SYL_FRAMEWORK_DIR . '/Lib/Form/SyL_Form.php';

/**
 * Index クラス
 */
class Admin_System_Area_Edit extends AppAdminSystemAction
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

        $file_area = BlAbstract::createInstance('file')->getFileArea($id);

        $form = self::createForm(array($file_area, '__get'));
        $select = $form->getElement('storage_type');
        $select->setOption('-- 選択してください --', '');
        foreach (BlFileStorageType::getList() as $name => $value) {
            $select->setOption($name, $value);
        }
        $data->set('form', $form->getView());

        $file_area_root_url = $data->get('App.Admin.url_file_base') . 'area/';
        $this->addBreadcrumbs($data, 'ファイル保存領域管理', $file_area_root_url);
        $this->addBreadcrumbs($data, '編集', '');
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
            $i_id = $context->getUser()->getId();

            $manager = BlAbstract::createInstance('file');
            $manager->editFileArea($view->getValue('file_area_id'), $view->getValue('file_area_name'), $view->getValue('storage_type'), $view->getValue('root_directory'), $view->getValue('root_url'), $view->getValue('connection_string'), $valid_flag, $i_id);
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
        $form_config = SyL_FormConfigAbstract::createInstance(ConfigPath::getFormFileAreaEditFile());
        $form = new SyL_Form('form1');
        $form->createElementFromConfig($form_config);
        return $form;
    }
}
