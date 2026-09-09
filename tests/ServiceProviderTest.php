<?php

namespace Estouai\Weave\Tests;

use Estouai\Weave\ServiceProvider;

class ServiceProviderTest extends TestCase
{
    public function test_config_and_asset_tags_are_publishable()
    {
        $config = ServiceProvider::pathsToPublish(ServiceProvider::class, 'weave-config');
        $assets = ServiceProvider::pathsToPublish(ServiceProvider::class, 'weave');

        $this->assertNotEmpty($config, '`weave-config` tag has nothing registered to publish.');
        $this->assertNotEmpty($assets, '`weave` tag has nothing registered to publish.');
    }
}
