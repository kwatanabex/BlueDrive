<?php
SyL_Loader::lib('FixedProperty');

class DataSessionSystem extends SyL_FixedProperty
{
    const SESSION_KEY = '__system_data';

    protected $properties = array(
      'crudDatabaseCount' => 0, // CRUD DBの設定数
    );
}
