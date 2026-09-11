<?php

namespace Estouai\Weave\Blocks;

use Estouai\Weave\Support\BlockIcon;
use Estouai\Weave\Support\FieldType;
class SpacerBlock extends Block
{
    public function type(): string
    {
        return 'spacer';
    }

    public function label(): string
    {
        return 'Spacer';
    }

    public function category(): string
    {
        return 'primitives';
    }

    public function icon(): BlockIcon
    {
        return BlockIcon::ArrowsFitToHeight;
    }

    public function view(): string
    {
        return 'weave::blocks.spacer';
    }

    public function propsSchema(): array
    {
        return [
            ['handle' => 'height', 'field' => ['type' => FieldType::Select, 'options' => ['sm', 'md', 'lg', 'xl'], 'default' => 'md']],
        ];
    }

    public function defaultProps(): array
    {
        return ['height' => 'md'];
    }

    public function excludedStyleFields(): array
    {
        return self::TYPOGRAPHY_FIELDS;
    }
}
