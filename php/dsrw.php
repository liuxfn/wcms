<?php
/**
 * wechat php test
 *文件的编码格式为UTF-8 不能包含bom文件头
 */
require "DBUtil.php";
$dbUtil = new DBUtil();

function dsrwHandler()
{

    $method = !isset($_GET['oper'])?$_POST['oper']:$_GET['oper'];
    if(empty($method))
    {
        return "{\"code\":\"401\",\"message\":\"方法名为空\"}";
    }

    if("sendMessage" == $method)
    {
        return sendMessage();
    }
}

function sendMessage()
{
    $sql = "select s.*,timestampdiff(minute,now(),endTime) sysj from dbd_spxx s where timestampdiff(minute,now(),endTime) <= 5
      and timestampdiff(minute,now(),endTime) >0 and sc = 'Y'";
    $reslult = $GLOBALS['dbUtil']->querySql($sql);
    $rtnArray = json_decode($reslult,true);
    $webhook = "https://oapi.dingtalk.com/robot/send?access_token=09a01c45b757b76888a904cb2ff7c7aff9a47e47fb9a8502230e1c9554a5fb5c";
    foreach ($rtnArray['rows'] as $value){
        $link = ['text'=>"您收藏的商品将在5分钟后结束拍卖，请及时购买！",
            'title'=> "【".$value['productName']."】",
            'picUrl'=> "https://img10.360buyimg.com/n1/s250x250_".$value['primaryPic'],
            'messageUrl'=> "https://paipai.m.jd.com/m/raise_auction.html?auctionId=".$value['id']];
        $message=['msgtype'=> "link", 'link'=>$link];
        $data_string = json_encode($message);
        $result = request_by_curl($webhook, $data_string);
    }
    return $result;
}

function request_by_curl($remote_server, $post_string) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $remote_server);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array ('Content-Type: application/json;charset=utf-8'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // 线下环境不用开启curl证书验证, 未调通情况可尝试添加该代码
    curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}

echo dsrwHandler();

?>