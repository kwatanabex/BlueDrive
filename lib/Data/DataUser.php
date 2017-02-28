<?php
SyL_Loader::lib('FixedProperty');
    
class DataUser extends SyL_FixedProperty
{
    protected $properties = array(
      'user_id'   => null,
      'login_id'  => null,
      'user_name' => null,
      'email' => null,
      'valid_flag' => '0',
      'admin_flag' => '0',
      'group_id' => null,
      'i_date' => null,
      'u_date' => null
    );

}
