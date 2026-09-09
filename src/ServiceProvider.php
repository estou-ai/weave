<?php

namespace Estouai\Weave;

use Estouai\Weave\Blocks\BlockRegistry;
use Estouai\Weave\Blocks\{
    Hero, FeatureSplit, TestimonialCarousel, StatGrid,
    Heading, TextBlock, ButtonBlock, ImageBlock, DividerBlock, SpacerBlock, Columns, Column,
    HeadingCta, ImageBanner, FormBlock,
};
use Illuminate\Support\Facades\Blade;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $vite = [
        'input' => ['resources/js/addon.js'],
    ];

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/weave.php', 'weave');
    }

    public function bootAddon()
    {
        Blade::anonymousComponentPath(__DIR__.'/../resources/views/components', 'weave');

        // Deliberately generic, presentation-only blocks — none of these reach
        // into a host app's own collections, Eloquent queries, or Tag classes.
        // A block that fetches project-specific data (a "latest posts from my
        // own `articles` collection" grid, a map pulling from my own `offices`
        // taxonomy, ...) belongs in the host app instead, registered via
        // BlockRegistry::register() — see README.md "Adding a block".
        foreach ([
            Hero::class, FeatureSplit::class, TestimonialCarousel::class, StatGrid::class,
            Heading::class, TextBlock::class, ButtonBlock::class, ImageBlock::class,
            DividerBlock::class, SpacerBlock::class, Columns::class, Column::class,
            HeadingCta::class, ImageBanner::class, FormBlock::class,
        ] as $block) {
            BlockRegistry::register($block);
        }

        // Project-defined blocks — see config/weave.php `custom_blocks` and
        // README.md "Adding a block". No addon code to touch for a simple block.
        foreach (config('weave.custom_blocks', []) as $type => $definition) {
            BlockRegistry::registerConfig($type, $definition);
        }
    }
}
