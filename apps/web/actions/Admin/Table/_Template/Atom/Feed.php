<?php

class Admin_Table_Template_Atom_Feed extends AppAdminTableAction
{
    /**
     * デフォルトアクション処理
     *
     * @param SyL_ContextAbstract コンテキストオブジェクト
     * @param SyL_Data データオブジェクト
     */
    public function execute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $page_count = $data->get('__page');
        if (!is_numeric($page_count) || ($page_count <= 0)) {
            $page_count = 1;
        }

        $config = $this->createCrudConfig($context, $data, SyL_CrudConfigAbstract::CRUD_TYPE_ATM);
        if (!$config->enableCrud()) {
            throw new BlueDriveAuthorizationException('Atom表示権限がありません');
        }

        $page = SyL_CrudPageAbstract::createInstance($config);

        $atom = null;
        try {
            $atom = $page->getAtomFeed($page_count);
        } catch (SyL_CrudNotFoundException $e) {
            throw new BlueDriveConfigException('Atom情報が正しく設定されていません', E_USER_ERROR, $e);
        }

        $context->setViewAtom($atom);
    }

}
