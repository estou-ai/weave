<?php

namespace Estouai\Weave\Tests\Blocks;

use Estouai\Weave\Blocks\BlockRegistry;
use Estouai\Weave\Tests\TestCase;

class TextBlockTest extends TestCase
{
    public function test_text_block_uses_bard_for_inline_formatting()
    {
        $field = collect(BlockRegistry::find('text')->finalPropsSchema())->firstWhere('handle', 'content')['field'];

        $this->assertSame('bard', $field['type']);
        $this->assertContains('bold', $field['buttons']);
    }
}
