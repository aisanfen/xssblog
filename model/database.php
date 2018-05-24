<?php

/**
 * Created by PhpStorm.
 * User: wangling
 * Date: 2018/5/16
 * Time: 21:56
 */
class database {
    private $config;//数据库配置参数

    /**
     * 构造函数，加载配置函数
     * database constructor.
     * @param $config
     */
    public function __construct($config) {
        $this->config = $config;
        if ($this->init_datebase()) {
            $this->init_tables();
        }
    }

    /**
     * 创建PDO数据库连接池
     * @return PDO
     */
    private function create_pdo_connect() {
        return new PDO($this->config['database'], $this->config['username'], $this->config['password']);
    }

    /**
     * 初始化数据库
     * 此函数当且仅当数据库不存在时进行初始化配置调用
     * @return bool|PDOStatement
     */
    private function init_datebase() {
        $pdo = new PDO($this->config['init'], $this->config['username'], $this->config['password']);
        $res = $pdo->query("create database " . $this->config['db_name']);
        return $res;
    }

    /**
     * 数据表初始化函数
     * 此函数只在数据库初始化函数调用时调用
     * @return bool|PDOStatement
     */
    private function init_tables() {
        $pdo = new PDO($this->config['init'], $this->config['username'], $this->config['password']);
        $res = $pdo->query("create database " . $this->config['db_name']);
        return $res;
    }

    /**
     * 登录检查函数
     * 检测输入用户名和密码是否存在
     * @param $username
     * @param $password
     * @return mixed|null
     */
    public function check_userinfo($username, $password) {
        $password = sha1(md5($username . $password));
        $sql = "select username from `user` where username=? and password=?";
        $exc = $this->create_pdo_connect()->prepare($sql);
        $exc->bindParam(1, $username);
        $exc->bindParam(2, $password);
        $exc->execute();
        return $exc->fetchAll();
    }

    /**
     * 通过用户id查询用户
     * @param $uid
     */
    public function get_user_info_by_id($uid) {
        $sql = "select username from `user` where id=?";
        $exc = $this->create_pdo_connect()->prepare($sql);
        $exc->bindParam(1, $uid);
        $exc->execute();
        $data = $exc->fetchAll();
    }

    /**
     * 通过用户名获取用户id
     * @param $username
     */
    private function get_user_id_by_username($username) {
        $sql = "select username from `user` where username=";
        $exc = $this->create_pdo_connect($sql)->prepare();
        $exc->bindParam(1, $username);
        $exc->execute();
        $data = $exc->fetchAll();
    }

    /**
     * 用户注册函数
     * @param $username
     * @param $password
     */
    public function regist_user($username, $password) {
        $pdo = $this->create_pdo_connect();
        $password = sha1(md5($username . $password));
        //检查用户名是否重复
        $sql = "select * from `user` where username=? and password=?";//?参数通配符
        //PDO预编译参数
        $exc = $pdo->prepare($sql);
        //参数绑定,反sql注入，只允许格式化有效参数$username与$password
        $exc->bindParam(1, $username);
        $exc->bindParam(2, $password);
        $exc->execute();//执行
        if (count($exc->fetchAll()) == 0) {
            $sql = "insert into `user` (username,password) values (?,?)";
            $exc = $pdo->prepare($sql);
            $exc->bindParam(1, $username);
            $exc->bindParam(2, $password);
            if($exc->execute()){
                return 1;
            }else return -1;

        }else return 0;
}

/**
 * 留言板内容存储
 * @param $username
 * @param $content
 */
public
function post_message($username, $content) {
    $sql = "select uid from `user` where username=?";

    $exc = $this->create_pdo_connect()->prepare($sql);
    $exc->bindParam(1, $username);
    $res = $exc->execute();
    if ($res) {
        $uid = $res->fetch()[0];
        $sql = "insert into `user` (uid,content) values (?,?)";

        $exc = $this->create_pdo_connect()->prepare($sql);
        $exc->bindParam(1, $uid);
        $exc->bindParam(2, $content);
        $exc->execute();
        $data = $exc->fetchAll();
        if ($data) return 1; else return 0;
    }
}

/**留言板回复
 * @param $username
 * @param $artical_id
 */
public
function post_recall($username, $artical_id, $content) {
    //这个try cache是为了防止异常提交的
    try {
        $uid = $this->get_user_id_by_username($username);
        $sql = "insert into `recall` (uid,aid,content) values (?,?,?)";
        $exc = $this->create_pdo_connect()->prepare($sql);
        $exc->bindParam(1, $uid);
        $exc->bindParam(2, $artical_id);
        $exc->bindParam(3, $content);
        $exc->execute();
        $exc->fetchAll();
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

/**
 * 获取留言
 * @param $page页码
 */
public
function get_artical($page) {
    $sql = "select username,content from `artical` limit 10,?";
    $exc = $this->create_pdo_connect()->prepare($sql);
    $exc->bindParam(1, $page);
    $exc->execute();
    $data = $exc->fetchAll();
    return $data;
}

/**
 * 获取留言页面总数
 */
public
function get_artical_num() {
    $sql = "select (count(select id from `artical`))/2";
//    $data = $this->create_pdo_connect()->query($sql)->fetchAll();
}

/**
 * 获取当前留言全部回复
 * @param $aid
 */
public
function get_recall_by_aid($aid) {
    $sql = "select fname,tname,content from `recall` where aid=?";
    $exc = $this->create_pdo_connect()->prepare($sql);
    $exc->bindParam(1, $aid);
    $exc->execute();
    $data = $exc->fetchAll();
}

}
//数据库模型
//表:recall
//字段：id:回复内容id
//fname:来源用户id
//aid:留言id
//tname：留言对象用户id  类似from to关系，form tom to jams表示tom回复jams的消息
//content 回复内容
//表:artical
//字段：id 留言id
//uid 留言用户
//content：留言内容
//表：user
//字段 id 用户id
//username 用户名
//password 密码 密码经过sha1与md5通过password与用户名链接组合加密