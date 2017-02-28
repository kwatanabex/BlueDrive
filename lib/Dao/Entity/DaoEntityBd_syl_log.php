<?php
class DaoEntityBd_syl_log extends SyL_DbDaoTableAbstract
{
protected $table = 'bd_syl_log';
protected $primary = array (
  0 => 'LOG_ID',
);
protected $uniques = array (
);
protected $foreigns = array (
);
protected $columns = array (
  'LOG_ID' => 
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
          'min' => '-9223372036854775808',
          'max' => '9223372036854775807',
          'min_error_message' => '{$name}は{$min}以上で入力してください',
          'max_error_message' => '{$name}は{$max}以下で入力してください',
        ),
      ),
    ),
  ),
  'LOG_DATE' => 
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
  'SYSTEM_LOG' => 
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
  'LOG_LEVEL' => 
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
          'max' => '8',
          'encoding' => 'utf-8',
        ),
      ),
    ),
  ),
  'HOSTNAME' => 
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
          'max' => '64',
          'encoding' => 'utf-8',
        ),
      ),
    ),
  ),
  'PROJECT_NAME' => 
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
          'max' => '64',
          'encoding' => 'utf-8',
        ),
      ),
    ),
  ),
  'APPLICATION_NAME' => 
  array (
    'type' => 'M',
    'validation' => 
    array (
      'multibyte' => 
      array (
        'message' => '{$name}は{$max}文字以内で入力してください',
        'parameters' => 
        array (
          'max' => '64',
          'encoding' => 'utf-8',
        ),
      ),
    ),
  ),
  'METHOD' => 
  array (
    'type' => 'M',
    'validation' => 
    array (
      'multibyte' => 
      array (
        'message' => '{$name}は{$max}文字以内で入力してください',
        'parameters' => 
        array (
          'max' => '128',
          'encoding' => 'utf-8',
        ),
      ),
    ),
  ),
  'LOG' => 
  array (
    'type' => 'M',
    'validation' => 
    array (
      'multibyte' => 
      array (
        'message' => '{$name}は{$max}文字以内で入力してください',
        'parameters' => 
        array (
          'max' => NULL,
          'encoding' => 'utf-8',
        ),
      ),
    ),
  ),
);

}
