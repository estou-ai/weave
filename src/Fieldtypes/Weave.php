<?php

namespace Estouai\Weave\Fieldtypes;

use Estouai\Weave\Blocks\BlockRegistry;
use Estouai\Weave\Support\BlockTreeAugmentor;
use Illuminate\Support\Facades\Log;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Fieldtype;

class Weave extends Fieldtype
{
    protected $categories = ['structured'];

    public function defaultValue()
    {
        return [];
    }

    public function preProcess($data)
    {
        return collect($data ?? [])
            ->map(fn ($node) => static::preProcessNode($node))
            ->values()
            ->all();
    }

    protected static function preProcessNode(array $node): array
    {
        $block = BlockRegistry::find($node['type'] ?? '');

        if (! $block) {
            return $node;
        }

        $node['props'] = Blueprint::make()
            ->setContents(['fields' => $block->finalPropsSchema()])
            ->fields()
            ->addValues($node['props'] ?? [])
            ->preProcess()
            ->values()
            ->all();

        if (! empty($node['children'])) {
            $node['children'] = collect($node['children'])->map(fn ($child) => static::preProcessNode($child))->all();
        }

        return $node;
    }

    public function process($data)
    {
        return collect($data ?? [])
            ->filter(function ($node) {
                if (! BlockRegistry::find($node['type'] ?? '')) {
                    Log::warning("weave: unknown block type [{$node['type']}] dropped on save");

                    return false;
                }

                return true;
            })
            ->map(fn ($node) => static::processNode($node))
            ->values()
            ->all();
    }

    protected static function processNode(array $node): array
    {
        $block = BlockRegistry::find($node['type']);

        $node['props'] = Blueprint::make()
            ->setContents(['fields' => $block->finalPropsSchema()])
            ->fields()
            ->addValues($node['props'] ?? [])
            ->process()
            ->values()
            ->all();

        if (! empty($node['children'])) {
            $node['children'] = collect($node['children'])->map(fn ($child) => static::processNode($child))->all();
        }

        return $node;
    }

    public function augment($value)
    {
        return BlockTreeAugmentor::augment($value);
    }

    public function preload()
    {
        return [
            'blockTypes' => BlockRegistry::all()->map(function ($block) {
                $fields = Blueprint::make()
                    ->setContents(['fields' => $block->finalPropsSchema()])
                    ->fields()
                    ->addValues($block->finalDefaultProps());

                return [
                    'type' => $block->type(),
                    'label' => $block->label(),
                    'icon' => $block->icon(),
                    'category' => $block->category(),
                    'allowsChildren' => $block->allowsChildren(),
                    'fields' => $fields->toPublishArray(),
                    'meta' => $fields->meta(),
                    'defaults' => $fields->preProcess()->values()->all(),
                ];
            })->values()->all(),
        ];
    }
}
