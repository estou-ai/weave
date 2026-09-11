<?php

namespace Estouai\Weave\Tests\Blocks;

use Estouai\Weave\Blocks\BlockRegistry;
use Estouai\Weave\Tests\TestCase;

class TextBlockTest extends TestCase
{
    public function test_text_block_uses_bard_for_inline_formatting()
    {
        $block = BlockRegistry::find('text');
        $field = collect($block->finalPropsSchema())->firstWhere('handle', 'content')['field'];

        $this->assertSame('Rich Text', $block->label());
        $this->assertSame('bard', $field['type']);
        $this->assertContains('bold', $field['buttons']);
    }
}
