<?php
class DaoEntityBd_group extends SyL_DbDaoTableAbstract
{
protected $table = 'bd_group';
protected $primary = array (
  0 => 'GROUP_ID',
);
protected $uniques = array (
);
protected $foreigns = array (
);
protected $columns = array (
  'GROUP_ID' => 
  array (
    'type' => 'I',
    'validation' => 
    array (
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
  'GROUP_NAME' => 
  array (
    'type' => 'M',
    'validation' => 
    array (
      'require' => 
      array (
        'message' => '{$name}は必須です',
      ),
      'multibyte' => 
      array (
        'message' => '{$name}は{$max}文字以内で入力してください',
        'parameters' => 
        array (
          'max' => '50',
          'encoding' => 'utf-8',
        ),
      ),
    ),
  ),
  'DESCRIPTION' => 
  array (
    'type' => 'M',
    'validation' => 
    array (
      'multibyte' => 
      array (
        'message' => '{$name}は{$max}文字以内で入力してください',
        'parameters' => 
        array (
          'max' => '200',
          'encoding' => 'utf-8',
        ),
      ),
    ),
  ),
  'VALID_FLAG' => 
  array (
    'type' => 'M',
    'validation' => 
    array (
      'require' => 
      array (
        'message' => '{$name}は必須です',
      ),
      'multibyte' => 
      array (
        'message' => '{$name}は{$max}文字以内で入力してください',
        'parameters' => 
        array (
          'max' => '1',
          'encoding' => 'utf-8',
        ),
      ),
    ),
  ),
  'I_DATE' => 
  array (
    'type' => 'DT',
    'validation' => 
    array (
      'require' => 
      array (
        'message' => '{$name}は必須です',
      ),
      'date' => 
      array (
        'message' => '{$name}は日付で入力してください',
      ),
    ),
  ),
  'I_ID' => 
  array (
    'type' => 'I',
    'validation' => 
    array (
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
  'U_DATE' => 
  array (
    'type' => 'DT',
    'validation' => 
    array (
      'require' => 
      array (
        'message' => '{$name}は必須です',
      ),
      'date' => 
      array (
        'message' => '{$name}は日付で入力してください',
      ),
    ),
  ),
  'U_ID' => 
  array (
    'type' => 'I',
    'validation' => 
    array (
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
