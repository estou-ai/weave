<?php

namespace Estouai\Weave\Blocks;

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

    public function icon(): string
    {
        return 'media-image-photo-focus-frame';
    }

    public function view(): string
    {
        return 'weave::blocks.image';
    }

    public function propsSchema(): array
    {
        return [
            ['handle' => 'asset', 'field' => ['type' => 'assets', 'container' => 'assets', 'max_files' => 1]],
            ['handle' => 'alt', 'field' => ['type' => 'text']],
        ];
    }

    public function defaultProps(): array
    {
        return ['alt' => ''];
    }
}
