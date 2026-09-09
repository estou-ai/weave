<?php

namespace Estouai\Weave\Tests\Blocks;

use Estouai\Weave\Blocks\Block;
use Estouai\Weave\Blocks\BlockRegistry;
use Estouai\Weave\Tests\TestCase;

class BlockRegistryTest extends TestCase
{
    public function test_all_mvp_block_types_resolve()
    {
        foreach ([
            'hero', 'feature_split', 'testimonial_carousel', 'stat_grid',
            'heading', 'text', 'button', 'image', 'divider', 'spacer', 'columns', 'column',
            'heading_cta', 'image_banner', 'form',
        ] as $type) {
            $this->assertInstanceOf(Block::class, BlockRegistry::find($type), "block type [$type] not registered");
        }
    }

    public function test_unknown_type_returns_null()
    {
        $this->assertNull(BlockRegistry::find('nonexistent'));
    }

    public function test_custom_block_can_be_registered()
    {
        BlockRegistry::register(FakeBlock::class);

        $this->assertInstanceOf(FakeBlock::class, BlockRegistry::find('fake'));
    }

    public function test_config_block_can_be_registered_without_a_class()
    {
        BlockRegistry::registerConfig('quote', [
            'label' => 'Quote',
            'icon' => 'quote',
            'view' => 'blocks.quote',
            'props' => [['handle' => 'quote', 'field' => ['type' => 'textarea']]],
            'defaults' => ['quote' => ''],
        ]);

        $block = BlockRegistry::find('quote');

        $this->assertInstanceOf(Block::class, $block);
        $this->assertSame('Quote', $block->label());
        $this->assertSame('quote', $block->icon());
        $this->assertSame('blocks.quote', $block->view());
        $this->assertSame(['quote' => ''], $block->defaultProps());
        // finalPropsSchema() must still fold in the shared style props (color,
        // margin, padding, hide_*) even though this block has no PHP class of
        // its own to inherit them from.
        $this->assertContains('hide_mobile', array_column($block->finalPropsSchema(), 'handle'));
    }

    public function test_config_block_falls_back_to_sane_defaults()
    {
        BlockRegistry::registerConfig('bare', ['view' => 'blocks.bare']);

        $block = BlockRegistry::find('bare');

        $this->assertSame('Bare', $block->label());
        $this->assertSame('custom', $block->category());
        $this->assertSame('puzzle-piece', $block->icon());
        $this->assertFalse($block->allowsChildren());
        $this->assertSame([], $block->propsSchema());
    }
}

class FakeBlock extends Block
{
    public function type(): string
    {
        return 'fake';
    }

    public function label(): string
    {
        return 'Fake';
    }

    public function category(): string
    {
        return 'primitives';
    }

    public function propsSchema(): array
    {
        return [];
    }

    public function view(): string
    {
        return 'weave::blocks.fake';
    }
}
