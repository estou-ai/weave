<?php

namespace Estouai\Weave\Blocks;

class TestimonialCarousel extends Block
{
    public function type(): string
    {
        return 'testimonial_carousel';
    }

    public function label(): string
    {
        return 'Testimonial Carousel';
    }

    public function category(): string
    {
        return 'sections';
    }

    public function icon(): string
    {
        return 'quote';
    }

    public function view(): string
    {
        return 'weave::blocks.testimonial-carousel';
    }

    public function propsSchema(): array
    {
        return [
            ['handle' => 'quotes', 'field' => ['type' => 'grid', 'display' => 'Quotes', 'fields' => [
                ['handle' => 'quote', 'field' => ['type' => 'textarea']],
                ['handle' => 'author', 'field' => ['type' => 'text']],
            ]]],
        ];
    }

    public function defaultProps(): array
    {
        return ['quotes' => []];
    }
}
