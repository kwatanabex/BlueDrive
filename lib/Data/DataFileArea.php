<?php
SyL_Loader::lib('FixedProperty');

class DataFileArea extends SyL_FixedProperty
{
    protected $properties = array(
      'file_area_id'   => null,
      'file_area_name' => null,
      'storage_type' => null,
      'storage_name' => null,
      'root_directory' => null,
      'root_url' => null,
      'connection_string' => null,
      'valid_flag' => '0',
      'i_date' => null,
      'u_date' => null
    );

}
