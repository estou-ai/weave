<?php

namespace Estouai\Weave\Blocks;

use Estouai\Weave\Support\BlockIcon;
use Estouai\Weave\Support\FieldType;
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

    public function icon(): BlockIcon
    {
        return BlockIcon::LayoutSplitRight;
    }

    public function view(): string
    {
        return 'weave::blocks.feature-split';
    }

    public function propsSchema(): array
    {
        return [
            ['handle' => 'image', 'field' => ['type' => FieldType::Assets, 'container' => 'assets', 'max_files' => 1]],
            ['handle' => 'eyebrow', 'field' => ['type' => FieldType::Text]],
            ['handle' => 'body', 'field' => ['type' => FieldType::Textarea]],
            ['handle' => 'href', 'field' => ['type' => FieldType::Text]],
            ['handle' => 'label', 'field' => ['type' => FieldType::Text, 'default' => 'Learn more']],
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
