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
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $vite = [
        'input' => ['resources/js/addon.js'],
    ];

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/weave.php', 'weave');

        // Registers the `weave-config` tag bootPublishAfterInstall() calls below
        // — without this, `vendor:publish --tag=weave-config` is a silent no-op
        // (unregistered tag, not an error) and config/weave.php never reaches
        // the host app.
        $this->publishes([
            __DIR__.'/../config/weave.php' => config_path('weave.php'),
        ], 'weave-config');
    }

    // Parent's version only republishes the `weave` tag (compiled JS/CSS —
    // see registerVite()'s $this->publishes() call), not `weave-config` — so
    // a fresh `statamic:install` would merge config defaults but never drop
    // config/weave.php into the host app. Composer update on an *existing*
    // install doesn't go through statamic:install at all — see README's
    // "Publishing on update" for the host-side composer script that covers
    // that case (this hook can't reach it: Composer only runs scripts
    // declared in the root project, never a dependency's own).
    protected function bootPublishAfterInstall()
    {
        Statamic::afterInstalled(function ($command) {
            // Compiled assets: always force — they're generated output, a host
            // app has no reason to hand-edit them, and a stale copy is a real bug.
            $command->call('vendor:publish', ['--tag' => 'weave', '--force' => true]);
            // Config: never force — this is the one file a host app is meant to
            // customize (nav/footer partials, vite entries, custom_blocks). Force
            // here would silently wipe those on every reinstall.
            $command->call('vendor:publish', ['--tag' => 'weave-config']);
        });

        return $this;
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
