<?php

namespace Estouai\Weave\Blocks;

use Estouai\Weave\Support\BlockIcon;
use Estouai\Weave\Support\FieldType;
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

    public function icon(): BlockIcon
    {
        return BlockIcon::HeroImageAboveText;
    }

    public function view(): string
    {
        return 'weave::blocks.hero';
    }

    public function propsSchema(): array
    {
        return [
            ['handle' => 'lines', 'field' => ['type' => FieldType::ArrayField, 'mode' => 'list', 'display' => 'Headline lines']],
            ['handle' => 'background', 'field' => ['type' => FieldType::Assets, 'container' => 'assets', 'max_files' => 1, 'display' => 'Background image']],
            ['handle' => 'buttons', 'field' => ['type' => FieldType::Grid, 'display' => 'Buttons', 'fields' => [
                ['handle' => 'label', 'field' => ['type' => FieldType::Text]],
                ['handle' => 'href', 'field' => ['type' => FieldType::Text]],
                ['handle' => 'variant', 'field' => ['type' => FieldType::Select, 'options' => ['dark', 'light', 'link-blue', 'link-green', 'link-sky'], 'default' => 'light']],
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
