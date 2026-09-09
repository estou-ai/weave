<?php

namespace Estouai\Weave\Support;

class AnimationBuilder
{
    // Elementor's own entrance-animation names, transform-only subset (their
    // full list also has bounce/flip/rotate/slide-in variants — add a row here
    // if editors ask for one, no redesign needed).
    protected const TRANSFORMS = [
        'fade-in' => '',
        'fade-up' => 'translateY(24px)',
        'fade-down' => 'translateY(-24px)',
        'fade-left' => 'translateX(24px)',
        'fade-right' => 'translateX(-24px)',
        'zoom-in' => 'scale(.92)',
    ];

    public static function isAnimated(array $props): bool
    {
        // `?? ''` not `?? null` — array_key_exists(null, ...) is itself a
        // deprecated implicit-null-to-array-key coercion as of PHP 8.1, same
        // class of bug as the one already documented in StyleBuilder::build().
        return array_key_exists($props['animation'] ?? '', self::TRANSFORMS);
    }

    // CSS-transition based, not @keyframes: the element starts at this
    // (opacity:0 + offset transform) and the host app's `@alpinejs/intersect`
    // plugin (via the `x-intersect.once` attribute nodes.blade.php emits) just
    // clears both to their normal values once the element scrolls into view —
    // the already-declared `transition` animates that change. No animation
    // library, no per-preset keyframes to maintain.
    public static function style(array $props): string
    {
        if (! self::isAnimated($props)) {
            return '';
        }

        $duration = (int) ($props['animation_duration'] ?? 600) ?: 600;
        $delay = (int) ($props['animation_delay'] ?? 0);

        $declarations = [
            'opacity:0',
            "transition:opacity {$duration}ms ease-out {$delay}ms, transform {$duration}ms ease-out {$delay}ms",
        ];

        if ($transform = self::TRANSFORMS[$props['animation']]) {
            $declarations[] = "transform:{$transform}";
        }

        return implode(';', $declarations);
    }
}
