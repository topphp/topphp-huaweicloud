<?php

declare(strict_types=1);

namespace Topphp\Test;

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
}
