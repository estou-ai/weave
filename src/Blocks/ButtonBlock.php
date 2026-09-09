<?php

namespace Estouai\Weave\Blocks;

class ButtonBlock extends Block
{
    public function type(): string
    {
        return 'button';
    }

    public function label(): string
    {
        return 'Button';
    }

    public function category(): string
    {
        return 'primitives';
    }

    public function icon(): string
    {
        return 'cursor-click';
    }

    public function view(): string
    {
        return 'weave::blocks.button';
    }

    public function propsSchema(): array
    {
        return [
            ['handle' => 'label', 'field' => ['type' => 'text']],
            ['handle' => 'href', 'field' => ['type' => 'text']],
            ['handle' => 'variant', 'field' => ['type' => 'select', 'options' => ['dark', 'light', 'link-blue', 'link-green', 'link-sky'], 'default' => 'dark']],
        ];
    }

    public function defaultProps(): array
    {
        return ['label' => 'Button', 'href' => '#', 'variant' => 'dark'];
    }
}
