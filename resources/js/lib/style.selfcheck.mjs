// node resources/js/lib/style.selfcheck.mjs
import assert from 'node:assert/strict';
import { buildNodeStyle, PATCHABLE_KEYS, INLINE_EDITED_KEYS } from './style.js';

assert.equal(buildNodeStyle({ type: 'column', props: { width: 4 } }), 'flex:4 1 0%;min-width:0');
assert.equal(
    buildNodeStyle({ type: 'column', props: { width: 4, background_color: '#fff' } }),
    'flex:4 1 0%;min-width:0;background-color:#fff',
);
assert.equal(buildNodeStyle({ type: 'column', props: {} }), 'flex:50 1 0%;min-width:0');
assert.equal(buildNodeStyle({ type: 'text', props: { text_color: '#000' } }), 'color:#000');
assert.equal(buildNodeStyle({ type: 'text', props: {} }), '');

// A prop like `label` (button) must NOT be in either set — it needs a full
// re-render to show up, since nothing patches text content from a side-panel edit.
assert.ok(! PATCHABLE_KEYS.includes('label') && ! INLINE_EDITED_KEYS.includes('label'));
assert.ok(PATCHABLE_KEYS.includes('background_color'));
assert.ok(INLINE_EDITED_KEYS.includes('content') && INLINE_EDITED_KEYS.includes('text'));

console.log('style.js self-check OK');
