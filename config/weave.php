<?php

return [
    'preview' => [
        'nav_partial' => 'partials.nav',
        'footer_partial' => 'partials.footer',

        // Vite entrypoints loaded by the CP canvas preview (resources/views/preview.blade.php).
        // On a fresh install, point these at your own app's entries (matching your
        // own vite.config.js `input`) — this is what the site itself loads, project-
        // specific by nature. Include a JS entry (even an empty-ish one that just
        // starts Alpine) if any block's Blade view uses x-data/x-show/etc — without
        // it those blocks render inert (no interactivity, no JS-driven content) in
        // the CP preview even though they work fine on the live site.
        'vite_entries' => ['resources/css/app.css', 'resources/js/app.js'],
    ],

    // Add your own blocks here without touching the addon — no PHP class needed
    // for a simple block. Key = block type (unique handle), value = definition.
    // See README.md "Adding a block" for the field reference and an example.
    'custom_blocks' => [
        //
    ],
];
