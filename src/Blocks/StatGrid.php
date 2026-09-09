<?php

namespace Estouai\Weave\Blocks;

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

    public function icon(): string
    {
        return 'charts-donut-graph';
    }

    public function view(): string
    {
        return 'weave::blocks.stat-grid';
    }

    public function propsSchema(): array
    {
        return [
            ['handle' => 'stats', 'field' => ['type' => 'grid', 'display' => 'Stats', 'fields' => [
                ['handle' => 'value', 'field' => ['type' => 'text']],
                ['handle' => 'label', 'field' => ['type' => 'text']],
            ]]],
        ];
    }

    public function defaultProps(): array
    {
        return ['stats' => []];
    }
}
