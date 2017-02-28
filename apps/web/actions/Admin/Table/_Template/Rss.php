<?php

class Admin_Table_Template_Rss extends AppAdminTableAction
{
    /**
     * デフォルトアクション処理
     *
     * @param SyL_ContextAbstract コンテキストオブジェクト
     * @param SyL_Data データオブジェクト
     */
    public function execute(SyL_ContextAbstract $context, SyL_Data $data)
    {
        $config = $this->createCrudConfig($context, $data, SyL_CrudConfigAbstract::CRUD_TYPE_RSS);
        if (!$config->enableCrud()) {
            throw new BlueDriveAuthorizationException('RSS表示権限がありません');
        }

        $page = SyL_CrudPageAbstract::createInstance($config);

        $rss = null;
        try {
            $rss = $page->getRss();
        } catch (SyL_CrudNotFoundException $e) {
            throw new BlueDriveConfigException('RSS情報が正しく設定されていません', $e);
        }

        $context->setViewRss($rss);
    }

}
