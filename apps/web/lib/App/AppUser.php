<?php

class AppUser extends SyL_UserAbstract
{
    protected $properties = array(
        'adminFlag' => false, // �Ǘ��҃��[�U�[
        'accessRealms' => array(), // �A�N�Z�X���͈�
        'crudCurrentDatabase' => null, // ���݂̊Ǘ��Ώۃf�[�^�x�[�X
        'crudCurrentOutputDir' => null, // ���݂�CRUD�����f�B���N�g��
        'crudCurrentConnectionString' => null, // ���݂�CRUD�f�[�^�x�[�X�ڑ�������
    );
}
