<?php

namespace Estouai\Weave\Blocks;

class DividerBlock extends Block
{
    public function type(): string
    {
        return 'divider';
    }

    public function label(): string
    {
        return 'Divider';
    }

    public function category(): string
    {
        return 'primitives';
    }

    public function icon(): string
    {
        return 'hr';
    }

    public function view(): string
    {
        return 'weave::blocks.divider';
    }

    public function propsSchema(): array
    {
        return [
            ['handle' => 'color', 'field' => ['type' => 'text', 'default' => 'border-black/10', 'instructions' => "Tailwind border color class, e.g. border-black/10"]],
        ];
    }

    public function defaultProps(): array
    {
        return ['color' => 'border-black/10'];
    }

    public function excludedStyleFields(): array
    {
        return self::TYPOGRAPHY_FIELDS;
    }
}
