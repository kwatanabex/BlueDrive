<?php
SyL_Loader::lib('FixedProperty');

class DataRealm extends SyL_FixedProperty
{
    protected $properties = array(
      'realm_id'   => null,
      'realm_name' => null,
      'realm' => null,
      'valid_flag' => '0',
      'i_date' => null,
      'u_date' => null
    );

}
