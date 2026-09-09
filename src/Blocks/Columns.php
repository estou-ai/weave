<?php

namespace Estouai\Weave\Blocks;

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

    public function icon(): string
    {
        return 'columns';
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
            ['handle' => 'preset', 'field' => ['type' => 'select', 'options' => ['50-50', '33-33-33', '66-33'], 'default' => '50-50']],
            ['handle' => 'direction', 'field' => ['type' => 'select', 'options' => ['horizontal' => 'Horizontal', 'vertical' => 'Vertical'], 'default' => 'horizontal']],
            ['handle' => 'gap', 'field' => ['type' => 'select', 'options' => ['sm', 'md', 'lg'], 'default' => 'md']],
        ];
    }

    public function defaultProps(): array
    {
        return ['preset' => '50-50', 'direction' => 'horizontal', 'gap' => 'md'];
    }
}
