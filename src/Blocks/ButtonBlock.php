<?php

namespace Estouai\Weave\Blocks;

use Estouai\Weave\Support\BlockIcon;
use Estouai\Weave\Support\FieldType;
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

    public function icon(): BlockIcon
    {
        return BlockIcon::CursorClick;
    }

    public function view(): string
    {
        return 'weave::blocks.button';
    }

    public function propsSchema(): array
    {
        return [
            ['handle' => 'label', 'field' => ['type' => FieldType::Text]],
            ['handle' => 'href', 'field' => ['type' => FieldType::Text]],
            ['handle' => 'variant', 'field' => ['type' => FieldType::Select, 'options' => ['dark', 'light', 'link-blue', 'link-green', 'link-sky'], 'default' => 'dark']],
        ];
    }

    public function defaultProps(): array
    {
        return ['label' => 'Button', 'href' => '#', 'variant' => 'dark'];
    }
}
