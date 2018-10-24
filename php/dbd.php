<?php
/**
 * wechat php test
 *文件的编码格式为UTF-8 不能包含bom文件头
 */
require "DBUtil.php";
$dbUtil = new DBUtil();

function dbdHandler()
{
    //file_put_contents("log.txt",$GLOBALS['dbUtil']->msectime(),FILE_APPEND);
    session_start();
    if (!isset($_SESSION["authorization"]) || $_SESSION["authorization"] == '' || $_SESSION["authorization"] != $_COOKIE["authorization"]) {
        return "{\"code\":\"402\",\"message\":\"请重新登陆\"}";
    }

    $method = !isset($_GET['oper']) ? $_POST['oper'] : $_GET['oper'];
    if (empty($method)) {
        return "{\"code\":\"401\",\"message\":\"方法名为空\"}";
    }

    if ("query" == $method) {
        return querySpxx();
    } else if ("collect" == $method) {
        return collectGoods();
    } else if ("refresh" == $method) {
        $count = refreshSpxx(1);
        return "{\"code\":\"200\",\"message\":\"下载成功:共计" . $count . "条商品信息\"}";
    }
    //return updateUser($sql);
}

function querySpxx()
{
    $pageSize = $_GET['rows'];
    $pageNo = $_GET['page'];
    $queryCondition = buildQueryCondition();
    $reslult = $GLOBALS['dbUtil']->querySql("
        select
        id,
        userNo,
        spcs,
        productName,
        date_format(startTime,'%Y-%m-%d %H:%i:%s') startTime,
        date_format(endTime,'%Y-%m-%d %H:%i:%s')  endTime,
        cappedPrice,
        category1,
        category1Name,
        category2,
        category2Name,
        primaryPic,
        currentPrice,
        status,
        sc
        from dbd_spxx where 1=1 and endTime > now() and " . $queryCondition . " order by endTime limit " . (($pageNo - 1) * $pageSize) . "," . $pageSize);

    $rtnArray = json_decode($reslult, true);

    $reslult = $GLOBALS['dbUtil']->querySql("select count(*) records,CEILING(count(*)/" . $pageSize . ") total from dbd_spxx where endTime > now() and " . $queryCondition);
    $reslult = json_decode($reslult, true);
    $rtnArray['total'] = $reslult['rows'][0]['total'];
    $rtnArray['records'] = $reslult['rows'][0]['records'];
    $rtnArray['page'] = $pageNo;

    $rtnStr = json_encode($rtnArray, true);
    return $rtnStr;
}

function buildQueryCondition()
{
    $queryCondition = "1=1";
    if ((!isset($_GET['type']) && (!isset($_GET['filters']) || empty($_GET['filters']))) || (isset($_GET['type']) && (!isset($_SESSION["user_filters"]) || empty($_SESSION["user_filters"])))) {
        unset($_SESSION["user_filters"]);
        return $queryCondition;
    }

    $filters = null;
    if (isset($_GET['filters'])) {

        $filters = json_decode($_GET['filters'], true);
        $_SESSION["user_filters"] = $_GET['filters'];
    } else {
        $filters = json_decode($_SESSION["user_filters"], true);
    }

    $groupOp = $filters['groupOp'];
    $rules = $filters['rules'];
    $operator = "";
    for ($x = 0; $x < count($rules); $x++) {
        $queryCondition .= " " . $groupOp . " ";
        $operator = $rules[$x]['op'] == "in" ? "in" : ($rules[$x]['op'] == "ni" ? "not in" : ($rules[$x]['op'] == "eq" ? "=" : ($rules[$x]['op'] == "cn" ? "like" : ($rules[$x]['op'] == "lt" ? "<" : ($rules[$x]['op'] == "le" ? "<=" : ($rules[$x]['op'] == "gt" ? ">" : ">="))))));
        if ($operator == 'in' || $operator == 'ni') {
            $queryCondition .= $rules[$x]['field'] . " " . $operator . " (" . addslashes($rules[$x]['data']) . ") ";
        } else if ($operator == 'like') {
            $queryCondition .= $rules[$x]['field'] . " " . $operator . " '%" . addslashes($rules[$x]['data']) . "%' ";
        } else {
            $queryCondition .= $rules[$x]['field'] . " " . $operator . " '" . addslashes($rules[$x]['data']) . "' ";
        }
    }
    return $queryCondition;
}

/**
 *相应用户输入
 **/
function refreshSpxx($pageNo)
{
    $millisecond = ceil(microtime(true) * 1000);
    $url = "https://used-api.jd.com/auction/list?time=" . $millisecond . "&status=1&pageNo=" . $pageNo . "&pageSize=100&isloading=false&isall=false&el=%23kaiPai+.camera&temp=%23camera&select=right&isLoading=true";
    $output = curlGet($url);
    //$startIndex = strpos($output,'{');
    //$endIndex = strrpos($output,'}');
    //$strLength = strlen($output);
    //$output = substr($output,$startIndex,($endIndex-$strLength + 1));
    $content = json_decode($output, true);
    if ($content['code'] == 200) {
        if ($pageNo == 1) {
            $sql = "truncate table dbd_spxx";
            $reslult = $GLOBALS['dbUtil']->updateSql($sql);
        }
        $splb = $content['data']['auctionInfos'];
        $count = count($splb);
        $insert_sql = "insert into dbd_spxx values";
        $insert_val = "";
        foreach ($splb as $item) {
            if (!empty($insert_val)) {
                $insert_val .= ",";
            }

            $insert_val .= "(";
            foreach ($item as $x => $x_value) {
                if($x == 'quality'){
                    break;
                }

                if ($x == 'startTime' || $x == 'endTime') {
                    $insert_val .= "FROM_UNIXTIME(" . $x_value . "/1000,'%Y-%m-%d %H:%i:%s')";
                } else if ($x == 'productName') {
                    $insert_val .= ("'" . (substr($x_value, 3, (strpos($x_value, '】') - strlen($x_value)))) . "'");
                    $insert_val .= ",";
                    $insert_val .= ("'" . (substr($x_value, strpos($x_value, '】') + 3)) . "'");
                } else {
                    $insert_val .= ("'" . $x_value . "'");
                }
                if ($x != 'status') {
                    $insert_val .= ',';
                }
            }
            $insert_val .= ",'N')";

        }
        $reslult = $GLOBALS['dbUtil']->updateSql($insert_sql . " " . $insert_val);
        if ($content['data']['hasNext'] == 'True') {
            $count = $count + refreshSpxx($pageNo + 1);
        }
        return $count;
    }

}

function getCategory1(){
    $sql = "select distinct category1,category1name from dbd_spxx";
    $reslult = $GLOBALS['dbUtil']->querySql($sql);
    $rtnArray = json_decode($reslult, true);
}

function refreshPrice()
{

}

function collectGoods()
{
    $id = $_GET['id'];
    $sczz = $_GET['sczz'] == 'Y' ? 'N' : 'Y';
    $message = $_GET['sczz'] == 'Y' ? '取消收藏成功，将取消提醒！' : '添加收藏成功，将在截至时间前3分钟发送提醒！';
    $sql = "update dbd_spxx set sc = '" . $sczz . "' where id = " . $id;
    $reslult = $GLOBALS['dbUtil']->updateSql($sql);
    if ($reslult == -1) {
        return "{\"code\":\"400\",\"message\":\"\"}";
    } else {
        return "{\"code\":\"200\",\"message\":\".$message.\"}";
    }
}

/**
 *get方式获取URL内容
 **/
function curlGet($url, $timeout = 30)
{
    $ssl = substr($url, 0, 8) == "https://" ? TRUE : FALSE;
    $ch = curl_init();
    $opt = array(
        CURLOPT_URL => $url,
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_TIMEOUT => $timeout,
    );
    if ($ssl) {
        $opt[CURLOPT_SSL_VERIFYHOST] = 2;
        $opt[CURLOPT_SSL_VERIFYPEER] = FALSE;
    }
    curl_setopt_array($ch, $opt);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

echo dbdHandler();

?>