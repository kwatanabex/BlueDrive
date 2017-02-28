<?php
SyL_Loader::userLib('BlueDriveException');

abstract class AppAction extends SyL_ActionAbstract
{
    /**
     * �A�N�V�������\�b�h���s�O�Ɏ��s����郁�\�b�h
     *
     * @param SyL_ContextAbstract �t�B�[���h���Ǘ��I�u�W�F�N�g
     * @param SyL_Data �f�[�^�I�u�W�F�N�g
     */
    public function preExecute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
            $this->setActionMethod('executeAjax');
        }

        $url_base = $context->getRequest()->getUrlBase() . '/';
        $url_admin_base = $url_base . 'admin/';

        $url_root = dirname($url_base);
        if ($url_root == '/') {
            $url_root = '';
        }

        $data->set('App.url_root', $url_root);
        $data->set('App.url_base', $url_base);
        $data->set('App.url_admin_base', $url_admin_base);
    }

    /**
     * Ajax�Ăяo�����ɃR�[�������A�N�V�������\�b�h
     * 
     * @param SyL_ContextAbstract �t�B�[���h���Ǘ��I�u�W�F�N�g
     * @param SyL_Data �f�[�^�I�u�W�F�N�g
     */
    protected function executeAjax(SyL_ContextAbstract $context, SyL_Data $data)
    {
        throw new SyL_NotImplementedException('ajax method not implemented in action class');
    }

    /**
     * �A�N�V�������\�b�h���s��Ɏ��s����郁�\�b�h
     * 
     * @param SyL_ContextAbstract �t�B�[���h���Ǘ��I�u�W�F�N�g
     * @param SyL_Data �f�[�^�I�u�W�F�N�g
     */
    public function postExecute(SyL_ContextAbstract $context, SyL_Data $data)
    {
    }


    protected static function getCrudDatabases()
    {
        $config = SyL_ConfigFileAbstract::createInstance('dao');
        $config->parse(false);
        $result = $config->getConfig();

        return $result['database'];
    }
}
