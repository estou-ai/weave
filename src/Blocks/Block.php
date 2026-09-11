<?php

namespace Estouai\Weave\Blocks;

use Estouai\Weave\Support\BlockIcon;
use Estouai\Weave\Support\FieldType;
use BackedEnum;
use UnitEnum;

abstract class Block
{
    abstract public function type(): string;

    abstract public function label(): string;

    abstract public function category(): string;

    abstract public function propsSchema(): array;

    abstract public function view(): string;

    public function icon(): BlockIcon|string
    {
        return BlockIcon::PuzzlePiece;
    }

    // Custom icon: override `icon()` returning a raw `<svg>` string — the CP
    // injects it as-is, so only ever pass markup you trust.
    public function iconName(): ?string
    {
        $icon = $this->icon();

        if ($icon instanceof BlockIcon) {
            return $icon->value;
        }

        return static::isSvg($icon) ? null : $icon;
    }

    public function iconSvg(): ?string
    {
        $icon = $this->icon();

        return $icon instanceof BlockIcon || ! static::isSvg($icon) ? null : $icon;
    }

    protected static function isSvg(string $icon): bool
    {
        return str_starts_with(trim($icon), '<svg');
    }

    public function allowsChildren(): bool
    {
        return false;
    }

    public function defaultProps(): array
    {
        return [];
    }

    // Style-field handles a block doesn't want in its props panel — e.g.
    // TYPOGRAPHY_FIELDS for a block with no text of its own to style. Override
    // per-block.
    public function excludedStyleFields(): array
    {
        return [];
    }

    public const TYPOGRAPHY_FIELDS = ['text_color', 'background_color', 'font_size', 'font_family', 'font_weight'];

    // ponytail: border/shadow/typography-family/responsive are a documented v2 — add a row here
    // + a row in StyleBuilder::MAP when needed, no redesign required.
    public static function styleSchema(): array
    {
        return [
            ['handle' => 'text_color', 'field' => ['type' => FieldType::Color, 'swatches' => static::colorSwatches(), 'allow_any' => true]],
            ['handle' => 'background_color', 'field' => ['type' => FieldType::Color, 'swatches' => static::colorSwatches(), 'allow_any' => true]],
            ['handle' => 'font_size', 'field' => ['type' => FieldType::Range, 'min' => 0, 'max' => 96, 'step' => 1, 'append' => 'px']],
            ['handle' => 'font_family', 'field' => ['type' => FieldType::Select, 'options' => static::fontFamilyOptions(), 'clearable' => true, 'placeholder' => 'Default']],
            ['handle' => 'font_weight', 'field' => ['type' => FieldType::Select, 'options' => static::fontWeightOptions(), 'clearable' => true, 'placeholder' => 'Default']],
            ['handle' => 'border_radius', 'field' => ['type' => FieldType::Range, 'display' => 'Corner Radius', 'min' => 0, 'max' => 48, 'step' => 1, 'append' => 'px']],
            ['handle' => 'border_width', 'field' => ['type' => FieldType::Range, 'display' => 'Border Width', 'min' => 0, 'max' => 12, 'step' => 1, 'append' => 'px']],
            ['handle' => 'border_style', 'field' => ['type' => FieldType::Select, 'display' => 'Border Style', 'options' => ['solid' => 'Solid', 'dashed' => 'Dashed', 'dotted' => 'Dotted', 'double' => 'Double'], 'clearable' => true, 'placeholder' => 'Solid']],
            ['handle' => 'border_color', 'field' => ['type' => FieldType::Color, 'display' => 'Border Color', 'swatches' => static::colorSwatches(), 'allow_any' => true]],
            ['handle' => 'shadow', 'field' => ['type' => FieldType::Select, 'display' => 'Shadow', 'options' => static::shadowOptions(), 'clearable' => true, 'placeholder' => 'None']],
            ['handle' => 'margin_top', 'field' => ['type' => FieldType::Integer, 'min' => 0, 'max' => 160, 'group' => 'margin', 'side' => 'top', 'append' => 'px']],
            ['handle' => 'margin_right', 'field' => ['type' => FieldType::Integer, 'min' => 0, 'max' => 160, 'group' => 'margin', 'side' => 'right', 'append' => 'px']],
            ['handle' => 'margin_bottom', 'field' => ['type' => FieldType::Integer, 'min' => 0, 'max' => 160, 'group' => 'margin', 'side' => 'bottom', 'append' => 'px']],
            ['handle' => 'margin_left', 'field' => ['type' => FieldType::Integer, 'min' => 0, 'max' => 160, 'group' => 'margin', 'side' => 'left', 'append' => 'px']],
            ['handle' => 'padding_top', 'field' => ['type' => FieldType::Integer, 'min' => 0, 'max' => 96, 'group' => 'padding', 'side' => 'top', 'append' => 'px']],
            ['handle' => 'padding_right', 'field' => ['type' => FieldType::Integer, 'min' => 0, 'max' => 96, 'group' => 'padding', 'side' => 'right', 'append' => 'px']],
            ['handle' => 'padding_bottom', 'field' => ['type' => FieldType::Integer, 'min' => 0, 'max' => 96, 'group' => 'padding', 'side' => 'bottom', 'append' => 'px']],
            ['handle' => 'padding_left', 'field' => ['type' => FieldType::Integer, 'min' => 0, 'max' => 96, 'group' => 'padding', 'side' => 'left', 'append' => 'px']],
            ['handle' => 'animation', 'field' => ['type' => FieldType::Select, 'display' => 'Animation', 'options' => static::animationOptions(), 'clearable' => true, 'placeholder' => 'None']],
            ['handle' => 'animation_duration', 'field' => ['type' => FieldType::Range, 'display' => 'Animation Duration', 'min' => 200, 'max' => 2000, 'step' => 100, 'default' => 600, 'append' => 'ms']],
            ['handle' => 'animation_delay', 'field' => ['type' => FieldType::Range, 'display' => 'Animation Delay', 'min' => 0, 'max' => 2000, 'step' => 100, 'append' => 'ms']],
            ['handle' => 'hide_mobile', 'field' => ['type' => FieldType::Toggle, 'display' => 'Hide on Mobile', 'instructions' => 'Below 768px.']],
            ['handle' => 'hide_tablet', 'field' => ['type' => FieldType::Toggle, 'display' => 'Hide on Tablet', 'instructions' => '768px–1023px.']],
            ['handle' => 'hide_desktop', 'field' => ['type' => FieldType::Toggle, 'display' => 'Hide on Desktop', 'instructions' => '1024px and up.']],
        ];
    }

    protected static function colorSwatches(): array
    {
        return ['#013983', '#5a84d8', '#1ed699', '#303030', '#f8f9f8', '#acc7ff', '#eaf1ff', '#f2f2f2', '#ffffff'];
    }

    // ponytail: values are raw CSS (var(...) / weight numbers) consumed as-is by
    // StyleBuilder — no separate lookup table to keep in sync.
    protected static function fontFamilyOptions(): array
    {
        return [
            'var(--font-display)' => 'Forum (Display)',
            'var(--font-sans)' => 'Montserrat (Sans)',
            'var(--font-caption)' => 'Geist (Caption)',
        ];
    }

    protected static function fontWeightOptions(): array
    {
        return [
            '400' => 'Normal',
            '500' => 'Medium',
            '600' => 'Semibold',
            '700' => 'Bold',
        ];
    }

    // Keys match StyleBuilder::SHADOWS / lib/style.js SHADOWS — the actual
    // box-shadow CSS lives there, not here (this is display labels only).
    protected static function shadowOptions(): array
    {
        return [
            'sm' => 'Small',
            'md' => 'Medium',
            'lg' => 'Large',
            'xl' => 'Extra Large',
        ];
    }

    // Keys match Support/AnimationBuilder::TRANSFORMS — same "labels here, CSS
    // there" split as shadowOptions() above.
    protected static function animationOptions(): array
    {
        return [
            'fade-in' => 'Fade In',
            'fade-up' => 'Fade In Up',
            'fade-down' => 'Fade In Down',
            'fade-left' => 'Fade In Left',
            'fade-right' => 'Fade In Right',
            'zoom-in' => 'Zoom In',
        ];
    }

    public function finalDefaultProps(): array
    {
        return collect($this->defaultProps())
            ->map(fn ($value) => static::normaliseDefaultValue($value))
            ->all();
    }

    public function finalPropsSchema(): array
    {
        $styles = array_filter(
            $this->normalisePropsSchema(static::styleSchema()),
            fn (array $field) => ! in_array($field['handle'], $this->excludedStyleFields(), true)
        );

        return array_merge($this->normalisePropsSchema($this->propsSchema()), array_values($styles));
    }

    protected function normalisePropsSchema(array $schema): array
    {
        return array_map(fn (array $field) => $this->normaliseField($field), $schema);
    }

    protected function normaliseField(array $field): array
    {
        if (($field['field']['type'] ?? null) instanceof FieldType) {
            $field['field']['type'] = $field['field']['type']->value;
        }

        if (array_key_exists('default', $field['field'])) {
            $field['field']['default'] = static::normaliseDefaultValue($field['field']['default']);
        }

        if (($field['field']['type'] ?? null) === 'select') {
            $field['field']['options'] = static::normaliseSelectOptions(
                $field['field']['enum'] ?? $field['field']['options'] ?? []
            );
            unset($field['field']['enum']);
        }

        if (isset($field['field']['fields']) && is_array($field['field']['fields'])) {
            $field['field']['fields'] = $this->normalisePropsSchema($field['field']['fields']);
        }

        if (isset($field['field']['sets']) && is_array($field['field']['sets'])) {
            $field['field']['sets'] = array_map(function (array $set) {
                if (isset($set['fields']) && is_array($set['fields'])) {
                    $set['fields'] = $this->normalisePropsSchema($set['fields']);
                }

                return $set;
            }, $field['field']['sets']);
        }

        return $field;
    }

    protected static function normaliseSelectOptions(array|string $options): array
    {
        if (! is_string($options)) {
            return $options;
        }

        if (! enum_exists($options)) {
            return [];
        }

        return collect($options::cases())
            ->mapWithKeys(fn (UnitEnum $case) => [
                $case instanceof BackedEnum ? $case->value : $case->name => method_exists($case, 'label') ? $case->label() : $case->name,
            ])
            ->all();
    }

    protected static function normaliseDefaultValue(mixed $value): mixed
    {
        return match (true) {
            $value instanceof BackedEnum => $value->value,
            $value instanceof UnitEnum => $value->name,
            is_array($value) => array_map(fn ($item) => static::normaliseDefaultValue($item), $value),
            default => $value,
        };
    }
}

