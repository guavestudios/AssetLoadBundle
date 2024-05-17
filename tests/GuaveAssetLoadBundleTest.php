<?php

declare(strict_types=1);

namespace Guave\AssetLoadBundle\Tests;

use Guave\AssetLoadBundle\GuaveAssetLoadBundle;
use PHPUnit\Framework\TestCase;

class GuaveAssetLoadBundleTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $bundle = new GuaveAssetLoadBundle();

        $this->assertInstanceOf('Guave\AssetLoadBundle\GuaveAssetLoadBundle', $bundle);
    }
}
