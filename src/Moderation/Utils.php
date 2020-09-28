<?php

namespace Topphp\TopphpHuawei\Moderation;

class Utils
{
    public static $regionName = "cn-north-1";

    public static $endpoint = [
        "moderation" => [
            "cn-north-1"     => "moderation.cn-north-1.myhuaweicloud.com",
            "cn-north-4"     => "moderation.cn-north-4.myhuaweicloud.com",
            "ap-southeast-1" => "moderation.ap-southeast-1.myhuaweicloud.com",
            "cn-east-3"      => "moderation.cn-east-3.myhuaweicloud.com",
            "ap-southeast-3" => "moderation.ap-southeast-3.myhuaweicloud.com",
        ]
    ];

    /**
     * 文件转化为base64
     * @param mixed $file 文件路径
     * @return string
     */
    public function file_to_base64($file)
    {
        $base64data = "";
        if (file_exists($file)) {
            $base64data = base64_encode(file_get_contents($file));
        }
        return $base64data;
    }

    /**
     * 将base64位的字符串转成文件
     * @param $filePath
     * @param $base64str
     */
    public function base64_to_file($filePath, $base64str)
    {
        if ($base64str != null) {
            // 将base64的字符串写入文件路径
            $flag = file_put_contents($filePath, base64_decode($base64str));
            if (!$flag) {
                echo "base64str change to file failed";
            } else {
                echo $filePath;
                echo "\n";
            }
        } else {
            echo "base64str is null!";
        }
    }

    /**
     * 初始化region信息
     * @param $region
     */
    public static function init_region($region)
    {
        self::$regionName = $region;
    }

    /**
     * 获取服务的域名
     * @param $type
     * @return mixed
     */
    public static function get_endpoint($type)
    {
        return self::$endpoint[$type][self::$regionName];
    }

    /**
     * 判断请求是否成功响应
     * @param $status
     * @return bool
     */
    public static function status_success($status)
    {
        if ($status >= 200 && $status < 300) {
            return true;
        } else {
            return false;
        }
    }
}
