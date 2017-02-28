<?php

class AppSession extends SyL_SessionAbstract
{
    /**
     * �Z�b�V������
     *
     * @var string
     */
    protected $name = 'bdsid';
    /**
     * �N�b�L�[�p�X
     *
     * @var string
     */
    protected $cookie_path = '/';

    /**
     * �Z�b�V�������J�n����̏���
     */
    protected function startAfter()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            // GET�̏ꍇ�̂݁A�Z�b�V����ID��ύX����
            session_regenerate_id(true);
        }
    }
}
