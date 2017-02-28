<?php
class DaoEntityBd_realm_group extends SyL_DbDaoTableAbstract
{
protected $table = 'bd_realm_group';
protected $primary = array (
  0 => 'GROUP_ID',
  1 => 'REALM_ID',
);
protected $uniques = array (
);
protected $foreigns = array (
  'bd_group' => 
  array (
    'GROUP_ID' => 'GROUP_ID',
  ),
  'bd_realm' => 
  array (
    'REALM_ID' => 'REALM_ID',
  ),
);
protected $columns = array (
  'GROUP_ID' => 
  array (
    'type' => 'I',
    'validation' => 
    array (
      'require' => 
      array (
        'message' => '{$name}は必須です',
      ),
      'numeric' => 
      array (
        'message' => '{$name}は数値で入力してください',
        'parameters' => 
        array (
          'dot' => false,
          'min' => '-2147483648',
          'max' => '2147483647',
          'min_error_message' => '{$name}は{$min}以上で入力してください',
          'max_error_message' => '{$name}は{$max}以下で入力してください',
        ),
      ),
    ),
  ),
  'REALM_ID' => 
  array (
    'type' => 'I',
    'validation' => 
    array (
      'require' => 
      array (
        'message' => '{$name}は必須です',
      ),
      'numeric' => 
      array (
        'message' => '{$name}は数値で入力してください',
        'parameters' => 
        array (
          'dot' => false,
          'min' => '-2147483648',
          'max' => '2147483647',
          'min_error_message' => '{$name}は{$min}以上で入力してください',
          'max_error_message' => '{$name}は{$max}以下で入力してください',
        ),
      ),
    ),
  ),
);

}
