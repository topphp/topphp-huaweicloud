<?php
/**
 * 获取临时授权请求示例
 */

require dirname(__DIR__).'/vendor/autoload.php';
require dirname(__DIR__).'/init.php';

use hwcvod\vod\client\VodConfig;
use hwcvod\vod\client\VodClient;

$vodConfig = new VodConfig();
$vodConfig->setAk(AK);
$vodConfig->setSk(SK);
$vodConfig->setProjectId(PID);
$vodClient = new VodClient($vodConfig);

$name = "hellocdj";
$password = "cuidongjian#0810";
$domainName = "hellocdj";
$duration = 3600;

$rsp = $vodClient->requestTemporaryCredential($name, $password, $domainName, $duration);
echo $rsp;