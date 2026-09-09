<?php

namespace Estouai\Weave\Support;

use Estouai\Weave\Blocks\BlockRegistry;
use Illuminate\Support\Facades\Log;
use Statamic\Fields\ArrayableString;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Value;

class BlockTreeAugmentor
{
    public static function augment(?array $blocks): array
    {
        return collect($blocks ?? [])->map(fn ($node) => static::augmentNode($node))->all();
    }

    protected static function augmentNode(array $node): array
    {
        $block = BlockRegistry::find($node['type'] ?? '');

        if (! $block) {
            Log::warning("weave: unknown block type [{$node['type']}], skipping props augmentation");

            return $node;
        }

        $node['props'] = Blueprint::make()
            ->setContents(['fields' => $block->finalPropsSchema()])
            ->fields()
            ->addValues($node['props'] ?? [])
            ->augment()
            ->values()
            ->map(fn ($value) => static::unwrap($value))
            ->all();

        if (! empty($node['children'])) {
            $node['children'] = collect($node['children'])->map(fn ($child) => static::augmentNode($child))->all();
        }

        return $node;
    }

    // Statamic wraps augmented values in Value/ArrayableString objects (e.g. select
    // options become LabeledValue) for lazy resolution + Antlers ergonomics. Our Blade
    // adapters expect plain scalars/arrays, so unwrap recursively rather than leaking
    // these types into ===/array-key comparisons in the views.
    protected static function unwrap($value)
    {
        if ($value instanceof Value) {
            return static::unwrap($value->value());
        }

        if ($value instanceof ArrayableString) {
            return $value->value();
        }

        if (is_array($value)) {
            return collect($value)->map(fn ($v) => static::unwrap($v))->all();
        }

        return $value;
    }
}
