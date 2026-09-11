<?php

namespace Estouai\Weave\Support;

enum StyleField: string
{
    case TextColor = 'text_color';
    case BackgroundColor = 'background_color';
    case FontSize = 'font_size';
    case FontFamily = 'font_family';
    case FontWeight = 'font_weight';
    case BorderRadius = 'border_radius';
    case BorderWidth = 'border_width';
    case BorderStyle = 'border_style';
    case BorderColor = 'border_color';
    case Shadow = 'shadow';
    case MarginTop = 'margin_top';
    case MarginRight = 'margin_right';
    case MarginBottom = 'margin_bottom';
    case MarginLeft = 'margin_left';
    case PaddingTop = 'padding_top';
    case PaddingRight = 'padding_right';
    case PaddingBottom = 'padding_bottom';
    case PaddingLeft = 'padding_left';
    case Animation = 'animation';
    case AnimationDuration = 'animation_duration';
    case AnimationDelay = 'animation_delay';
    case HideMobile = 'hide_mobile';
    case HideTablet = 'hide_tablet';
    case HideDesktop = 'hide_desktop';
}
