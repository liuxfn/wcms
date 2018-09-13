<?php
//require "DBUtil.php";
//$dbUtil = new DBUtil();

session_start();
//$reslult = $GLOBALS['dbUtil']->querySql('select * from person where yxbz = "Y"');
//$reslult = json_decode($reslult,true);
//$ssxm_con =$_SESSION["user_sf"]==1?"1=1":"ssxm='".$_SESSION["user_ssxm"]."'";
//echo $ssxm_con;

echo file_put_contents("test.txt","Hello World. Testing!");

?>