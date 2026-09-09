<?php

namespace Estouai\Weave\Support;

class StyleBuilder
{
    protected const MAP = [
        'text_color' => ['prop' => 'color', 'unit' => ''],
        'background_color' => ['prop' => 'background-color', 'unit' => ''],
        'font_size' => ['prop' => 'font-size', 'unit' => 'px'],
        'font_family' => ['prop' => 'font-family', 'unit' => ''],
        'font_weight' => ['prop' => 'font-weight', 'unit' => ''],
        'margin_top' => ['prop' => 'margin-top', 'unit' => 'px'],
        'margin_right' => ['prop' => 'margin-right', 'unit' => 'px'],
        'margin_bottom' => ['prop' => 'margin-bottom', 'unit' => 'px'],
        'margin_left' => ['prop' => 'margin-left', 'unit' => 'px'],
        'padding_top' => ['prop' => 'padding-top', 'unit' => 'px'],
        'padding_right' => ['prop' => 'padding-right', 'unit' => 'px'],
        'padding_bottom' => ['prop' => 'padding-bottom', 'unit' => 'px'],
        'padding_left' => ['prop' => 'padding-left', 'unit' => 'px'],
        'border_radius' => ['prop' => 'border-radius', 'unit' => 'px'],
        'border_width' => ['prop' => 'border-width', 'unit' => 'px'],
        'border_style' => ['prop' => 'border-style', 'unit' => ''],
        'border_color' => ['prop' => 'border-color', 'unit' => ''],
    ];

    // `shadow` isn't a plain prop:value pair like the MAP above (a preset key
    // expands to a full multi-value box-shadow) — Tailwind's own shadow scale,
    // so a block looks consistent with any shadow-sm/md/lg/xl used elsewhere.
    protected const SHADOWS = [
        'sm' => '0 1px 2px 0 rgb(0 0 0 / 0.05)',
        'md' => '0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)',
        'lg' => '0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)',
        'xl' => '0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1)',
    ];

    // Statamic's `range` fieldtype casts an unset value to 0 during augmentation
    // instead of leaving it null (see buildFrom below) — 0 is a real "off" value
    // for these (0px radius, invisible 0px text/border), so treat it as unset too
    // rather than emitting a no-op declaration that'd defeat nodes.blade.php's
    // display:contents optimization for every block that's never touched the field.
    protected const ZERO_MEANS_UNSET = ['font_size', 'border_radius', 'border_width'];

    // Text/heading blocks render their own default Tailwind classes (color, size)
    // directly on the <p>/<h*> tag. An inherited style on the wrapper div loses to
    // any explicit class on the element itself, so text props must be re-applied
    // as inline style on that same element to actually win.
    protected const TEXT_KEYS = ['text_color', 'font_size', 'font_family', 'font_weight'];

    public static function build(array $props): string
    {
        $style = self::buildFrom($props, array_keys(self::MAP));
        $extra = [];

        // A rounded corner on a box with a background/nested content needs
        // overflow:hidden to actually clip to it (e.g. an Image block's <img>,
        // or a Column's children) — pair them rather than adding a second
        // control nobody would think to flip.
        if ((int) ($props['border_radius'] ?? 0) > 0) {
            $extra[] = 'overflow:hidden';
        }

        // CSS's own border-style default is `none`, so a width with no style
        // chosen would be invisible — default to solid rather than making
        // "Border Style" a mandatory second step to see any border at all.
        if ((int) ($props['border_width'] ?? 0) > 0 && empty($props['border_style'])) {
            $extra[] = 'border-style:solid';
        }

        if ($shadow = self::SHADOWS[$props['shadow'] ?? ''] ?? null) {
            $extra[] = "box-shadow:{$shadow}";
        }

        return implode(';', array_filter([$style, ...$extra]));
    }

    public static function text(array $props): string
    {
        return self::buildFrom($props, self::TEXT_KEYS);
    }

    protected static function buildFrom(array $props, array $keys): string
    {
        $declarations = [];

        foreach ($keys as $handle) {
            $spec = self::MAP[$handle];
            $value = $props[$handle] ?? null;
            if ($value === null || $value === '') {
                continue;
            }
            // Statamic's `range` fieldtype casts an unset value to 0 during
            // augmentation instead of leaving it null — see ZERO_MEANS_UNSET above.
            if (in_array($handle, self::ZERO_MEANS_UNSET, true) && (int) $value === 0) {
                continue;
            }
            $declarations[] = "{$spec['prop']}:{$value}{$spec['unit']}";
        }

        return implode(';', $declarations);
    }
}
