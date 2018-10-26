<?php
/**
 * wechat php test
 *文件的编码格式为UTF-8 不能包含bom文件头
 */
require "DBUtil.php";
$dbUtil = new DBUtil();

function dbdHandler()
{
    refreshSpxx(1);
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