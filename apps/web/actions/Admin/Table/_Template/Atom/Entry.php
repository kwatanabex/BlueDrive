<?php

class Admin_Table_Template_Atom_Entry extends AppAdminTableAction
{
    /**
     * デフォルトアクション処理
     *
     * @param SyL_ContextAbstract コンテキストオブジェクト
     * @param SyL_Data データオブジェクト
     */
    public function execute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $id = $data->get('__id');

        $config = $this->createCrudConfig($context, $data, SyL_CrudConfigAbstract::CRUD_TYPE_ATM);
        if (!$config->enableCrud()) {
            throw new BlueDriveAuthorizationException('Atom表示権限がありません');
        }

        $page = SyL_CrudPageAbstract::createInstance($config);
        $page->setId($id);

        $context->setViewAtom($page->getAtomEntry());
    }

}
