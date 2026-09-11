<?php

namespace Estouai\Weave\Blocks;

use Estouai\Weave\Support\BlockIcon;
use Estouai\Weave\Support\FieldType;
class HeadingCta extends Block
{
    public function type(): string
    {
        return 'heading_cta';
    }

    public function label(): string
    {
        return 'Heading + CTA';
    }

    public function category(): string
    {
        return 'sections';
    }

    public function icon(): BlockIcon
    {
        return BlockIcon::AlignLeft;
    }

    public function view(): string
    {
        return 'weave::blocks.heading-cta';
    }

    public function propsSchema(): array
    {
        return [
            ['handle' => 'eyebrow', 'field' => ['type' => FieldType::Text]],
            ['handle' => 'heading', 'field' => ['type' => FieldType::Text]],
            ['handle' => 'button_label', 'field' => ['type' => FieldType::Text]],
            ['handle' => 'button_href', 'field' => ['type' => FieldType::Text]],
            ['handle' => 'button_variant', 'field' => ['type' => FieldType::Select, 'options' => ['dark', 'light'], 'default' => 'light']],
        ];
    }

    public function defaultProps(): array
    {
        return [
            'eyebrow' => '',
            'heading' => 'Heading',
            'button_label' => '',
            'button_href' => '#',
            'button_variant' => 'light',
        ];
    }
}
