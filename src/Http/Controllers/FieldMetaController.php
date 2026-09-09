<?php

namespace Estouai\Weave\Http\Controllers;

use Estouai\Weave\Blocks\BlockRegistry;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Statamic\Fields\Blueprint;

class FieldMetaController extends Controller
{
    // The CP's Vue field components (relationship, assets, ...) need per-field
    // `meta` (resolved title/thumbnail for whatever's currently selected) to
    // show a real chip instead of the raw stored id — Statamic computes that
    // from the entry's own data when editing a normal blueprint field. A block
    // has no entry of its own, and Weave::preload() only ever computes
    // `meta` once per block TYPE from defaultProps() (empty), not per node —
    // so a relationship field showing a value it was loaded with (not one just
    // picked in this session) has nothing to resolve against. Called when a
    // node is selected in the canvas, keyed by that node's actual props.
    public function render(Request $request)
    {
        $data = $request->validate(['type' => 'required|string', 'props' => 'array']);

        $block = BlockRegistry::find($data['type']);
        abort_unless($block, 404);

        $meta = Blueprint::make()
            ->setContents(['fields' => $block->finalPropsSchema()])
            ->fields()
            ->addValues($data['props'] ?? [])
            ->meta();

        return response()->json(['meta' => $meta]);
    }
}
