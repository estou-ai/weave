<?php

namespace Estouai\Weave\Blocks;

use Estouai\Weave\Support\BlockIcon;
use Estouai\Weave\Support\FieldType;
class StatGrid extends Block
{
    public function type(): string
    {
        return 'stat_grid';
    }

    public function label(): string
    {
        return 'Stat Grid';
    }

    public function category(): string
    {
        return 'sections';
    }

    public function icon(): BlockIcon
    {
        return BlockIcon::ChartsDonutGraph;
    }

    public function view(): string
    {
        return 'weave::blocks.stat-grid';
    }

    public function propsSchema(): array
    {
        return [
            ['handle' => 'stats', 'field' => ['type' => FieldType::Grid, 'display' => 'Stats', 'fields' => [
                ['handle' => 'value', 'field' => ['type' => FieldType::Text]],
                ['handle' => 'label', 'field' => ['type' => FieldType::Text]],
            ]]],
        ];
    }

    public function defaultProps(): array
    {
        return ['stats' => []];
    }
}
