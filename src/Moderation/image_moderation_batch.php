<?php

namespace Topphp\TopphpHuawei\Moderation;

/**
 * token 方式
 */
function image_content_batch($token, $urls, $categories, $threshold)
{

    $endPoint = get_endpoint(MODERATION);

    // 构建请求信息
    $_url = "https://" . $endPoint . IMAGE_CONTENT_BATCH;

    $data = array(
        "urls"       => $urls,
        // 图片对象的obs数组
        "threshold"  => $threshold,
        // 非必选 结果过滤门限
        "categories" => $categories,
        // 非必选 检测场景 array politics：是否涉及政治人物的检测。terrorism：是否包含暴恐元素的检测。porn：是否包含涉黄内容元素的检测
    );

    $curl    = curl_init();
    $headers = array(
        "Content-Type:application/json",
        "X-Auth-Token:" . $token
    );

    // 请求信息封装
    curl_setopt($curl, CURLOPT_URL, $_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_NOBODY, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);

    // 执行请求信息
    $response = curl_exec($curl);
    $status   = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if ($status == 0) {
        echo curl_error($curl);
    } else {
        // 验证服务调用返回的状态是否成功，如果为2xx, 为成功, 否则失败。
        if (status_success($status)) {
            return $response;
        } else {
            echo "Http status is: " . $status . "\n";
            echo $response;
        }
    }
    curl_close($curl);
}

/**
 * ak,sk 方式
 */
function image_content_batch_aksk($_ak, $_sk, $urls, $categories, $threshold)
{
    // 构建ak，sk对象
    $signer            = new Signer();
    $signer->AppKey    = $_ak;             // 构建ak
    $signer->AppSecret = $_sk;             // 构建sk

    $endPoint = get_endpoint(MODERATION);

    // 构建请求对象
    $req         = new Request();
    $req->method = "POST";
    $req->scheme = "https";
    $req->host   = $endPoint;
    $req->uri    = IMAGE_CONTENT_BATCH;

    $data = array(
        "urls"       => $urls,
        // 图片的obs数组
        "threshold"  => $threshold,
        // 非必选 结果过滤门限 过滤门限0-1 之间，检测结果与算法有关，与其他无关
        "categories" => $categories,
        // 非必选 检测场景 array politics：是否涉及政治人物的检测。terrorism：是否包含暴恐元素的检测。porn：是否包含涉黄内容元素的检测

    );

    $headers = array(
        "Content-Type" => "application/json",
    );

    $req->headers = $headers;
    $req->body    = json_encode($data);

    // 获取ak，sk方式的请求对象，执行请求
    $curl = $signer->Sign($req);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($curl);
    $status   = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if ($status == 0) {
        echo curl_error($curl);
    } else {
        // 验证服务调用返回的状态是否成功，如果为2xx, 为成功, 否则失败。
        if (status_success($status)) {
            return $response;
        } else {
            echo "Http status is: " . $status . "\n";
            echo $response;
        }
    }
    curl_close($curl);
}
