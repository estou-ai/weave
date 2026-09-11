<?php

namespace Estouai\Weave\Blocks;

use Estouai\Weave\Support\BlockIcon;
use Estouai\Weave\Support\FieldType;
class ImageBanner extends Block
{
    public function type(): string
    {
        return 'image_banner';
    }

    public function label(): string
    {
        return 'Image Banner';
    }

    public function category(): string
    {
        return 'sections';
    }

    public function icon(): BlockIcon
    {
        return BlockIcon::MediaImagePhotoFocusFrame;
    }

    public function view(): string
    {
        return 'weave::blocks.image-banner';
    }

    public function propsSchema(): array
    {
        return [
            ['handle' => 'asset', 'field' => ['type' => FieldType::Assets, 'container' => 'assets', 'max_files' => 1]],
            ['handle' => 'caption', 'field' => ['type' => FieldType::Text]],
            ['handle' => 'height', 'field' => ['type' => FieldType::Select, 'options' => ['sm', 'md', 'lg'], 'default' => 'md']],
        ];
    }

    public function defaultProps(): array
    {
        return ['caption' => '', 'height' => 'md'];
    }
}
