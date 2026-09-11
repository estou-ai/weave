<?php

namespace Estouai\Weave\Blocks;

use Estouai\Weave\Support\BlockIcon;
use Estouai\Weave\Support\FieldType;

class TextBlock extends Block
{
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

    public function icon(): BlockIcon
    {
        return BlockIcon::TextFormattingParagraph;
    }

    public function view(): string
    {
        return 'weave::blocks.text';
    }

    public function propsSchema(): array
    {
        return [
            ['handle' => 'content', 'field' => [
                'type' => FieldType::Bard,
                'buttons' => ['bold', 'italic', 'unorderedlist', 'orderedlist', 'anchor', 'removeformat'],
                'toolbar_mode' => 'floating',
                'remove_empty_nodes' => 'trim',
            ]],
        ];
    }

    public function defaultProps(): array
    {
        return ['content' => [[
            'type' => 'paragraph',
            'content' => [['type' => 'text', 'text' => 'Text content']],
        ]]];
    }
}
