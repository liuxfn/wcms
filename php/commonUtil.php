<?php

/**
 * Created by PhpStorm.
 * User: lxf
 * Date: 2018年9月6日
 * Time: 19点58分
 * Desc: 生成UUID
 */
class commonUtil
{
    /*UUID是指在一台机器上生成的数字，它保证对在同一时空中的所有机器都是唯一的。通常平台 会提供生成UUID的API。
    UUID按照开放软件基金会(OSF)制定的标准计算，用到了以太网卡地址、纳秒级时间、芯片ID码和许多可能的数字。由以
    下几部分的组合：当前日期和时间(UUID的第一个部分与时间有关，如果你在生成一个UUID之后，过几秒又生成一个UUID，
    则第一个部分不同，其余相 同)，时钟序列，全局唯一的IEEE机器识别号（如果有网卡，从网卡获得，没有网卡以其他方式
    获得），UUID的唯一缺陷在于生成的结果串会比较长。关于 UUID这个标准使用最普遍的是微软的GUID(Globals Unique
    Identifiers)。在ColdFusion中可以用CreateUUID()函数很简单的生成UUID，其格式为：xxxxxxxx-xxxx-xxxx-
    xxxxxxxxxxxxxxxx(8-4-4-16)，其中每个 x 是 0-9 或 a-f 范围内的一个十六进制的数字。而标准的UUID格式为：
    xxxxxxxx-xxxx-xxxx-xxxxxx-xxxxxxxxxx (8-4-4-4-12)*/

    /**
     * 创建UUID
     * @return string uuid
     */
    public function guid()
    {
        if (function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            mt_srand((double)microtime() * 10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            /*$hyphen = chr(45);// "-"
            $uuid = chr(123)// "{"
                . substr($charid, 0, 8) . $hyphen
                . substr($charid, 8, 4) . $hyphen
                . substr($charid, 12, 4) . $hyphen
                . substr($charid, 16, 4) . $hyphen
                . substr($charid, 20, 12)
                . chr(125);// "}"
            return $uuid;*/
            return $charid;
        }
    }

}

//$util = new commonUtil();
//echo $util->guid();

?>