<?php

namespace Estouai\Weave\Tests\Support;

use Estouai\Weave\Support\RichText;
use PHPUnit\Framework\TestCase;

class RichTextTest extends TestCase
{
    public function test_legacy_plain_text_is_escaped_and_keeps_line_breaks()
    {
        $this->assertSame("Hello<br>\n&lt;script&gt;x&lt;/script&gt;", RichText::render("Hello\n<script>x</script>"));
    }

    public function test_bard_html_preserves_inline_bold_and_strips_unsafe_markup()
    {
        $html = '<p class="x">Hello <strong>bold</strong><script>alert(1)</script><a href="javascript:alert(1)">bad link</a></p>';

        $this->assertSame('<p>Hello <strong>bold</strong><a>bad link</a></p>', RichText::render($html));
    }

    public function test_safe_links_survive()
    {
        $html = '<p><a href="https://example.com" target="_blank">Link</a></p>';

        $this->assertSame('<p><a href="https://example.com" target="_blank" rel="noopener noreferrer">Link</a></p>', RichText::render($html));
    }

    public function test_unsupported_tags_unwrap_without_preserving_unsafe_children()
    {
        $html = '<p><span onclick="alert(1)">ok<script>alert(1)</script></span></p>';

        $this->assertSame('<p>ok</p>', RichText::render($html));
    }

    public function test_plain_text_extracts_prosemirror_arrays()
    {
        $value = [[
            'type' => 'paragraph',
            'content' => [
                ['type' => 'text', 'text' => 'Hello'],
                ['type' => 'text', 'text' => 'bold', 'marks' => [['type' => 'bold']]],
            ],
        ]];

        $this->assertSame('Hello bold', RichText::plainText($value));
    }
}
