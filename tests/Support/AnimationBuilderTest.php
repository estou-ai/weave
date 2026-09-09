<?php

namespace Estouai\Weave\Tests\Support;

use Estouai\Weave\Support\AnimationBuilder;
use PHPUnit\Framework\TestCase;

class AnimationBuilderTest extends TestCase
{
    public function test_no_animation_is_not_animated()
    {
        $this->assertFalse(AnimationBuilder::isAnimated([]));
        $this->assertFalse(AnimationBuilder::isAnimated(['animation' => 'bogus']));
        $this->assertSame('', AnimationBuilder::style([]));
    }

    public function test_fade_up_starts_offset_and_transparent()
    {
        $style = AnimationBuilder::style(['animation' => 'fade-up', 'animation_duration' => 800, 'animation_delay' => 200]);

        $this->assertStringContainsString('opacity:0', $style);
        $this->assertStringContainsString('transform:translateY(24px)', $style);
        $this->assertStringContainsString('800ms ease-out 200ms', $style);
    }

    public function test_fade_in_has_no_transform()
    {
        $this->assertStringNotContainsString('transform:', AnimationBuilder::style(['animation' => 'fade-in']));
    }

    public function test_duration_augmented_to_zero_falls_back_to_default()
    {
        // Same Statamic `range` fieldtype quirk as StyleBuilder's ZERO_MEANS_UNSET
        // — a never-touched duration field augments to 0, which would otherwise
        // mean an instant, invisible "animation".
        $this->assertStringContainsString('600ms', AnimationBuilder::style(['animation' => 'fade-in', 'animation_duration' => 0]));
    }
}
