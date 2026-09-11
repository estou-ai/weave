<?php

namespace Estouai\Weave\Blocks;

use Estouai\Weave\Support\BlockIcon;
use Estouai\Weave\Support\FieldType;

class Column extends Block
{
    public function type(): string
    {
        return 'column';
    }

    public function label(): string
    {
        return 'Column';
    }

    public function category(): string
    {
        return 'primitives';
    }

    public function icon(): BlockIcon
    {
        return BlockIcon::ContainerAdd;
    }

    public function view(): string
    {
        return 'weave::blocks.column';
    }

    public function allowsChildren(): bool
    {
        return true;
    }

    public function propsSchema(): array
    {
        return [
            // Still a flex-grow ratio under the hood (nodes.blade.php renders
            // `flex:{width} 1 0%`), not a real CSS percentage — gap-safe (the
            // flexbox algorithm reserves gap space before distributing by ratio,
            // which a fixed `width:{n}%` wouldn't). Scaled 1–100 purely so sibling
            // columns that sum to 100 read as literal percentages: 50/50, 70/30.
            ['handle' => 'width', 'field' => ['type' => FieldType::Integer, 'display' => 'Width', 'min' => 1, 'max' => 100, 'default' => 50, 'append' => '%']],
        ];
    }

    public function defaultProps(): array
    {
        return ['width' => 50];
    }
}
