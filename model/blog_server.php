<?php
/**
 * Created by PhpStorm.
 * User: wangling
 * Date: 2018/5/16
 * Time: 22:12
 */
session_start();//启动新会话或者重用现有会话
include "database.php";
class blog_server {
    private $config=array();
    private $model;

    /**
     * blog_server构造函数 加载配置
     * @param $config
     */
    public function __construct($config) {
        $this->config=$config;
        $this->model=new database($this->config);
    }

    /**
     * URL解析方法
     * 解析方法为假设路径为index.php/index/home
     * 获取第一组解析路径/index/home
     * 解析出index模块home方法
     * 注：次出只有一层解析即解析index、login、xss、logout即发生页面回显
     */
    public function url_decode() {
        if(isset($_GET)){
            $path=array_keys($_GET);
//            var_dump($path);
            if(empty($path)){
                echo $this->index();
                exit();
            }
            $url=preg_split("/\//",$path[0]);
            array_shift($url);
            /**
             * URL解析
             */
//            echo $url[0];
            switch ($url[0]){
                case "index":
                    echo $this->index();
                    break;
                case "login":
                    echo $this->login();
                    break;
                case "message":
                    echo $this->message();
                    break;
                case "logout":
                    echo $this->logout();
                    break;
                case "xss":
                    echo $this->xss();
                    break;
                case "regist":
                    echo $this->regist();
                    break;
                default:
                    echo $this->index();
                    break;
            }
        }
    }

    /**
     * 注册页面
     */
    private function regist(){
        $HTML=<<<HTML
<form action="index.php?/regist" method="post">
用户名：<input name="username" />
密码：<input onmousemove="this.type=text" type="text" name="password"/>
<input type="submit">
</form>
HTML;
        if(isset($_SESSION) and isset($_SESSION['username'])){
            header("Location:index.php/index");
        }else{
            if(isset($_POST) and isset($_POST['password']) and isset($_POST['username'])){
                $req=$this->model->regist_user($_POST['username'],$_POST['password']);
                switch ($req){
                    case 1:
                        return "注册成功";
                        break;
                    case -1:
                        return "数据库错误";
                        break;
                    case 0:
                        return "用户名已存在";
                        break;
                }
            }else{
                return $HTML;
            }
        }
        return ;
    }
    private function index(){

        $page=<<<HTML
<!DOCTYPE html>
<html>
<head>
<title>留言板</title>
</head>
<body>
%s
<table>
<tr>
<td>用户</td>
<td>留言</td>
<td>时间</td>
%s
</tr>
</table>
</body>
</html>
HTML;
            $content="";
                var_dump($this->model->get_artical(1));
        if(isset($_SESSION) and isset($_SESSION['username'])){
            return sprintf($page,"欢迎".$_SESSION['username']."的到来<a href='index.php?/logout'>注销</a>",$content);
        }else{

            return sprintf($page,"您还未登录请<a href='index.php?/login'>登录</a>",$content);
        }
    }

    /**
     * 登录页面
     * @return null|string
     */
    private function login(){
        if(isset($_POST['username']) and isset($_POST['password'])){
            $username=$_POST['username'];
            $password=$_POST['password'];
            $uinfo=$this->model->check_userinfo($username,$password);
            if(count($uinfo)!=0){
            $_SESSION['username']= $uinfo[0][0];
            header("Location:index.php");
            }else return json_encode(array("message","账号或密码错误"));
        }
        $page=<<<HTML
<!DOCTYPE html>
<html>
<head>
<title>留言板</title>
</head>
<body>
<form method="post" action="index.php?/login">
<table>
<tr>
<td>用户名:</td>
<td><input name="username" type="text"></td>
</tr>
<tr>
<td>密码:</td>
<td><input name="password" type="test"></td>
</tr>
<tr>
<td colspan="2"><input style="width:100%;" type="submit"></td>
</tr>
</table>
</form>
</body>
</html>
HTML;

        return $page;
    }

    /**
     * 留言板页面
     * @return string
     */
    private function message(){
        $page=<<<HTML
<!DOCTYPE html>
<html>
<head>
<title>留言板</title>
</head>
<body>
%s
</body>
</html>
HTML;
        $board=<<<HTML
<form action="index.php?/message" method="post">
<table>
<tr>
<td>
标题:<input type="text" name="title"/>
</td>
</tr>
<tr>
<td>
<textarea name="content">
此处填写留言
</textarea>
</td>
</tr>
<tr>
<td>
<input style="width: 100%;" type="submit" value="提交留言">

</td>
</tr>
</table>
</form> 
HTML;

        if(isset($_SESSION) and isset($_SESSION['username'])){
            if (isset($_POST['content']) and isset($_POST['title'])){
                $this->model->post_message($_SESSION['username'],$_POST['content']);
            }
            return sprintf($page,"欢迎".$_SESSION['username']."的到来<a href='index.php?/logout'>注销</a>").$board;
        }else{
            return sprintf($page,"您还未登录请<a href='index.php?/login'>登录</a>");
        }
        return $page;

    }

    /**
     * 注销页面：删除Session后重定向到主页
     */
    private function logout(){
        unset($_SESSION);
        session_destroy();
        header("Location:index.php?/index");
    }

    /**
     * 反射型XSS路径
     */
    public function xss(){
        if(isset($_GET['xss']))echo $_GET['xss'];
    }

    /**
     * 主系统启动函数
     */
    public function run() {
        $this->url_decode();//启动路径解析
    }
}