<?php

namespace Estouai\Weave\Blocks;

use Estouai\Weave\Support\BlockIcon;
use Estouai\Weave\Support\FieldType;

class ImageBlock extends Block
{
    public function type(): string
    {
        return 'image';
    }

    public function label(): string
    {
        return 'Image';
    }

    public function category(): string
    {
        return 'primitives';
    }

    public function icon(): BlockIcon
    {
        return BlockIcon::MediaImagePhotoFocusFrame;
    }

    public function view(): string
    {
        return 'weave::blocks.image';
    }

    public function propsSchema(): array
    {
        return [
            ['handle' => 'asset', 'field' => ['type' => FieldType::Assets, 'container' => 'assets', 'max_files' => 1]],
            ['handle' => 'alt', 'field' => ['type' => FieldType::Text]],
        ];
    }

    public function defaultProps(): array
    {
        return ['alt' => ''];
    }
}
