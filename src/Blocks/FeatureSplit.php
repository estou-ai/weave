<?php

namespace Estouai\Weave\Blocks;

class FeatureSplit extends Block
{
    public function type(): string
    {
        return 'feature_split';
    }

    public function label(): string
    {
        return 'Feature Split';
    }

    public function category(): string
    {
        return 'sections';
    }

    public function icon(): string
    {
        return 'layout-split-right';
    }

    public function view(): string
    {
        return 'weave::blocks.feature-split';
    }

    public function propsSchema(): array
    {
        return [
            ['handle' => 'image', 'field' => ['type' => 'assets', 'container' => 'assets', 'max_files' => 1]],
            ['handle' => 'eyebrow', 'field' => ['type' => 'text']],
            ['handle' => 'body', 'field' => ['type' => 'textarea']],
            ['handle' => 'href', 'field' => ['type' => 'text']],
            ['handle' => 'label', 'field' => ['type' => 'text', 'default' => 'Learn more']],
        ];
    }

    public function defaultProps(): array
    {
        return [
            'eyebrow' => 'Eyebrow',
            'body' => 'Body text',
            'href' => '#',
            'label' => 'Learn more',
        ];
    }
}
