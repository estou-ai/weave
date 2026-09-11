<?php

namespace Estouai\Weave\Support;

class AnimationBuilder
{
    public static function isAnimated(array $props): bool
    {
        // `?? ''` not `?? null` — array_key_exists(null, ...) is itself a
        // deprecated implicit-null-to-array-key coercion as of PHP 8.1, same
        // class of bug as the one already documented in StyleBuilder::build().
        return Animation::tryFrom($props['animation'] ?? '') !== null;
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

        if ($transform = Animation::from($props['animation'])->transform()) {
            $declarations[] = "transform:{$transform}";
        }

        return implode(';', $declarations);
    }
}
