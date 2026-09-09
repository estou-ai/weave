<?php

namespace Estouai\Weave\Blocks;

class TextBlock extends Block
{
    // ponytail: upgrade to bard fieldtype when editors need inline formatting (bold/links).
    public function type(): string
    {
        return 'text';
    }

    public function label(): string
    {
        return 'Text';
    }

    public function category(): string
    {
        return 'primitives';
    }

    public function icon(): string
    {
        return 'text-formatting-paragraph';
    }

    public function view(): string
    {
        return 'weave::blocks.text';
    }

    public function propsSchema(): array
    {
        return [
            ['handle' => 'content', 'field' => ['type' => 'textarea']],
        ];
    }

    public function defaultProps(): array
    {
        return ['content' => 'Text content'];
    }
}
