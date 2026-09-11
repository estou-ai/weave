<?php

namespace Estouai\Weave\Blocks;

use Estouai\Weave\Support\BlockIcon;
use Estouai\Weave\Support\FieldType;
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

    public function icon(): BlockIcon
    {
        return BlockIcon::Hr;
    }

    public function view(): string
    {
        return 'weave::blocks.divider';
    }

    public function propsSchema(): array
    {
        return [
            ['handle' => 'color', 'field' => ['type' => FieldType::Text, 'default' => 'border-black/10', 'instructions' => "Tailwind border color class, e.g. border-black/10"]],
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
