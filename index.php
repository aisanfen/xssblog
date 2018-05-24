<?php
/**
 * Created by PhpStorm.
 * User: wangling
 * Date: 2018/5/16
 * Time: 21:55
 */
header("Content-type: text/html; charset=utf-8");
include "./model/blog_server.php";
$config=array(
    "database"=>"mysql:host=localhost;dbname=xssblog",
    "init"=>"mysql:host=localhost;",
    "db_name"=>"xssblog",
    "username"=>"root",
    "password"=>"root",
    "address"=>"127.0.0.1",
    "port"=>"3306"
    );
//var_dump($config);
$server=new blog_server($config);
$server->run();
