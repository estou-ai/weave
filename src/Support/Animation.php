<?php

namespace Estouai\Weave\Support;

enum Animation: string
{
    case FadeIn = 'fade-in';
    case FadeUp = 'fade-up';
    case FadeDown = 'fade-down';
    case FadeLeft = 'fade-left';
    case FadeRight = 'fade-right';
    case ZoomIn = 'zoom-in';

    public function label(): string
    {
        return match ($this) {
            self::FadeIn => 'Fade In',
            self::FadeUp => 'Fade In Up',
            self::FadeDown => 'Fade In Down',
            self::FadeLeft => 'Fade In Left',
            self::FadeRight => 'Fade In Right',
            self::ZoomIn => 'Zoom In',
        };
    }

    public function transform(): string
    {
        return match ($this) {
            self::FadeIn => '',
            self::FadeUp => 'translateY(24px)',
            self::FadeDown => 'translateY(-24px)',
            self::FadeLeft => 'translateX(24px)',
            self::FadeRight => 'translateX(-24px)',
            self::ZoomIn => 'scale(.92)',
        };
    }
}
