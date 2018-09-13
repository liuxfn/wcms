<?php
/**
 * Created by PhpStorm.
 * User: lxf
 * Date: 12/19 0019
 * Time: 20:57
 */

//初始化数据库访问工具类
require "DBUtil.php";
$dbUtil = new DBUtil();

require "commonUtil.php";
$util = new commonUtil();


function login($postStr)
{
    $post = json_decode($postStr,true);
    $user_id = $post['user_id'];
    $password = $post['password'];
    if(!isset($user_id)||!isset($password)||empty($user_id)||empty($password))
    {
        return "{\"code\":\"401\",\"message\":\"用户名或密码为空，请重试\"}";
    }

	$reslult = $GLOBALS['dbUtil']->querySql("select user_id,user_name,user_sf,ssxm from user where user_id = ".$user_id." and password = '".$password."'");
    $p = json_decode($reslult,true);
    if($p['total']>0)
    {
        $uuid = $GLOBALS['util']->guid();
        session_start();
        $_SESSION['authorization']=$uuid;
        $_SESSION['user_name']=$p['rows'][0]['user_name'];
        $_SESSION['user_id']=$p['rows'][0]['user_id'];
        $_SESSION['user_sf']=$p['rows'][0]['user_sf'];
        $_SESSION['user_ssxm']=$p['rows'][0]['ssxm'];
        $return_str = "{\"code\":\"200\",\"message\":\"登陆成功\",\"user_id\":\"".$p['rows'][0]['user_id']."\",\"user_name\":\"".$p['rows'][0]['user_name']."\",\"user_sf\":\"".$p['rows'][0]['user_sf']."\",\"user_ssxm\":\"".$p['rows'][0]['ssxm']."\",\"authorization\":\"".$uuid."\"}";
        return $return_str;
    }
    return "{\"code\":\"401\",\"message\":\"用户名或密码错误\"}";
}

function check_session(){
    session_start();
    if(!isset($_SESSION["authorization"]) || $_SESSION["authorization"] == '' || $_SESSION["authorization"] != $_COOKIE["authorization"]){
        return "{\"code\":\"401\",\"message\":\"请重新登陆\"}";
    }else{
        return "{\"code\":\"200\",\"message\":\"用户已登陆\"}";
    }
}

function logout(){
    session_start();
    session_destroy();
    return "{\"code\":\"200\",\"message\":\"登出成功\"}";
}

function accountHandler()
{
    $method = !isset($_GET['method'])?'':$_GET['method'];
    if(empty($method))
    {
        return "{\"code\":\"401\",\"message\":\"参数名为空\"}";
    }
    else if("login" == $method)
    {
        //$postStr=$GLOBALS["HTTP_RAW_POST_DATA"];
        $postStr = file_get_contents('php://input');
        if(empty($postStr))
        {
            return "{\"code\":\"401\",\"message\":\"用户名或密码为空，请重试\"}";
        }
        return login($postStr);

    }
    else if("logout" == $method)
    {
        return logout();
    }
    else if("check_session" == $method)
    {
        return check_session();
    }
}

echo accountHandler();

?>