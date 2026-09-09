// Mirrors Support/StyleBuilder.php MAP — keep the two in sync by hand.
const MAP = {
    text_color: { prop: 'color', unit: '' },
    background_color: { prop: 'background-color', unit: '' },
    font_size: { prop: 'font-size', unit: 'px' },
    font_family: { prop: 'font-family', unit: '' },
    font_weight: { prop: 'font-weight', unit: '' },
    margin_top: { prop: 'margin-top', unit: 'px' },
    margin_right: { prop: 'margin-right', unit: 'px' },
    margin_bottom: { prop: 'margin-bottom', unit: 'px' },
    margin_left: { prop: 'margin-left', unit: 'px' },
    padding_top: { prop: 'padding-top', unit: 'px' },
    padding_right: { prop: 'padding-right', unit: 'px' },
    padding_bottom: { prop: 'padding-bottom', unit: 'px' },
    padding_left: { prop: 'padding-left', unit: 'px' },
    border_radius: { prop: 'border-radius', unit: 'px' },
    border_width: { prop: 'border-width', unit: 'px' },
    border_style: { prop: 'border-style', unit: '' },
    border_color: { prop: 'border-color', unit: '' },
};

// Mirrors StyleBuilder::SHADOWS (PHP) — Tailwind's own shadow scale, so a block
// looks consistent with any shadow-sm/md/lg/xl used elsewhere. Not in MAP above:
// a preset key expands to a full multi-value box-shadow, not a plain prop:value.
const SHADOWS = {
    sm: '0 1px 2px 0 rgb(0 0 0 / 0.05)',
    md: '0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)',
    lg: '0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)',
    xl: '0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1)',
};

// Mirrors StyleBuilder::ZERO_MEANS_UNSET (PHP) — 0 is a real "off" value for
// these, not an unset one; skip so a never-touched field doesn't emit a no-op
// declaration (which'd defeat nodes.blade.php's display:contents optimization).
const ZERO_MEANS_UNSET = ['font_size', 'border_radius', 'border_width'];

// Text/heading blocks render their own default color/size classes directly on the
// <p>/<h*> tag, which win over an inherited style on the wrapper div. Mirrors
// StyleBuilder::text() — these keys get re-applied on that inner element too.
const TEXT_KEYS = ['text_color', 'font_size', 'font_family', 'font_weight'];

function buildFrom(props, keys) {
    return keys
        .filter((handle) => props[handle] !== null && props[handle] !== undefined && props[handle] !== '')
        .filter((handle) => ! (ZERO_MEANS_UNSET.includes(handle) && parseInt(props[handle], 10) === 0))
        .map((handle) => `${MAP[handle].prop}:${props[handle]}${MAP[handle].unit}`)
        .join(';');
}

export function buildStyle(props = {}) {
    const style = buildFrom(props, Object.keys(MAP));
    const extra = [];

    // Mirrors StyleBuilder::build() (PHP): pair a rounded corner with
    // overflow:hidden so it actually clips a background/nested content (e.g.
    // an Image block's <img>, or a Column's children).
    if (parseInt(props.border_radius, 10) > 0) {
        extra.push('overflow:hidden');
    }

    // CSS's border-style default is `none` — a width with no style chosen
    // would otherwise be invisible.
    if (parseInt(props.border_width, 10) > 0 && ! props.border_style) {
        extra.push('border-style:solid');
    }

    if (SHADOWS[props.shadow]) {
        extra.push(`box-shadow:${SHADOWS[props.shadow]}`);
    }

    return [style, ...extra].filter(Boolean).join(';');
}

// Keys a live `style` patch can actually express (see Canvas.vue's structuralKey):
// the cosmetic MAP above, plus the two props edited via contenteditable directly
// in the iframe (heading.blade.php / text.blade.php) — those are already visually
// correct the moment the user types them, no patch or reload needed at all.
// animation/animation_duration/animation_delay contribute nothing to buildStyle
// (entrance animations only ever apply outside the editor — see nodes.blade.php)
// so there's genuinely nothing to patch, but they still need to be here: without
// it, editing one falls through to Canvas.vue's "structural" bucket and forces
// a full canvas reload for a change with zero visible effect on the canvas.
export const PATCHABLE_KEYS = [...Object.keys(MAP), 'shadow', 'animation', 'animation_duration', 'animation_delay'];
export const INLINE_EDITED_KEYS = ['content', 'text'];

// Mirrors the `column` special case in components/nodes.blade.php: its flex-grow
// share comes from `width`, not the generic color/margin MAP above. A live patch
// (prop-only edit, no full server re-render) sets the wrapper's `style` attribute
// straight from this — missing the column's flex-basis here would wipe it out on
// every such patch, collapsing all columns back to their own content width.
export function buildNodeStyle(node) {
    const base = buildStyle(node.props || {});

    if (node.type !== 'column') return base;

    const width = parseInt(node.props?.width ?? 50, 10) || 50;
    return [`flex:${width} 1 0%`, 'min-width:0', base].filter(Boolean).join(';');
}

export function buildTextStyle(props = {}) {
    return buildFrom(props, TEXT_KEYS);
}
