<?php
SyL_Loader::lib('FixedProperty');

class DataGroup extends SyL_FixedProperty
{
    protected $properties = array(
      'group_id'   => null,
      'group_name' => null,
      'description' => null,
      'valid_flag' => '0',
      'i_date' => null,
      'u_date' => null
    );

}
