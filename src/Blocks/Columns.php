<?php

namespace Estouai\Weave\Blocks;

use Estouai\Weave\Support\BlockIcon;
use Estouai\Weave\Support\FieldType;
class Columns extends Block
{
    public function type(): string
    {
        return 'columns';
    }

    public function label(): string
    {
        return 'Columns';
    }

    public function category(): string
    {
        return 'primitives';
    }

    public function icon(): BlockIcon
    {
        return BlockIcon::Columns;
    }

    public function view(): string
    {
        return 'weave::blocks.columns';
    }

    public function allowsChildren(): bool
    {
        return true;
    }

    public function propsSchema(): array
    {
        return [
            ['handle' => 'preset', 'field' => ['type' => FieldType::Select, 'options' => ['50-50', '33-33-33', '66-33'], 'default' => '50-50']],
            ['handle' => 'direction', 'field' => ['type' => FieldType::Select, 'options' => ['horizontal' => 'Horizontal', 'vertical' => 'Vertical'], 'default' => 'horizontal']],
            ['handle' => 'gap', 'field' => ['type' => FieldType::Select, 'options' => ['sm', 'md', 'lg'], 'default' => 'md']],
        ];
    }

    public function defaultProps(): array
    {
        return ['preset' => '50-50', 'direction' => 'horizontal', 'gap' => 'md'];
    }
}
