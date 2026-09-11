<?php

namespace Estouai\Weave\Blocks;

use Estouai\Weave\Support\BlockIcon;
use Estouai\Weave\Support\FieldType;
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

    public function icon(): BlockIcon
    {
        return BlockIcon::Quote;
    }

    public function view(): string
    {
        return 'weave::blocks.testimonial-carousel';
    }

    public function propsSchema(): array
    {
        return [
            ['handle' => 'quotes', 'field' => ['type' => FieldType::Grid, 'display' => 'Quotes', 'fields' => [
                ['handle' => 'quote', 'field' => ['type' => FieldType::Textarea]],
                ['handle' => 'author', 'field' => ['type' => FieldType::Text]],
            ]]],
        ];
    }

    public function defaultProps(): array
    {
        return ['quotes' => []];
    }
}
