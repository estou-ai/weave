<?php

namespace Estouai\Weave\Blocks;

class Hero extends Block
{
    public function type(): string
    {
        return 'hero';
    }

    public function label(): string
    {
        return 'Hero';
    }

    public function category(): string
    {
        return 'sections';
    }

    public function icon(): string
    {
        return 'hero-image-above-text';
    }

    public function view(): string
    {
        return 'weave::blocks.hero';
    }

    public function propsSchema(): array
    {
        return [
            ['handle' => 'lines', 'field' => ['type' => 'array', 'mode' => 'list', 'display' => 'Headline lines']],
            ['handle' => 'background', 'field' => ['type' => 'assets', 'container' => 'assets', 'max_files' => 1, 'display' => 'Background image']],
            ['handle' => 'buttons', 'field' => ['type' => 'grid', 'display' => 'Buttons', 'fields' => [
                ['handle' => 'label', 'field' => ['type' => 'text']],
                ['handle' => 'href', 'field' => ['type' => 'text']],
                ['handle' => 'variant', 'field' => ['type' => 'select', 'options' => ['dark', 'light', 'link-blue', 'link-green', 'link-sky'], 'default' => 'light']],
            ]]],
        ];
    }

    public function defaultProps(): array
    {
        return [
            'lines' => ['Headline'],
            'buttons' => [],
        ];
    }
}
