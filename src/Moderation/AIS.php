<?php
/**
 * 内容审核服务请求url常量及配置信息
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/16
 * Time: 10:44
 */

namespace Topphp\TopphpHuawei\Moderation;

class AIS
{
    // token请求域名
    const IAM_ENPOINT = 'iam.myhuaweicloud.com';
    // token请求uri
    const AIS_TOKEN = '/v3/auth/tokens';
    // 图像清晰度的检测服务uri
    const IMAGE_CLARITY_DETECT = '/v1.0/moderation/image/clarity-detect';
    // 扭曲校正服务的uri
    const DISTORTION_CORRECT = '/v1.0/moderation/image/distortion-correct';
    // 图片内容审核服务的uri
    const IMAGE_CONTENT_DETECT = '/v1.0/moderation/image';
    // 文本内容检测服务的uri
    const MODERATION_TEXT = '/v1.0/moderation/text';
    // 视频审核服务的uri
    const MODERATION_VIDEO = '/v1.0/moderation/video';
    // 图像内容审核批量异步uri
    const IMAGE_CONTENT_BATCH_JOBS = '/v1.0/moderation/image/batch/jobs';
    // 图像内容审核批量异步结果uri
    const IMAGE_CONTENT_BATCH_RESULT = '/v1.0/moderation/image/batch';
    // 图片内容审核服务批量的uri
    const IMAGE_CONTENT_BATCH = '/v1.0/moderation/image/batch';
    //内容审核服务类别
    const MODERATION = 'moderation';
    // 异步查询任务失败最大重试次数
    const RETRY_MAX_TIMES = 3;
}
