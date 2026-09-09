<?php

namespace Estouai\Weave\Http\Controllers;

use Estouai\Weave\Fieldtypes\Weave;
use Estouai\Weave\Support\BlockTreeAugmentor;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PreviewController extends Controller
{
    public function render(Request $request)
    {
        $blocks = $request->validate(['blocks' => ['array']])['blocks'] ?? [];

        // Live editor state is always in CP wire format (e.g. prefixed asset IDs),
        // same shape the fieldtype receives on save — run it through the same
        // process() step before augmenting, or fieldtypes like Assets can't
        // resolve their value.
        $blocks = (new Weave)->process($blocks);

        $html = view('weave::preview', [
            'blocks' => BlockTreeAugmentor::augment($blocks),
            'editing' => true,
        ])->render();

        return response($html)->header('Content-Type', 'text/html');
    }
}
