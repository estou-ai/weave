<?php

namespace Estouai\Weave\Blocks;

class Heading extends Block
{
    public function type(): string
    {
        return 'heading';
    }

    public function label(): string
    {
        return 'Heading';
    }

    public function category(): string
    {
        return 'primitives';
    }

    public function icon(): string
    {
        return 'h2';
    }

    public function view(): string
    {
        return 'weave::blocks.heading';
    }

    public function propsSchema(): array
    {
        return [
            ['handle' => 'text', 'field' => ['type' => 'text']],
            ['handle' => 'level', 'field' => ['type' => 'select', 'options' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'], 'default' => 'h2']],
            ['handle' => 'align', 'field' => ['type' => 'select', 'options' => ['left', 'center', 'right'], 'default' => 'left']],
        ];
    }

    public function defaultProps(): array
    {
        return ['text' => 'Heading', 'level' => 'h2', 'align' => 'left'];
    }
}
