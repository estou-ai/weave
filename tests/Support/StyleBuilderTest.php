<?php

namespace Estouai\Weave\Tests\Support;

use Estouai\Weave\Support\StyleBuilder;
use PHPUnit\Framework\TestCase;

class StyleBuilderTest extends TestCase
{
    public function test_builds_style_string_from_known_props()
    {
        $style = StyleBuilder::build(['text_color' => '#ff0000', 'font_size' => 24, 'margin_top' => 16, 'unrelated' => 'x']);

        $this->assertSame('color:#ff0000;font-size:24px;margin-top:16px', $style);
    }

    public function test_returns_empty_string_when_no_style_props_set()
    {
        $this->assertSame('', StyleBuilder::build(['text' => 'hi']));
    }

    public function test_zero_means_unset_for_range_fieldtype_defaults()
    {
        // Statamic's `range` fieldtype augments a never-touched field to 0, not
        // null — these three must stay empty so nodes.blade.php's display:contents
        // optimization isn't defeated for every untouched block.
        $this->assertSame('', StyleBuilder::build(['font_size' => 0]));
        $this->assertSame('', StyleBuilder::build(['border_radius' => 0]));
        $this->assertSame('', StyleBuilder::build(['border_width' => 0]));
    }

    public function test_border_radius_pairs_with_overflow_hidden()
    {
        $this->assertSame('border-radius:8px;overflow:hidden', StyleBuilder::build(['border_radius' => 8]));
    }

    public function test_border_width_defaults_to_solid_style()
    {
        $this->assertSame('border-width:2px;border-style:solid', StyleBuilder::build(['border_width' => 2]));
    }

    public function test_border_width_respects_explicit_style()
    {
        $this->assertSame('border-width:4px;border-style:dashed', StyleBuilder::build(['border_width' => 4, 'border_style' => 'dashed']));
    }

    public function test_shadow_preset_expands_to_box_shadow()
    {
        $style = StyleBuilder::build(['shadow' => 'md']);

        $this->assertSame('box-shadow:0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)', $style);
    }

    public function test_unknown_shadow_key_is_ignored()
    {
        $this->assertSame('', StyleBuilder::build(['shadow' => 'bogus']));
    }
}
