<?php

declare(strict_types=1);

namespace Topphp\Test;

use hwcvod\vod\client\VodClient;
use hwcvod\vod\client\VodConfig;
use hwcvod\vod\model\QueryAssetDetailReq;
use hwcvod\vod\model\QueryAssetListReq;
use PHPUnit\Framework\TestCase;
use Topphp\TopphpHuawei\Moderation\ModerationText;

class ExampleTest extends TestCase
{
    /**
     * Test that true does in fact equal true
     */
    public function testTrueIsTrue()
    {
        $result = ModerationText::text('haha 曹尼玛');
        var_dump($result);
    }

    public function testVodList()
    {
        $vodConfig = new VodConfig();
        $vodConfig->setAk('F8JS8OUWERGVHD0QVCOI');
        $vodConfig->setSk('N4D0uDkpFpdb8jUZt8szPOkoULds0YZFgaVg4K5n');
        $vodConfig->setProjectId('091b4a2d73000fec2f27c00a82ae5fa7');
        $vodConfig->setVodHost('vod.cn-north-4.myhuaweicloud.com');
        $vodClient = new VodClient($vodConfig);

        $req = new QueryAssetListReq();
        $rsp = $vodClient->queryAssetList($req);
        echo $rsp->getBody();
    }

    public function testVodDetail()
    {
        $vodConfig = new VodConfig();
        $vodConfig->setAk('F8JS8OUWERGVHD0QVCOI');
        $vodConfig->setSk('N4D0uDkpFpdb8jUZt8szPOkoULds0YZFgaVg4K5n');
        $vodConfig->setProjectId('091b4a2d73000fec2f27c00a82ae5fa7');
        $vodConfig->setVodHost('vod.cn-north-4.myhuaweicloud.com');
        $vodClient = new VodClient($vodConfig);

        $req = new QueryAssetDetailReq();
        $req->setAssetId('1d9e9f555b07f053d2588d0ccd0580bb');
        $req->setCategories(array('base_info', 'review_info'));

        $rsp = $vodClient->queryAssetDetail($req);
        echo $rsp->getBody();
    }
}
