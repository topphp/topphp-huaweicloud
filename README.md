# topphp-huawei

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]


# 华为违禁词过滤组件
目前仅支持文本过滤

#安装
``` bash
$ composer require topphp/topphp-huawei
```

## Usage

```php
<?php
use Topphp\TopphpHuawei\Moderation\ModerationText;
function ModerationText()
{
    $result = ModerationText::text('haha 曹尼玛','key','secret');
    var_dump($result);
}
```

#### 获取vod点播资源列表和详情
```php
<?php
  public function testVodList()
    {
        $vodConfig = new VodConfig();
        $vodConfig->setAk('');
        $vodConfig->setSk('');
        $vodConfig->setProjectId('');
        $vodConfig->setVodHost('vod.cn-north-4.myhuaweicloud.com');
        $vodClient = new VodClient($vodConfig);

        $req = new QueryAssetListReq();
        $rsp = $vodClient->queryAssetList($req);
        echo $rsp->getBody();
    }

    public function testVodDetail()
    {
        $vodConfig = new VodConfig();
        $vodConfig->setAk('');
        $vodConfig->setSk('');
        $vodConfig->setProjectId('');
        $vodConfig->setVodHost('vod.cn-north-4.myhuaweicloud.com');
        $vodClient = new VodClient($vodConfig);

        $req = new QueryAssetDetailReq();
        $req->setAssetId('1d9e9f555b07f053d2588d0ccd0580bb');
        $req->setCategories(array('base_info', 'review_info'));

        $rsp = $vodClient->queryAssetDetail($req);
        echo $rsp->getBody();
    }
```
## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email sleep@kaituocn.com instead of using the issue tracker.

## Credits

- [topphp][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/topphp/topphp-huawei.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/topphp/topphp-huawei/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/topphp/topphp-huawei.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/topphp/topphp-huawei.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/topphp/topphp-huawei.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/topphp/topphp-huawei
[link-travis]: https://travis-ci.org/topphp/topphp-huawei
[link-scrutinizer]: https://scrutinizer-ci.com/g/topphp/topphp-huawei/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/topphp/topphp-huawei
[link-downloads]: https://packagist.org/packages/topphp/topphp-huawei
[link-author]: https://github.com/topphp
[link-contributors]: ../../contributors
