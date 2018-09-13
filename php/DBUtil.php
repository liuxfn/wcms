<?php

/**
 * Created by PhpStorm.
 * User: lxf
 * Date: 2018-9-6
 * Time: 19:59:10
 * Desc: 数据库工具类
 */
class DBUtil
{
    private function createConn()
    {
        $jsonStr = file_get_contents('mysql.json');
        $json = json_decode($jsonStr,true);
        $con = mysqli_connect($json['servername'],$json['username'],$json['password'],$json['defaultdb'],$json['port']);
        // 检查连接
        if (!$con)
        {
            die("连接错误: " . mysqli_connect_error());
        }
        mysqli_set_charset($con,"utf8");
        mysqli_autocommit($con,FALSE);
        return $con;
    }

    public function querySql($sql)
    {
        //file_put_contents("log.txt",'---------------------------------------------------',FILE_APPEND);
        //file_put_contents("log.txt",$sql,FILE_APPEND);
        if(!isset($sql)||empty($sql))
        {
            return "";
        }
        $con = $this->createConn();
        $result = mysqli_query($con, $sql);
        //file_put_contents('log.txt',$result,FILE_APPEND);
        $count = mysqli_num_rows($result);
        //$t = mysqli_fetch_array($result,MYSQLI_BOTH);
        //$p = mysqli_fetch_all($result,MYSQLI_BOTH);
        $rtn="{\"total\":".$count.",\"rows\":[";
        //foreach ($p as $row)
        while($row = mysqli_fetch_array($result,MYSQLI_BOTH))
        {
            $rtn .= json_encode($row);
            if($count > 1)
            {
                $rtn .=",";
            }
            $count--;
        }
        $rtn.="]}";
        mysqli_close($con);
        return $rtn;
    }

    public function updateSql($sql)
    {
        //file_put_contents("log.txt",'---------------------------------------------------',FILE_APPEND);
        //file_put_contents("log.txt",$sql,FILE_APPEND);
        if(!isset($sql)||empty($sql))
        {
            return "";
        }
        $con = $this->createConn();
        $result = mysqli_query($con, $sql);
        $code=0;
        if(!$result)
        {
            $code = mysqli_errno($con);
            mysqli_rollback($con);
        }
        else
        {
            $code = mysqli_affected_rows($con);
            mysqli_commit($con);
        }
        mysqli_close($con);
        return $code;
    }
}

//$dbUtil = new DBUtil();
//echo $dbUtil->querySql("select status,count(status)count from message where userid = '3011' group by status");

?>