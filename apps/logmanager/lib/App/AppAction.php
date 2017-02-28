<?php

abstract class AppAction extends SyL_ActionAbstract
{
    public function preExecute(SyL_ContextAbstract $context, SyL_Data $data)
    {
    }

    public function validate(SyL_ContextAbstract $context, SyL_Data $data)
    {
        parent::validate($context, $data);
    }

    public function postExecute(SyL_ContextAbstract $context, SyL_Data $data)
    {
    }
}
