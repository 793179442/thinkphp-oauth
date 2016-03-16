<?php
namespace Home\Controller;
//include_once '../Think/PhpUnit.php';
//include_once '../Think/PhpunitHelper.php';
require '../vendor/autoload.php';

class IndexControllerTest extends \PHPUnit_Framework_TestCase
{
    use \Think\PhpUnit; // ֻ�п��������������Ҫ��

    static public function setupBeforeClass()
    {
        // �������д���ģ���һ��Ӧ��ʵ��, ÿһ�ж��ܹؼ�, ����ȷ���ò���
        self::$app = new \Think\PhpunitHelper();
        self::$app->setMVC('domain.com','Home','Index');
        self::$app->setTestConfig(['DB_NAME'=>'test', 'DB_HOST'=>'127.0.0.1',]); // һ��Ҫ����һ�������õ����ݿ�,������Թ����ƻ���������
        self::$app->start();
    }

    /**
     * ������action�������ʾ��
     */
    public function testIndex()
    {
        $output = $this->execAction('index');
        $this->assertEquals('hello world',$output);
    }
}