<?php
/**
 * Created by PhpStorm.
 * User: lxf
 * Desc:消息处理
 * Date: 12/21 0021
 * Time: 10:49
 */

//初始化数据库访问工具类
require "DBUtil.php";
$dbUtil = new DBUtil();

//用户消息查询
function queryUser()
{
    $pageSize=$_GET['rows'];
    $pageNo=$_GET['page'];
    //$userId = $_SESSION['user_sf']; todo 身份校验
    $queryCondition = buildQueryCondition();
    $reslult = $GLOBALS['dbUtil']->querySql("select user_id,user_name,
    case user_sf when 1 then '是' else '否' end user_sf,ssxm,date_format(lrrq,'%Y-%m-%d') lrrq,
    bz from user where yxbz = 'Y' and ".$queryCondition." limit ".(($pageNo-1)*$pageSize).",".$pageSize);

    $rtnArray = json_decode($reslult,true);

    $reslult = $GLOBALS['dbUtil']->querySql("select count(*) records,CEILING(count(*)/".$pageSize.") total from user where yxbz = 'Y' and ".$queryCondition);
    $reslult = json_decode($reslult,true);
    $rtnArray['total'] = $reslult['rows'][0]['total'];
    $rtnArray['records'] = $reslult['rows'][0]['records'];
    $rtnArray['page'] = $pageNo;

    $rtnStr= json_encode($rtnArray,true);
    return $rtnStr;
}

function buildQueryCondition()
{
    $queryCondition = "1=1";
    if((!isset($_GET['type']) && (!isset($_GET['filters']) || empty($_GET['filters']))) || (isset($_GET['type']) && (!isset($_SESSION["user_filters"]) || empty($_SESSION["user_filters"]))))
    {
        unset($_SESSION["user_filters"]);
        return $queryCondition;
    }

    $filters=null;
    if(isset($_GET['filters'])){

        $filters = json_decode($_GET['filters'],true);
        $_SESSION["user_filters"] = $_GET['filters'];
    }else{
        $filters = json_decode($_SESSION["user_filters"],true);
    }

    $groupOp = $filters['groupOp'];
    $rules = $filters['rules'];
    $operator = "";
    for ($x=0; $x<count($rules);$x++) {
        $queryCondition.=" ".$groupOp." ";
        $operator = $rules[$x]['op'] == "in" ? "in" : ($rules[$x]['op'] == "ni" ? "not in" : ($rules[$x]['op'] == "eq" ? "=" : ($rules[$x]['op'] == "cn" ? "like" : ($rules[$x]['op'] == "lt" ? "<" : ($rules[$x]['op'] == "le" ? "<=" : ($rules[$x]['op'] == "gt" ? ">" : ">="))))));
        if($operator == 'in' || $operator == 'ni')
        {
            $queryCondition.=$rules[$x]['field']." ".$operator." (".$rules[$x]['data'].") ";
        }else if($operator == 'like'){
            $queryCondition.=$rules[$x]['field']." ".$operator." '%".$rules[$x]['data']."%' ";
        }else{
            $queryCondition.=$rules[$x]['field']." ".$operator." '".$rules[$x]['data']."' ";
        }
    }
    return $queryCondition;
}

//用户消息更新
function updateUser($sql)
{
    $reslult = $GLOBALS['dbUtil']->updateSql($sql);
    if($reslult == -1)
    {
        return "{\"code\":\"400\",\"message\":\"\"}";
    }
    else
    {
        return "{\"code\":\"200\",\"message\":\"\"}";
    }
}

//信息助手
function userHandler()
{
    session_start();
    if(!isset($_SESSION["authorization"]) || $_SESSION["authorization"] == '' || $_SESSION["authorization"] != $_COOKIE["authorization"]){
        return "{\"code\":\"402\",\"message\":\"请重新登陆\"}";
    }

    $method = !isset($_GET['oper'])?$_POST['oper']:$_GET['oper'];
    if(empty($method))
    {
        return "{\"code\":\"401\",\"message\":\"方法名为空\"}";
    }

    if("query" == $method)
    {
        return queryUser();
    }else if("add" == $method)
    {
        $user_sf = $_POST['user_sf'] == '是' ? '1' : '0';
        $sql = "INSERT INTO user VALUES(NULL,'".$_POST['user_name']."','".$user_sf."','".$_POST['ssxm']."','".md5($_POST['password'])."',now(),null,'Y','".$_POST['bz']."')";
    }else if("edit" == $method){
        $password = empty($_POST['password'])?"":",PASSWORD='".md5($_POST['password'])."'";
        $sql = "UPDATE user SET USER_NAME = '".$_POST['user_name']."',SSXM='".$_POST['ssxm']."',XGRQ=now(),bz='".$_POST['bz']."'".$password." WHERE USER_ID = ".$_POST['user_id'];
    }else if("del" == $method){
        $sql = "UPDATE user SET YXBZ = 'N',XGRQ=now() WHERE USER_SF = 0 AND USER_ID in (".$_POST['id'].")";
    }else if("update_password" == $method){
        $sql = "select * from user where yxbz = 'Y' and user_id = ".$_SESSION['user_id']." and password = '".$_GET['old_password']."'";
        $reslult = $GLOBALS['dbUtil']->querySql($sql);
        $reslult = json_decode($reslult,true);
        if($reslult['total'] == 0){
            return "{\"code\":\"403\",\"message\":\"密码错误\"}";
        }else{
            $sql = "UPDATE user SET PASSWORD = '".$_GET['new_password']."',XGRQ=now() WHERE USER_ID = '".$_SESSION['user_id']."'";
        }

    }
    return updateUser($sql);
}

echo userHandler();

?>