<?php

namespace Estouai\Weave\Blocks;

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

    public function icon(): string
    {
        return 'media-image-photo-focus-frame';
    }

    public function view(): string
    {
        return 'weave::blocks.image-banner';
    }

    public function propsSchema(): array
    {
        return [
            ['handle' => 'asset', 'field' => ['type' => 'assets', 'container' => 'assets', 'max_files' => 1]],
            ['handle' => 'caption', 'field' => ['type' => 'text']],
            ['handle' => 'height', 'field' => ['type' => 'select', 'options' => ['sm', 'md', 'lg'], 'default' => 'md']],
        ];
    }

    public function defaultProps(): array
    {
        return ['caption' => '', 'height' => 'md'];
    }
}
