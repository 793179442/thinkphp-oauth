<?php
/**
 * Author: Michael
 * Date: 2016/1/19
 * Time: 15:47
 */
namespace  Home\Model;
use Think\Model;

class UsersModel extends Model
{
    /**
     * @param null $username
     * @return bool|mixed
     */
    public function select($username = null)
    {
        if ($username !== null) {
            return false;
        }

        $result = M('users')
            ->field('username, password, name, email, photo')
            ->where(['username'=>$username])
            ->select();

        if (!$result || count($result) === 0) {
            return false;
        }

        return $result;
    }


    // �Զ���֤����
    protected $_validate = array(
        array('username', 'require', '�û���������д��', 1),
        array('name', 'require', '����������д��', 1),
        array('email', 'email', '�����ʽ����', 2),
        array('username', '', '�û����Ѿ����ڣ�', 0, 'unique', 1),
    );

    //�Զ��������
    protected $_auto = array(
//        д�뵱ǰʱ���
        array('createtime', 'time', 1, 'function'),
        array('password', 'passwordHash', 3, 'function'),
        array('status', '1'),
//        д���û�ע��IP��ַ
        array('ip', 'get_client_ip', 1, 'function'),
    );

}