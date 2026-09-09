<?php

namespace Estouai\Weave\Blocks;

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

    public function icon(): string
    {
        return 'align-left';
    }

    public function view(): string
    {
        return 'weave::blocks.heading-cta';
    }

    public function propsSchema(): array
    {
        return [
            ['handle' => 'eyebrow', 'field' => ['type' => 'text']],
            ['handle' => 'heading', 'field' => ['type' => 'text']],
            ['handle' => 'button_label', 'field' => ['type' => 'text']],
            ['handle' => 'button_href', 'field' => ['type' => 'text']],
            ['handle' => 'button_variant', 'field' => ['type' => 'select', 'options' => ['dark', 'light'], 'default' => 'light']],
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
