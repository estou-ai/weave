<?php

namespace Estouai\Weave\Tests\Fieldtypes;

use Estouai\Weave\Fieldtypes\Weave;
use Estouai\Weave\Tests\TestCase;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\User;

class WeaveTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Hero/ImageBlock's `assets` fields resolve against this container
        // while building field meta in the preload() test below.
        AssetContainer::make('assets')->disk('local')->save();

        $this->actingAs(tap(User::make()->email('test@example.com')->makeSuper())->save());
    }

    public function test_default_value_is_empty_array()
    {
        $this->assertSame([], (new Weave)->defaultValue());
    }

    public function test_pre_process_and_process_are_passthrough_for_known_types()
    {
        $data = [
            ['id' => '1', 'type' => 'heading', 'props' => ['text' => 'Hello', 'level' => 'h2', 'align' => 'left']],
        ];

        $fieldtype = new Weave;

        // Not a byte-for-byte passthrough: both steps fold in the shared style
        // schema's defaults (color/font/margin/padding/hide_*), which grows over
        // time — pin the values that matter instead of the whole shape.
        foreach ([$fieldtype->preProcess($data), $fieldtype->process($data)] as $result) {
            $this->assertSame('1', $result[0]['id']);
            $this->assertSame('heading', $result[0]['type']);
            $this->assertSame('Hello', $result[0]['props']['text']);
            $this->assertSame('h2', $result[0]['props']['level']);
            $this->assertSame('left', $result[0]['props']['align']);
        }
    }

    public function test_process_drops_unknown_block_types()
    {
        $data = [
            ['id' => '1', 'type' => 'heading', 'props' => ['text' => 'Hello']],
            ['id' => '2', 'type' => 'nonexistent', 'props' => []],
        ];

        $result = (new Weave)->process($data);

        $this->assertCount(1, $result);
        $this->assertSame('heading', $result[0]['type']);
    }

    public function test_augment_resolves_props_and_recurses_into_children()
    {
        $data = [
            [
                'id' => '1',
                'type' => 'columns',
                'props' => ['preset' => '50-50', 'gap' => 'md'],
                'children' => [
                    ['id' => '2', 'type' => 'column', 'props' => ['width' => 6], 'children' => [
                        ['id' => '3', 'type' => 'heading', 'props' => ['text' => 'Hi', 'level' => 'h3', 'align' => 'left']],
                    ]],
                ],
            ],
        ];

        $result = (new Weave)->augment($data);

        $this->assertSame('50-50', $result[0]['props']['preset']);
        $this->assertSame(6, $result[0]['children'][0]['props']['width']);
        $this->assertSame('Hi', $result[0]['children'][0]['children'][0]['props']['text']);
    }

    public function test_preload_ships_field_configs_for_every_registered_block()
    {
        $preload = (new Weave)->preload();

        $heading = collect($preload['blockTypes'])->firstWhere('type', 'heading');
        $handles = collect($heading['fields'])->pluck('handle')->all();

        $this->assertNotNull($heading);
        // Own fields come first, in order...
        $this->assertSame(['text', 'level', 'align'], array_slice($handles, 0, 3));
        // ...then the shared style schema is folded in (its exact field list grows
        // over time — see Block::styleSchema() — so only check a couple land).
        $this->assertContains('text_color', $handles);
        $this->assertContains('hide_desktop', $handles);
    }
}
